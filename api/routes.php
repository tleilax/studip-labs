<?php
namespace API {
    $router = Router::getInstance();

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
}
