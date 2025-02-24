<?php
namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
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
        $time = Carbon::parse($this->order->created_at)->diffInDays(Carbon::now()) == 1
        ? Carbon::parse($this->order->created_at)->format('g:ia, yesterday')
        : Carbon::parse($this->order->created_at)->format('g:ia, today');

        return [
            'message'       => 'New order',
            'order_id'      => $this->order->id,
            'time'          => $time,
            'service_title' => $this->order->service->title,
            'user_name'     => $this->order->user->full_name,
            'address'       => $this->order->user->address,
        ];

    }
}
