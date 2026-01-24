<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenericNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_send_notification()
    {
        $message = 'Halo {{nama_klien}},

Terima kasih telah melakukan reservasi di Klinik Goaria.
Reservasi Anda berhasil dibuat dan saat ini telah tercatat dalam sistem kami.

📅 Tanggal: {{tanggal}}
⏰ Waktu: {{waktu}}
📸 Layanan/Paket: {{nama_paket}}
📸 Kode Booking: {{kode_booking}}
📍 Lokasi: {{lokasi}}
Tim kami akan melakukan konfirmasi lanjutan sesuai jadwal yang telah Anda pilih. Mohon pastikan data reservasi sudah sesuai. Jika terdapat pertanyaan atau perubahan, jangan ragu untuk menghubungi kami melalui WhatsApp ini.

Terima kasih atas kepercayaan Anda.
Klinik Goaria
';

        $user = User::factory()->create([
            'email' => 'saputra22022@gmail.com',
        ]);

        $user->notify(new \App\Notifications\GenericDatabaseNotification(
            message: $message,
            kind: 'test',

            channel: ['database', 'mail'],
            extra: [],

        ));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'data->kind' => 'test',
            'data->message' => $message,
        ]);

    }
}
