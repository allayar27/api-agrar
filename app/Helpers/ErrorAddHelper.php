<?php

namespace App\Helpers;

use App\Models\Error;
use Throwable;

class ErrorAddHelper
{
    public static function logException(Throwable $e)
    {
        Error::create([
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'code' => $e->getCode(),
        ]);
    }
}
