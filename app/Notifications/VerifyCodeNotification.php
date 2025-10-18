<?php

namespace App\Notifications;

use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $code,
        protected ?string $scene = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', SmsChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sceneName = $this->getSceneName();

        return (new MailMessage)
            ->subject(__('messages.verification_code_subject'))
            ->greeting(__('messages.verification_code_greeting'))
            ->line(__('messages.verification_code_intro', ['scene' => $sceneName]))
            ->line(__('messages.verification_code_display', ['code' => $this->code]))
            ->line(__('messages.verification_code_validity'))
            ->line(__('messages.verification_code_warning'));
    }

    public function toSms(object $notifiable): array
    {
        // Get the route notification value (phone number)
        $to = $notifiable->routeNotificationFor('sms');

        $sceneName = $this->getSceneName();

        return [
            'to' => $to,
            'content' => __('messages.sms_verification_code', [
                'scene' => $sceneName,
                'code' => $this->code,
            ]),
        ];
    }

    protected function getSceneName(): string
    {
        return match ($this->scene) {
            'bind_email' => __('messages.scene_bind_email'),
            'change_email' => __('messages.scene_change_email'),
            'unbind_email' => __('messages.scene_unbind_email'),
            'login_email' => __('messages.scene_login_email'),
            default => __('messages.scene_general'),
        };
    }
}
