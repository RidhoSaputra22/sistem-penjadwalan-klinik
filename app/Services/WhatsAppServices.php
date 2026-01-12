<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class WhatsAppServices
{

    private const API_URL = 'http://127.0.0.1:5000/send';

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

        $response = Http::timeout(30)
            ->acceptJson()
            ->asJson()
            ->post(self::API_URL, $payload);

        if ($response->failed()) {
            // Handle error
            throw new Exception('Failed to send message: ' . $response->body());
        }

        return $response->json();
    }
}
