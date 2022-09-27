<?php

namespace App\Exceptions;

use Exception;

class CliException extends Exception
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        if ('local' !== env('app.env')) {
            set_exception_handler(function ($previous) {
                $text = '[' . $_SERVER['REQUEST_TIME'] . '] - [error] - [' . $previous->getCode() . '] - ' . $previous->getMessage() . PHP_EOL . PHP_EOL;
                if (env('app.debug')) echo $text;
                writelog($text);
            });
        }

        parent::__construct($message, $code, $previous);
    }
}