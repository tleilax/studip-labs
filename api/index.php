<?php
namespace {
    // TODO: Adjust global autoload to support namespaces
    spl_autoload_register(function ($class) {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $path  = __DIR__ . DIRECTORY_SEPARATOR
               . 'lib' . DIRECTORY_SEPARATOR
               . strtolower(dirname($class));

        $base = $path . DIRECTORY_SEPARATOR . basename($class);
        if (file_exists($base . '.class.php')) {
            require $base . '.class.php';
        } elseif (file_exists($base . '.php')) {
            require $base . '.php';
        }
    });
}
namespace API {
    const VERSION = '2';

    // Register content renderers
    Router::getInstance()->registerRenderer(new JSONRenderer, true);
    Router::getInstance()->registerRenderer(new PHPRenderer);

    require 'routes.php';

    // A potential api exception will lead to an empty response with the
    // exception code and name as the http status.
    try {
        $uri    = $_SERVER['PATH_INFO'];
        $method = $_SERVER['REQUEST_METHOD'];

        // Check version
        if (defined('API\\VERSION') && preg_match('~^/v(\d+)~i', $uri, $match)) {
            $version = $match[1];
            if ($version != VERSION) {
                throw new RouterException('Version not supported.');
            }

            header('X-API-Version: ' . VERSION);
            $uri = substr($uri, strlen($match[0]));
        }

        // Debug stuff
        // TODO: Replace this with Studip\ENV === 'development'
        if ($debug = true) {
            Router::getInstance()->registerRenderer(new DebugRenderer);
            if (isset($_REQUEST['METHOD'])) {
                $method = $_REQUEST['METHOD'];
                unset($_REQUEST['METHOD']);
            }
        }

        // Actural dispatch
        Router::getInstance()->dispatch($uri, $method);
    } catch (RouterException $e) {
        $status = sprintf('%s %u %s',
                          $_SERVER['SERVER_PROTOCOL'] ?: 'HTTP/1.1',
                          $e->getCode(),
                          $e->getMessage());
        $status = trim($status);
        if (!headers_sent()) {
            header($status, true, $e->getCode());
            echo $status;
        } else {
            echo $status;
        }
    }
}
