<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
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
        $message = 'This is a test notification message.';

        $user = User::factory()->create([
            'email' => 'saputra22022@gmail.com',
            'phone' => '081344968521',
        ]);

        $user->notify(new GenericDatabaseNotification(
            message: $message,
            kind: 'test',

            extra: [],

        ));

        $this->assertTrue(true);

    }
}
