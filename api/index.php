<?php
namespace {
    spl_autoload_register(function ($class) {
        $temp  = explode('\\', $class);
        $class = array_pop($temp);
        $path  = __DIR__ . '/lib' ;

        foreach ($temp as $dir) {
            $path .= DIRECTORY_SEPARATOR . strtolower($dir);
        }

        $base = $path . DIRECTORY_SEPARATOR . $class;
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
    if ($debug = true) {
        Router::getInstance()->registerRenderer(new DebugRenderer);
    }

    require 'routes.php';

    // A potential api exception will lead to an empty response with the
    // exception code and name as the http status.
    try {
        $uri = $_SERVER['PATH_INFO'];

        // Check version
        if (preg_match('~^/v(\d+)~i', $uri, $match)) {
            $version = $match[1];
            if ($version != VERSION) {
                throw new RouterException('Version not supported.');
            }
            $uri = substr($uri, strlen($match[0]));
        }

        // Actural dispatch
        Router::getInstance()->dispatch($uri);
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
