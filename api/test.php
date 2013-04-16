<?php
class RouterTest extends PHPUnit_Framework_TestCase
{
    private function dispatch($url, $method = 'get')
    {
        ob_start();
        API\Router::getInstance()->dispatch($url, $method);
        return ob_get_clean();
    }

    protected $router;

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

    public function testGet()
    {
        $this->assertEquals($this->dispatch('/get', 'get'), 'get');
    }

    public function testPost()
    {
        $this->assertEquals($this->dispatch('/post', 'post'), 'post');
    }

    public function testPut()
    {
        $this->assertEquals($this->dispatch('/put', 'put'), 'put');
    }

    public function testDelete()
    {
        $this->assertEquals($this->dispatch('/delete', 'delete'), 'delete');
    }

    public function testURLMatching()
    {
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello', '/hello'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('hello', '/hello'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello', 'hello'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/////', '/hello'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello/world'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello/:name'));

        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '/:greeting/:name', $parameters));
        $this->assertEquals($parameters, array('greeting' => 'hello', 'name' => 'world'));

        $this->assertFalse(API\Router::getInstance()->uriMatchesTemplate('/hello/world.json', '/hello/world'));
        $this->assertFalse(API\Router::getInstance()->uriMatchesTemplate('/hello/mr/jones', '/hello/mr'));
        $this->assertFalse(API\Router::getInstance()->uriMatchesTemplate('/hello/mr', '/hello/mr/jones'));
    }
        
    public function testURLMatchingOptional()
    {
        $this->markTestIncomplete();

        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello(/:name)'));
    }

    public function testConditionMatch()
    {
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/hello/foooooo', '/hello/:name', $z, array('name' => '/^fo+$/')));
    }

    public function testConditionFail()
    {
        $this->assertFalse(API\Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello/:name', $z, array('name' => '/^fo+$/')));
    }

    public function testGlobalConditionMatch()
    {
        API\Router::getInstance()->setConditions(array('foo' => '/^foo$/', 'bar' => '/^\d+/'));
        $this->assertTrue(API\Router::getInstance()->uriMatchesTemplate('/foo/123', '/:foo/:bar'));
    }

    public function testGlobalConditionFail()
    {
        API\Router::getInstance()->setConditions(array('foo' => '/^foo$/', 'bar' => '/^\d+/'));
        $this->assertFalse(API\Router::getInstance()->uriMatchesTemplate('/foo/bar', '/:foo/:bar'));
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
    }

    public function testJSONRendererResponse()
    {
        API\Router::getInstance()->registerRenderer(new API\JSONRenderer);
        $this->assertEquals($this->dispatch('/test.json'), json_encode('test'));
    }

    public function testJSONRendererArrayResponse()
    {
        API\Router::getInstance()->registerRenderer(new API\JSONRenderer);
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
    }

    public function testPHPRendererResponse()
    {
        API\Router::getInstance()->registerRenderer(new API\PHPRenderer);
        $this->assertEquals($this->dispatch('/test.php'), serialize('test'));
    }

    public function testPHPRendererArrayResponse()
    {
        API\Router::getInstance()->registerRenderer(new API\PHPRenderer);
        $this->assertEquals($this->dispatch('/array.php'), serialize(array('test' => 'array')));
    }

    public function testPHPRendererDefault()
    {
        API\Router::getInstance()->registerRenderer(new API\PHPRenderer, true);
        $this->assertEquals($this->dispatch('/test'), serialize('test'));
    }
}