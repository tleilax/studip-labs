<?php
/**
 * @see http://blog.sosedoff.com/2009/07/04/simpe-php-url-routing-controller/
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
    
    protected $conditions = array();

    /**
     * Constructs the router.
     */
    protected function __construct()
    {
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
     * @param mixed  $uri     Uri to dispatch (defaults to path info)
     * @param String $default Default value if no uri is given
     * @throws RuntimeException If the uri could not be dispatched
     */
    public function dispatch($uri = null, $default = '')
    {
        // Default URI to path info or given default uri
        if ($uri === null) {
            $uri = $_SERVER['PATH_INFO'] ?: $default;
        }
        // Normalize method
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        // Content negotiation
        $content_type     = 'text/html';
        $content_renderer = false;
        foreach ($this->renderers as $renderer) {
            if ($renderer->shouldRespondTo($uri)) {
                $content_renderer = $renderer;
                $uri = substr($uri, 0, -strlen($renderer->extension()));
                break;
            }
        }

        // Try to dispatch the uri to all routes registered for the current
        // request method
        $handler    = false;
        $parameters = array();
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $uri_template => $route) {
                $conditions = array_merge($this->conditions, $route['conditions']);
                if ($this->uriMatchesTemplate($uri, $uri_template, $prmtrs, $conditions)) {
                    $handler    = $route['handler'];
                    $parameters = $prmtrs;
                    break;
                }
            }
        }

        // Throw exception if no route matches
        if (!$handler) {
            throw new RuntimeException('No route matches your request.');
        }
        
        // Call the request handler. A potential api exception will lead to
        // an empty response with the exception code and name as the http status.
        try {
            $result = call_user_func_array($handler, $parameters);

            // Set Content-Type header
            $content_type = $content_renderer
                          ? $content_renderer->contentType()
                          : 'text/html';
            header('Content-Type: ' . $contentType);

            // Output result
            if ($content_renderer) {
                $result = $content_renderer->render($result);
            }
            echo $result;
        } catch (RouterException $e) {
            $status = sprintf('%s %u %s',
                              $_SERVER['SERVER_PROTOCOL'] ?: 'HTTP/1.1',
                              $e->getCode(),
                              $e->getMessage());
            $status = trim($status);
            header($status, true, $e->getCode());
            die;
        }
    }

    /**
     * Registers a content renderer.
     *
     * @param ContentRenderer $renderer Instance of a content renderer
     * @return Router Returns instance of itself to allow chaining
     */
    public function registerRenderer(ContentRenderer $renderer)
    {
        $this->renderers[] = $renderer;
        return $this;
    }
}
