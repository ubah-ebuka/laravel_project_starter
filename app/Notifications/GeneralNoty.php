<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GeneralNoty extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private array $payload)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        DB::table('notifications')->insert([
            'type'       => $this->payload['type'] ?? 'email',
            'recipient'  => $this->payload['recipient'] ?? '',
            'user_id'    => $this->payload['user_id'],
            'status'    => 'dispatched',
            'data'       => json_encode([
                'subject' => $this->payload['subject'],
                'body' => $this->payload['data']
            ] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->view('mail.'.$this->payload['view'], ['data' => $this->payload['data']])
            ->from(config('mail.from.address'), config('app.name'))
            ->subject($this->payload['subject']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if(app()->environment('local', 'staging', 'development')) {
            return [
                'type' => $this->payload['type'],
                'recipient' => $this->payload['recipient'],
                'user_id' => $this->payload['user_id'],
                'data' => [
                    'subject' => $this->payload['subject'],
                    'message' => $this->payload['message']
                ]
            ];
        }

        return [];
    }
}
