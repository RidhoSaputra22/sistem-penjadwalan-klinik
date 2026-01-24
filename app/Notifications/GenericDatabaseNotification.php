<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GenericDatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $message,
        protected ?string $kind,
        protected ?string $channel = null,

        protected array $extra = []
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if ((bool) config('services.whatsapp.enabled', false)) {
            $channels[] = WhatsAppChannel::class;
        }

        if ($this->channel !== null) {
            $channels[] = $this->channel;
        }

        return $channels;
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $mailMessage = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Notification')
            ->line($this->message);

        if (isset($this->extra['action_url']) && isset($this->extra['action_text'])) {
            $mailMessage->action($this->extra['action_text'], $this->extra['action_url']);
        }

        return $mailMessage;
    }

    /**
     * @return array<string,mixed>|string|null
     */
    public function toWhatsApp(object $notifiable): array|string|null
    {
        // Flexible payload: WhatsApp API may accept extra fields (message, action_url, etc.)
        return array_merge([
            'message' => $this->message,
            'phone' => $notifiable->phone,
        ], $this->extra);
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'kind' => $this->kind,
            'message' => $this->message,
        ], $this->extra);
    }
}
