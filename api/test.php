<?php
class RouterTest extends PHPUnit_Framework_TestCase
{
    private function dispatch($url, $method = 'get')
    {
        ob_start();
        Router::getInstance()->dispatch($url, $method);
        return ob_get_clean();
    }

    protected $router;

    public function setup()
    {
        require_once 'Router.class.php';
        require_once 'RouterException.class.php';
        require_once 'ContentRenderer.class.php';
        require_once 'JSONRenderer.class.php';
        require_once 'PHPRenderer.class.php';

        Router::getInstance()
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
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/hello', '/hello'));
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('hello', '/hello'));
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/hello', 'hello'));
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/hello/////', '/hello'));
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello/world'));
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello/:name'));

        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/hello/world', '/:greeting/:name', $parameters));
        $this->assertEquals($parameters, array('greeting' => 'hello', 'name' => 'world'));

        $this->assertFalse(Router::getInstance()->uriMatchesTemplate('/hello/world.json', '/hello/world'));
        $this->assertFalse(Router::getInstance()->uriMatchesTemplate('/hello/mr/jones', '/hello/mr'));
        $this->assertFalse(Router::getInstance()->uriMatchesTemplate('/hello/mr', '/hello/mr/jones'));
    }

    public function testConditions()
    {
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/hello/foooooo', '/hello/:name', $z, array('name' => '/^fo+$/')));
        $this->assertFalse(Router::getInstance()->uriMatchesTemplate('/hello/world', '/hello/:name', $z, array('name' => '/^fo+$/')));

        Router::getInstance()->setConditions(array('foo' => '/^foo$/', 'bar' => '/^\d+/'));
        $this->assertTrue(Router::getInstance()->uriMatchesTemplate('/foo/123', '/:foo/:bar'));
        $this->assertFalse(Router::getInstance()->uriMatchesTemplate('/foo/bar', '/:foo/:bar'));
    }

    public function testJSONRenderer()
    {
        $renderer = new JSONRenderer();
        
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
        Router::getInstance()->registerRenderer(new JSONRenderer);
        $this->assertEquals($this->dispatch('/test.json'), json_encode('test'));
    }

    public function testJSONRendererArrayResponse()
    {
        Router::getInstance()->registerRenderer(new JSONRenderer);
        $this->assertEquals($this->dispatch('/array.json'), json_encode(array('test' => 'array')));
    }

    public function testJSONRendererForcedByExtension()
    {
        Router::getInstance()->registerRenderer(new JSONRenderer);
        Router::getInstance()->forceRenderer('.json');
        $this->assertEquals($this->dispatch('/test'), json_encode('test'));
    }

    public function testJSONRendererForcedByContentType()
    {
        Router::getInstance()->registerRenderer(new JSONRenderer);
        Router::getInstance()->forceRenderer('application/json');
        $this->assertEquals($this->dispatch('/test'), json_encode('test'));
    }

    public function testJSONRendererDefault()
    {
        Router::getInstance()->registerRenderer(new JSONRenderer, true);
        $this->assertEquals($this->dispatch('/test'), json_encode('test'));
    }

    public function testPHPRenderer()
    {
        $renderer = new PHPRenderer();
        
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
        Router::getInstance()->registerRenderer(new PHPRenderer);
        $this->assertEquals($this->dispatch('/test.php'), serialize('test'));
    }

    public function testPHPRendererArrayResponse()
    {
        Router::getInstance()->registerRenderer(new PHPRenderer);
        $this->assertEquals($this->dispatch('/array.php'), serialize(array('test' => 'array')));
    }

    public function testPHPRendererDefault()
    {
        Router::getInstance()->registerRenderer(new PHPRenderer, true);
        $this->assertEquals($this->dispatch('/test'), serialize('test'));
    }
}