<?php

namespace App\Notifications;

use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletTopUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public WalletTransaction $transaction)
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ATU Eats wallet top up confirmed')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your wallet has been credited with GHS '.number_format((float) $this->transaction->amount, 2).'.')
            ->line('Reference: '.$this->transaction->reference)
            ->action('View your wallet', route('wallet.show'))
            ->line('Thank you for using ATU Eats.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'message' => 'Your wallet top up is complete.',
        ];
    }
}
