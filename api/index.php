<?php
namespace API {
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

    $router = Router::getInstance();

    $router->registerRenderer(new JSONRenderer, true);
    $router->registerRenderer(new PHPRenderer);
    if ($debug = true) {
        $router->registerRenderer(new DebugRenderer);
    }

    $router->get('/get', function () { return 'get'; });
    $router->post('/post', function () { return 'post'; });
    $router->put('/put', function () { return 'put'; });
    $router->delete('/delete', function () { return 'delete'; });

    $router->describe(array(
        '/get' => 'GET method',
        '/post' => 'POST method',
        '/put' => 'PUT method',
        '/delete' => 'DELETE method',
    ));

    $router->get('/hello/:name', function ($name = 'world') {
        return sprintf('Hello %s!', $name);
    }, array('name' => '/^\w+$/'));

    $router->get('/lower/:what', 'strtolower');
    $router->get('/md5/:what', 'md5');

    $router->get('/collection', function () {
        $data = array(
            array('name' => 'foo'),
            array('name' => 'bar'),
        );
        return Collection::fromArray($data)->paginate('/collection?offset=%1u&limit=%2u', 317, 20, 20);
    });

    $router->dispatch();
}
