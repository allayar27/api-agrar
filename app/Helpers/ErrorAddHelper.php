<?php

namespace App\Helpers;

use App\Actions\TelegramCallMethod;
use App\Models\Error;
use Illuminate\Support\Facades\Http;
use Laravel\Envoy\Telegram;
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

    public static function sendErrorToTelegram(Error $error)
    {
        $chat_id = config('services.telegram.chat_id');

        $message = "*Error Report*\n";
        $message .= "*Message:* {$error->message}\n";
        $message .= "*File:* {$error->file}\n";
        $message .= "*Line:* {$error->line}\n";
        $message .= "*Code:* {$error->code}\n";
        $message .= "*Trace:*\n`{$error->trace}`";

        $url = "https://api.telegram.org/bot" . config('services.telegram.second_api_key') . "/sendMessage";

        Http::post($url, [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

}
