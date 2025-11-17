<?php 

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendSms(string $recipient, string $message, User|null $user = null): void
    {
        Notification::create([
            'type'       => 'sms',
            'recipient'  => $recipient,
            'user_id'    => $user?->id,
            'status'    => 'dispatched',
            'data'       => json_encode([
                'body' => $message
            ] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("SMS sent to {$recipient} with message: {$message}");

        //Integrate with SMS gateway here
    }
}