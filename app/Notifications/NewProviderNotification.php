<?php

namespace App\Notifications;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProviderNotification extends Notification
{
    use Queueable;

    protected $totalProviders;
    protected $currentDateTime;
    public function __construct()
    {
        $this->totalProviders = User::where('role', 'provider')->count();
        $this->currentDateTime = Carbon::now()->format('d/m/Y H:i a');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->totalProviders . ' New Provider Registered',
            'details' => 'Tawun Has ' . $this->totalProviders . ' New Providers.',
            'date_time' => $this->currentDateTime,
        ];
    }
}
