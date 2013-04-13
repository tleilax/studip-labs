<?php
class RouterException extends Exception
{
    public function __construct($message = '', $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}