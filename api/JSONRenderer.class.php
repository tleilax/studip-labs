<?php
/**
 * Content renderer for json content.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2+
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

    public function render($data)
    {
        return json_encode($data);
    }
}
