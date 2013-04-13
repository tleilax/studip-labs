<?
require 'Router.class.php';
require 'RouterException.class.php';
require 'ContentRenderer.class.php';
require 'JSONRenderer.class.php';
require 'PHPRenderer.class.php';

$router = Router::getInstance();

$router->registerRenderer(new JSONRenderer(), true);
$router->registerRenderer(new PHPRenderer());

$router->get('/get', function () { return 'get'; });
$router->post('/post', function () { return 'post'; });
$router->put('/put', function () { return 'put'; });
$router->post('/delete', function () { return 'delete'; });

$router->get('/hello/:name', function ($name = 'world') {
    return sprintf('Hello %s!', $name);
}, array('name' => '/^\w+$/'));

$router->get('/lower/:what', 'strtolower');
$router->get('/md5/:what', 'md5');

$router->dispatch();
