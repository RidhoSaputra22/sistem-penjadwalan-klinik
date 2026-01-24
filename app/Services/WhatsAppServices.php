<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class WhatsAppServices
{
    private static function apiUrl(): string
    {
        return (string) config('services.whatsapp.api_url', 'http://127.0.0.1:5000/send');
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public static function sendPayload(array $payload): array
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::timeout(30)
            ->acceptJson()
            ->asJson()
            ->post(self::apiUrl(), $payload);

        if ($response->failed()) {
            throw new Exception('Failed to send message: ' . $response->body());
        }

        $json = $response->json();

        return is_array($json) ? $json : ['response' => $json];
    }

    /**
     * Mengirim/menangani pesan WhatsApp berdasarkan payload JSON dari proses reservasi.
     *
     * Payload yang diharapkan:
     * - user_email   (string) : Email pengguna penerima/terkait reservasi.
     * - booking_code (string) : Kode booking unik reservasi.
     * - paket_slug   (string) : Slug paket yang dipesan.
     *
     * @param  array<string, mixed>  $payload  Data payload (hasil decode JSON) berisi:
     *                                        - user_email (string)
     *                                        - booking_code (string)
     *                                        - paket_slug (string)
     * @return mixed  Respons dari proses pengiriman/penanganan WhatsApp (bergantung implementasi).
     *
     * @throws \InvalidArgumentException  Jika salah satu field wajib tidak tersedia/format tidak valid.
     */
    public static function sendMessage($userEmail, $bookingCode, $paketSlug)
    {
        $payload = [
            'user_email' => $userEmail,
            'booking_code' => $bookingCode,
            'paket_slug' => $paketSlug
        ];

        return self::sendPayload($payload);
    }
}
