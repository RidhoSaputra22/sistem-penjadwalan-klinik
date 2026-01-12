<?php

namespace App\Jobs;

use App\Services\WhatsAppServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppBookingMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $userEmail,
        public readonly string $bookingCode,
        public readonly string $paketSlug,
    ) {
    }

    public function handle(): void
    {
        if (trim($this->userEmail) === '') {
            Log::warning('Skip WhatsApp send: empty userEmail', [
                'booking_code' => $this->bookingCode,
            ]);
            return;
        }

        try {
            WhatsAppServices::sendMessage(
                $this->userEmail,
                $this->bookingCode,
                $this->paketSlug,
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send WhatsApp message (queued)', [
                'booking_code' => $this->bookingCode,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
