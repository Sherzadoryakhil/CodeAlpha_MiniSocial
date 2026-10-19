<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

// Browsers send a line break from a text box as two characters ("\r\n"), but count it
// as one. Turning it into one character ("\n") keeps the 1000 / 500 / 160 character
// limits the same on the screen and on the server.
class NormalizeLineEndings extends TransformsRequest
{
    protected function transform($key, $value)
    {
        return is_string($value) ? str_replace(["\r\n", "\r"], "\n", $value) : $value;
    }
}
