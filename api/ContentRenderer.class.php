<?php
/**
 *
 */

abstract class ContentRenderer
{
    /**
     *
     */
    abstract public function contentType();

    /**
     *
     */
    abstract public function extension();

    /**
     *
     */
    abstract public function render($data);

    /**
     *
     */
    public function shouldRespondTo($filename, $media_range = null)
    {
        // If no media range is passed, evalute http header "Accept"
        if ($media_range === null && isset($_SERVER['ACCEPT'])) {
            $media_range = reset(explode(';', $_SERVER['ACCEPT']));
        }

        // Test if either the filename has the appropriate extension or
        // if the client accepts the content type
        return fnmatch('*' . $this->extension(), $filename)
            || ($media_range && fnmatch($media_range, $this->contentType()));
    }
}
