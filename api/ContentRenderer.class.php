<?php
/**
 *
 */

abstract class ContentRenderer
{
    /**
     * Returns an associated content type.
     */
    abstract public function contentType();

    /**
     * Returns an associated extension.
     */
    abstract public function extension();

    /**
     * Actual data rendering function.
     *
     * @param mixed $data    Data to render
     * @param Router $router Related router object
     */
    abstract public function render($data, $router);

    /**
     * Detects whether the renderer should respond to either a certain
     * filename (tests by extension) or to a certain media range.
     *
     * @param String $filename    Filename to test against
     * @param mixed  $media_range Media range to test against (optional,
     *                            defaults to request's accept header if set)
     * @return bool Returns whether the renderer should respond
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
