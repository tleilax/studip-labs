<?php
/**
 * Content renderer for serialized php content.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL3
 */

class PHPRenderer extends ContentRenderer
{
    public function contentType()
    {
        return 'application/x-php';
    }

    public function extension()
    {
        return '.php';
    }

    public function render($data, $router)
    {
        return serialize($data);
    }
}
