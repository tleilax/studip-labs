<?php
/**
 * Router exception.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL3
 */

namespace API;
use \Exception;

class RouterException extends Exception
{
    public function __construct($message = '', $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
