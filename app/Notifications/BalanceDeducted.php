<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;

class BalanceDeducted extends Notification
{
    public function __construct(public int $amount, public string $serviceName) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array {
        return [
            'type'    => 'balance_deducted',
            'title'   => 'Balance Deducted 💸',
            'message' => "{$this->amount} minutes deducted for: {$this->serviceName}",
            'amount'  => $this->amount,
        ];
    }
}