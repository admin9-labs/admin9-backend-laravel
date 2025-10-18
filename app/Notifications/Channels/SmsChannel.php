<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toSms($notifiable);

        // Log SMS for development/testing
        // In production, integrate with an SMS provider (e.g., Twilio, Aliyun, Tencent Cloud)
        Log::info('SMS Notification', [
            'to' => $message['to'],
            'content' => $message['content'],
        ]);
    }
}
