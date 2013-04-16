<?php
/**
 * Default content renderer class (outputs text/plain).
 *
 * Content renderers are output filters that can reshape data before it
 * is sent to the client.
 * Each content renderer is associated with a certain content type and a
 * certain file extension. This is neccessary for content negotiation.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL3
 */

namespace API;

class ContentRenderer
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
        return '';
    }

    /**
     * Actual data rendering function.
     *
     * @param mixed $data    Data to render
     * @param Router $router Related router object
     */
    public function render($data, $router)
    {
        return $data;
    }

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
        return ($this->extension() && fnmatch('*' . $this->extension(), $filename))
            || ($media_range && fnmatch($media_range, $this->contentType()));
    }
}
