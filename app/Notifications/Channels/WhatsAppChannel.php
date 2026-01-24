<?php

namespace App\Notifications\Channels;

use App\Services\WhatsAppServices;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {

        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        /** @var array<string,mixed>|string|null $payload */
        $payload = $notification->{'toWhatsApp'}($notifiable);

        if ($payload === null) {
            return;
        }

        if (is_string($payload)) {
            $payload = ['message' => $payload];
        }

        if (! is_array($payload)) {
            return;
        }

        if (! array_key_exists('user_email', $payload)) {
            $route = $notifiable->routeNotificationFor('whatsapp', $notification);
            if (is_string($route) && trim($route) !== '') {
                $payload['user_email'] = $route;
            }
        }

        $userEmail = (string) ($payload['user_email'] ?? '');
        if (trim($userEmail) === '') {
            return;
        }

        WhatsAppServices::sendPayload($payload);
    }
}
