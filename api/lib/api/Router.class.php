<?php
namespace API;

/**
 * Simple and flexible router. Needs PHP >= 5.3.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL3
 * @see     Inspired by http://blog.sosedoff.com/2009/07/04/simpe-php-url-routing-controller/
 */

class Router
{
    // Stores the instance of this object
    protected static $instance = null;

    /**
     * Returns (and if neccessary, initializes) a router object.
     *
     * @return Router Returns a router object
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // All supported method need to be defined here
    protected $supported_methods = array('get', 'post', 'put', 'delete');

    // Stores the registered routes by method and uri template
    protected $routes = array();

    // Stores the registered content renderers
    protected $renderers = array();

    // Stores the chosen or forced content renderer
    protected $content_renderer = false;

    // Stores the default renderer
    protected $default_renderer = false;

    // Stores the registered conditions
    protected $conditions = array();

    // Stores the registered descriptions
    protected $descriptions = array();

    /**
     * Constructs the router.
     */
    protected function __construct()
    {
        $this->registerRenderer(new ContentRenderer);
    }

    /**
     * Sets global conditions.
     *
     * @param Array   $conditions   An associative array with parameter name
     *                              as key and regexp to match as value
     * @return Router Returns instance of itself to allow chaining
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * Registers a handler for a specific combination of request method
     * and uri template.
     *
     * @param String  $method       Expected requested method
     * @param String  $uri_template Expected uri structure
     * @param Closure $handler      Request handler
     * @param Array   $conditions   An associative array with parameter name
     *                              as key and regexp to match as value
     *                              (optional)
     * @return Router Returns instance of itself to allow chaining
     * @throws RuntimeException If passed request method is not supported
     */
    public function register($method, $uri_template, $handler, $conditions = array())
    {
        // Normalize method and test whether it's supported
        $method = strtolower($method);
        if (!in_array($method, $this->supported_methods)) {
            throw new Exception('Method "' . $method . '" is not supported.');
        }

        // Initialize routes storage for this method if neccessary
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = array();
        }

        // Normalize uri template (always starts with a slash)
        if ($uri_template[0] !== '/') {
            $uri_template = '/' . $uri_template;
        }

        $this->routes[$method][$uri_template] = compact('handler', 'conditions');

        // Return instance to allow chaining
        return $this;
    }

    /**
     * Unknown method fallback that simplifies registration for supported
     * request methods. For example, see the following example:
     *
     * <code>
     *     // The first operation is a shortcut of the latter
     *     $router->get('/foo', function () { return 'foo'; });
     *     $router->register('get', '/foo', function () { return 'foo'; });
     * </code>
     *
     * @param String $method    Name of the called method
     * @param Array  $arguments Array of arguments passed to the method
     * @return Router Returns instance of itself to allow chaining
     * @throws BadMethodCallException If the method call is invalid in any way
     */
    public function __call($method, $arguments)
    {
        if (!in_array($method, $this->supported_methods)) {
            throw new BadMethodCallException('Call to undefined method "' . $method . '"');
        }
        if (count($arguments) < 2) {
            throw new BadMethodCallException('Method "' . $method . '" expects exactly two parameters.');
        }

        array_unshift($arguments, $method);

        return call_user_func_array(array($this, 'register'), $arguments);
    }

    /**
     * Describe a single route or multiple routes.
     *
     * @param String|Array $uri_template URI template to describe or pass an
     *                                   array to describe multiple routes.
     * @param String|null  $description  Description of the route
     * @param String       $method       Method to describe.
     * @return Router Returns instance of itself to allow chaining
     */
    public function describe($uri_template, $description = null, $method = 'get')
    {
        if (func_num_args() === 1 && is_array($uri_template)) {
            foreach ($uri_template as $template => $description) {
                $this->describe($template, $description);
            }
        } elseif (func_num_args() === 2 && is_array($description)) {
            foreach ($description as $method => $desc) {
                $this->describe($uri_template, $desc, $method);
            }
        } else {
            if (!isset($this->descriptions[$uri_template])) {
                $this->descriptions[$uri_template] = array();
            }
            if (isset($this->routes[$method][$uri_template])) {
                $this->descriptions[$uri_template][$method] = $description;
            } else {
                // Try to find route with different method
                foreach ($this->routes as $m => $templates) {
                    if (isset($templates[$uri_template])) {
                        $this->descriptions[$uri_template][$m] = $description;
                        break;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get list of registered routes - optionally with their descriptions.
     *
     * @param bool $describe Include descriptions
     * @return Array List of routes
     */
    public function getRoutes($describe = false)
    {
        $result = array();
        foreach ($this->routes as $method => $routes) {
            foreach (array_keys($routes) as $uri) {
                if (!isset($result[$uri])) {
                    $result[$uri] = array();
                }
                if ($describe) {
                    $result[$uri][$method] = $this->descriptions[$uri][$method] ?: null;
                } else {
                    $result[$uri][] = $method;
                }
            }
        }
        ksort($result);
        if ($describe) {
            $result = array_map(function ($item) {
                ksort($item);
                return $item;
            }, $result);
        }
        return $result;
    }

    /**
     * Tests whether an uri matches a template.
     *
     * The template may contain placeholders by prefixing an appropriate,
     * unique placeholder name with a colon (:).
     *
     * <code>$template = '/hello/:name';</code>
     *
     * If the uri matches the template, all evaluated placeholders will
     * be stored in the parameters array.
     *
     * @param String $uri        The uri to test
     * @param String $template   The uri template to test against
     * @param Array  $parameters Stores evaluated parameters on match (optional)
     * @param Array  $conditions An associative array with parameter name as key
     *                           and regexp to match as value (optional)
     * @return bool Returns true if the uri matches the template
     */
    public function uriMatchesTemplate($uri, $template, &$parameters = null, $conditions = array())
    {
        // Initialize parameters array
        $parameters = array();

        // Set conditions (globally and locally, locally has higher priority)
        $conditions = array_merge($this->conditions, $conditions);

        // Split and normalize uri and template
        $given = array_filter(explode('/', $uri));
        $rules = array_filter(explode('/', $template));

        // Leave if uri and template do not contain the same number of
        // elements
        if (count($given) !== count($rules)) {
            return false;
        }

        // Combine uri and template element-wise (simplifies iteration)
        $combined = array_combine($rules, $given);

        // Iterate over uri and template and compare element by element
        foreach ($combined as $rule => $actual) {
            if ($rule[0] === ':') {
                // Rule is a placeholder
                $parameter_name = substr($rule, 1);

                if (isset($conditions[$parameter_name])
                    && !preg_match($conditions[$parameter_name], $actual))
                {
                    return false;
                }

                $parameters[$parameter_name] = $actual;
            } elseif ($actual !== $rule) {
                // Elements do not match
                $parameters = array();
                return false;
            }
        }

        return true;
    }

    /**
     * Dispatches an uri across the defined routes.
     *
     * @param mixed  $uri    Uri to dispatch (defaults to path info)
     * @param String $method Request method (defaults to actual method or GET)
     */
    public function dispatch($uri = null, $method = null)
    {
        // Default URI to path info or given default uri
        if ($uri === null) {
            $uri = $_SERVER['PATH_INFO'];
        }

        // Normalize method
        $method = strtolower($method ?: $_SERVER['REQUEST_METHOD'] ?: 'get');

        // Content negotiation
        if (!$this->content_renderer) {
            foreach ($this->renderers as $renderer) {
                if ($renderer->shouldRespondTo($uri)) {
                    $this->content_renderer = $renderer;
                    break;
                }
            }
            if (!$this->content_renderer) {
                $this->content_renderer = $this->default_renderer ?: reset($this->renderers);
            }
        }

        // Try to dispatch the uri to all routes registered for the current
        // request method
        $handler    = false;
        $parameters = array();
        if (isset($this->routes[$method])) {
            if ($this->content_renderer->extension() && strpos($uri, $this->content_renderer->extension()) !== false) {
                $uri = substr($uri, 0, -strlen($this->content_renderer->extension()));
            }

            foreach ($this->routes[$method] as $uri_template => $route) {
                if ($this->uriMatchesTemplate($uri, $uri_template, $prmtrs, $route['conditions'])) {
                    $handler    = $route['handler'];
                    $parameters = $prmtrs;
                    break;
                }
            }
        }

        // Throw exception if no route matches
        if (!$handler) {
            throw new RouterException('No route matches your request "' . strtoupper($method) . ':/' . $uri . '".', 404);
        }

        // Execute the request handler.
        $result = $this->execute($handler, $parameters);

        // Set Content-Type header
        if (!headers_sent()) {
            header('Content-Type: ' . $this->content_renderer->contentType());
        }

        // Output result
        echo $this->content_renderer->render($result, $this);
    }

    protected function execute($handler, $parameters)
    {
        $result = call_user_func_array($handler, $parameters);
        if ($result instanceof Collection) {
            $result = $result->toArray();
        }
        return $result;
    }

    /**
     * Registers a content renderer.
     *
     * @param ContentRenderer $renderer   Instance of a content renderer
     * @param boolean         $is_default Set this renderer as default?
     * @return Router Returns instance of itself to allow chaining
     */
    public function registerRenderer(ContentRenderer $renderer, $is_default = false)
    {
        $this->renderers[$renderer->extension()] = $renderer;
        if ($is_default) {
            $this->default_renderer = $renderer;
        }

        // Reset current content renderer
        $this->content_renderer = false;

        return $this;
    }

    /**
     * Forces a certain content renderer.
     *
     * @param String $identifier Identifier for the chosen content type. This
     *                           is either the associated file extension or
     *                           the associated content type of the content
     *                           type that is supposed to be forced.
     * @return Router Returns instance of it self to allow chaining
     * @throws RouterException If no renderer could be identified
     */
    public function forceRenderer($identifier)
    {
        $identified = false;
        foreach ($this->renderers as $renderer) {
            if (in_array($identifier, array($renderer->extension(), $renderer->contentType()))) {
                $this->content_renderer = $renderer;

                $identified = true;
                break;
            }
        }

        if (!$identified) {
            throw new RouterException('No renderer could be identified by "' . $identifier. '"');
        }

        return $this;
    }
}
