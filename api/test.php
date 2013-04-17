<?php
class RouterTest extends PHPUnit_Framework_TestCase
{
    private function dispatch($url, $method = 'get')
    {
        ob_start();
        API\Router::getInstance()->dispatch($url, $method);
        return ob_get_clean();
    }

    public function setup()
    {
        require_once 'lib/api/Router.class.php';
        require_once 'lib/api/RouterException.class.php';
        require_once 'lib/api/ContentRenderer.class.php';
        require_once 'lib/api/JSONRenderer.class.php';
        require_once 'lib/api/PHPRenderer.class.php';

        API\Router::getInstance()
            ->get('/test', function () { return 'test'; })
            ->get('/array', function () { return array('test' => 'array'); })
            ->get('/get', function () { return 'get'; })
            ->post('/post', function () { return 'post'; })
            ->put('/put', function () { return 'put'; })
            ->delete('/delete', function () { return 'delete'; });
    }

    public function testMethods()
    {
        $this->assertEquals($this->dispatch('/get', 'get'), 'get');
        $this->assertEquals($this->dispatch('/post', 'post'), 'post');
        $this->assertEquals($this->dispatch('/put', 'put'), 'put');
        $this->assertEquals($this->dispatch('/delete', 'delete'), 'delete');
    }

    public function testURLMatching()
    {
        $router = API\Router::getInstance();
        
        $this->assertTrue($router->uriMatchesTemplate('/hello', '/hello'));
        $this->assertTrue($router->uriMatchesTemplate('hello', '/hello'));
        $this->assertTrue($router->uriMatchesTemplate('/hello', 'hello'));
        $this->assertTrue($router->uriMatchesTemplate('/hello/////', '/hello'));
        $this->assertTrue($router->uriMatchesTemplate('/hello/world', '/hello/world'));
        $this->assertTrue($router->uriMatchesTemplate('/hello/world', '/hello/:name'));

        $this->assertTrue($router->uriMatchesTemplate('/hello/world', '/:greeting/:name', $parameters));
        $this->assertEquals($parameters, array('greeting' => 'hello', 'name' => 'world'));

        $this->assertFalse($router->uriMatchesTemplate('/hello/world.json', '/hello/world'));
        $this->assertFalse($router->uriMatchesTemplate('/hello/mr/jones', '/hello/mr'));
        $this->assertFalse($router->uriMatchesTemplate('/hello/mr', '/hello/mr/jones'));
    }
        
    public function testURLMatchingOptional()
    {
        $this->markTestIncomplete();

        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello(/:name)'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '(/:greeting/:name)'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '(/:greeting(/:name))'));

        $this->assertFalse(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '/:greeting/(:name)'));
    }

    public function testLocalConditions()
    {
        $router = API\Router::getInstance();
        
        $this->assertTrue($router->uriMatchesTemplate('/hello/foooooo', '/hello/:name', $z, array('name' => '/^fo+$/')));
        $this->assertFalse($router->uriMatchesTemplate('/hello/world', '/hello/:name', $z, array('name' => '/^fo+$/')));
    }

    public function testGlobalConditions()
    {
        $router = API\Router::getInstance();

        $router->setConditions(array('foo' => '/^foo$/', 'bar' => '/^\d+/'));
        $this->assertTrue($router->uriMatchesTemplate('/foo/123', '/:foo/:bar'));
        $this->assertFalse($router->uriMatchesTemplate('/foo/bar', '/:foo/:bar'));
    }

    public function testJSONRenderer()
    {
        $renderer = new API\JSONRenderer();

        $this->assertTrue($renderer->shouldRespondTo('filename.json'));
        $this->assertFalse($renderer->shouldRespondTo('filename.txt'));

        $this->assertTrue($renderer->shouldRespondTo('', 'application/json'));
        $this->assertTrue($renderer->shouldRespondTo('', 'application/*'));
        $this->assertTrue($renderer->shouldRespondTo('', '*/*'));

        $this->assertFalse($renderer->shouldRespondTo('', 'image/jpeg'));
        $this->assertFalse($renderer->shouldRespondTo('', 'image/*'));
        $this->assertFalse($renderer->shouldRespondTo('', '*/jpeg'));

        API\Router::getInstance()->registerRenderer($renderer);

        $this->assertEquals($this->dispatch('/test.json'), json_encode('test'));
        $this->assertEquals($this->dispatch('/array.json'), json_encode(array('test' => 'array')));
    }

    public function testJSONRendererForcedByExtension()
    {
        API\Router::getInstance()->registerRenderer(new API\JSONRenderer);
        API\Router::getInstance()->forceRenderer('.json');
        $this->assertEquals($this->dispatch('/test'), json_encode('test'));
    }

    public function testJSONRendererForcedByContentType()
    {
        API\Router::getInstance()->registerRenderer(new API\JSONRenderer);
        API\Router::getInstance()->forceRenderer('application/json');
        $this->assertEquals($this->dispatch('/test'), json_encode('test'));
    }

    public function testJSONRendererDefault()
    {
        API\Router::getInstance()->registerRenderer(new API\JSONRenderer, true);
        $this->assertEquals($this->dispatch('/test'), json_encode('test'));
    }

    public function testPHPRenderer()
    {
        $renderer = new API\PHPRenderer();

        $this->assertTrue($renderer->shouldRespondTo('filename.php'));
        $this->assertFalse($renderer->shouldRespondTo('filename.txt'));

        $this->assertTrue($renderer->shouldRespondTo('', 'application/x-php'));
        $this->assertTrue($renderer->shouldRespondTo('', 'application/*'));
        $this->assertTrue($renderer->shouldRespondTo('', '*/*'));

        $this->assertFalse($renderer->shouldRespondTo('', 'image/jpeg'));
        $this->assertFalse($renderer->shouldRespondTo('', 'image/*'));
        $this->assertFalse($renderer->shouldRespondTo('', '*/jpeg'));

        API\Router::getInstance()->registerRenderer($renderer);

        $this->assertEquals($this->dispatch('/test.php'), serialize('test'));
        $this->assertEquals($this->dispatch('/array.php'), serialize(array('test' => 'array')));
    }

    public function testPHPRendererDefault()
    {
        API\Router::getInstance()->registerRenderer(new API\PHPRenderer, true);
        $this->assertEquals($this->dispatch('/test'), serialize('test'));
    }
}