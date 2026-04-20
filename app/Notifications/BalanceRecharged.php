<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;

class BalanceRecharged extends Notification
{
    public function __construct(public int $amount) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array {
        return [
            'type'    => 'balance_recharged',
            'title'   => 'Balance Recharged ✅',
            'message' => "{$this->amount} minutes added to your account by admin.",
            'amount'  => $this->amount,
        ];
    }
}