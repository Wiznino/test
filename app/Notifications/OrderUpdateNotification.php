<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public string $message)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing('items');

        $mail = (new MailMessage)
            ->subject('ATU Eats order #'.$this->order->id.' update')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->message)
            ->line('Order #'.$this->order->id.' receipt:');

        foreach ($this->order->items as $item) {
            $mail->line($item->quantity.' × '.$item->food_name.' — GHS '.number_format((float) $item->price * $item->quantity, 2));
        }

        return $mail
            ->line('Total: GHS '.number_format((float) $this->order->total, 2))
            ->line('Pickup: '.$this->order->pickup_time->format('M j, Y g:i A'))
            ->action('View your order', route('orders.show', $this->order))
            ->line('Thank you for ordering with ATU Eats.');
    }

    /** @return array<string, int|string> */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'message' => $this->message,
        ];
    }
}
