<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WithdrawMoney;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawMoneyNotification extends Notification
{
    use Queueable;

    public $withdrawal;
    public $provider;
    public function __construct(WithdrawMoney $withdrawal, User $provider)
    {
        $this->withdrawal = $withdrawal;
        $this->provider = $provider;
    }

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
            'provider_id' => $this->provider->id,
            'provider_name' => $this->provider->full_name,
            'image' => $this->provider->image,
            'amount' => $this->withdrawal->amount,
            'withdrawal_id' => $this->withdrawal->id,
            'message' => 'Requested for money withdraw',
        ];
    }
}
