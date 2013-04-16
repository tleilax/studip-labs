<?php
/**
 * Debug content renderer.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL3
 */

namespace API;

class DebugRenderer extends ContentRenderer
{
    /**
     * Returns an associated content type.
     */
    public function contentType()
    {
        return 'text/plain';
    }

    /**
     * Returns an associated extension.
     */
    public function extension()
    {
        return '.debug';
    }

    /**
     * Actual data rendering function.
     *
     * @param mixed $data    Data to render
     * @param Router $router Related router object
     */
    public function render($data, $router)
    {
        $debug = function ($label, $data) {
            echo str_pad('', 78, '=') . PHP_EOL;
            echo str_pad('- ' . $label, 77, ' ') . '-' . PHP_EOL;
            echo str_pad('', 78, '=') . PHP_EOL;
            var_dump($data);
            echo PHP_EOL;
        };
        
        ob_start();
        $debug('Data', $data);
        $debug('Request', $GLOBALS['_' . $_SERVER['REQUEST_METHOD']]);
        return ob_get_clean();
    }
}
