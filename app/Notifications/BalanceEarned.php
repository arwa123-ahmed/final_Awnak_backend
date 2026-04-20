<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;

class BalanceEarned extends Notification
{
    public function __construct(public int $amount, public string $serviceName) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array {
        return [
            'type'    => 'balance_earned',
            'title'   => 'Minutes Earned! 🎉',
            'message' => "You earned {$this->amount} minutes for: {$this->serviceName}",
            'amount'  => $this->amount,
        ];
    }
}