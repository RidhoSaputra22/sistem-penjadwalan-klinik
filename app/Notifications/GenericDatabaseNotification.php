<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class GenericDatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $message,
        protected ?string $kind,
        protected null|string|array $channel = null,

        protected array $extra = []
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $mailRoute = $notifiable->routeNotificationFor('mail', $this);
        $hasMailRoute = is_string($mailRoute)
            ? trim($mailRoute) !== ''
            : (is_array($mailRoute) ? count(array_filter($mailRoute, fn ($v) => is_string($v) && trim($v) !== '')) > 0 : false);

        if ($hasMailRoute) {
            $channels[] = 'mail';
        } else {
            Log::warning('Skipping mail channel: notifiable has no mail route.', [
                'notification' => static::class,
                'notifiable_type' => $notifiable::class,
                'notifiable_id' => method_exists($notifiable, 'getKey') ? $notifiable->getKey() : null,
            ]);
        }

        if ((bool) config('services.whatsapp.enabled', false)) {
            $channels[] = WhatsAppChannel::class;
        }

        if ($this->channel !== null) {
            $channels = $this->channel;
        }

        return $channels;
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        Log::info('invoke toMail');

        $mailMessage = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Notification')
            ->greeting('Hallo '.($notifiable->name ?? ''))

            ->line($this->message)

            ->line('Silakan cek detail booking Anda di sini')
            ->line(route('user.dashboard', ['tab' => 'history']))
            ->line('Terima kasih telah menggunakan aplikasi kami.')
            ->salutation('Salam Hormat, '.(config('app.name', 'Our Application')));

        return $mailMessage;
    }

    /**
     * @return array<string,mixed>|string|null
     */
    public function toWhatsApp(object $notifiable): array|string|null
    {
        // Flexible payload: WhatsApp API may accept extra fields (message, action_url, etc.)

        $phone = isset($notifiable->phone) && is_string($notifiable->phone) && trim($notifiable->phone) !== ''
            ? trim($notifiable->phone)
            : null;

        $message = 'Hallo '.($notifiable->name ?? '').",\n\n";
        $message .= $this->message."\n\n";
        $message .= "Silakan cek detail booking Anda di sini:\n";
        $message .= route('user.dashboard', ['tab' => 'history'])."\n\n";
        $message .= "Terima kasih telah menggunakan aplikasi kami.\n";

        $payload = [
            'message' => $message,
        ];

        if ($phone !== null) {
            $payload['phone'] = $phone;
        }

        return array_merge($payload, $this->extra);
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'kind' => $this->kind,
            'message' => $this->message,
        ], $this->extra);
    }
}
