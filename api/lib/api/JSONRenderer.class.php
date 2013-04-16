<?php
/**
 * Content renderer for json content.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL3
 */

class JSONRenderer extends ContentRenderer
{
    public function contentType()
    {
        return 'application/json';
    }

    public function extension()
    {
        return '.json';
    }

    public function render($data, $router)
    {
        return json_encode($data);
    }
}
