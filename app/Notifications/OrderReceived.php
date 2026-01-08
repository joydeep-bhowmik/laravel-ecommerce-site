<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReceived extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail']; // You can also add 'database', 'broadcast', or 'nexmo' for SMS
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Received')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your order with ID #' . $this->order->uid . ' has been received.')
            ->action('View Order', route('admin.orders.view', ['id' => $this->order->uid]))
            ->line('Thank you for shopping with us!');
    }

    /**
     * Get the array representation of the notification (for database storage).
     */
    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->uid,
            'message'  => 'Your order has been received.',
        ];
    }
}
