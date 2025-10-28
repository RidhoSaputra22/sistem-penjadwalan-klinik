<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Holiday;

class FetchHolidays extends Command
{
    protected $signature = 'holidays:fetch';
    protected $description = 'Ambil data hari libur nasional dari API libur.deno.dev dan simpan ke database';

    public function handle()
    {
        $this->info('Mengambil data hari libur dari API...');

        try {
            $response = Http::get('https://libur.deno.dev/api');

            if ($response->failed()) {
                $this->error('Gagal mengambil data dari API');
                return Command::FAILURE;
            }

            $holidays = $response->json();

            foreach ($holidays as $holiday) {
                Holiday::updateOrCreate(
                    ['date' => $holiday['date']],
                    [
                        'name' => $holiday['name'],
                        'full_day' => true,
                    ]
                );
            }

            $this->info('✅ Data hari libur berhasil diperbarui. Total: ' . count($holidays));
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
