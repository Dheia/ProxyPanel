<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private string $amount;

    private string $sn;

    public function __construct(string $sn, string $amount)
    {
        $this->amount = $amount;
        $this->sn = $sn;
    }

    public function via($notifiable)
    {
        return sysConfig('payment_received_notification');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Payment Received'))
            ->line(__('Payment for #:sn has been received! Total amount: :amount.', ['sn' => $this->sn, 'amount' => $this->amount]))
            ->action(__('Invoice Detail'), route('invoiceInfo', $this->sn));
    }

    public function toDataBase($notifiable): array
    {
        return [
            'sn' => $this->sn,
            'amount' => $this->amount,
        ];
    }

    // todo: 需要重新审视发送对象
    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->to($notifiable->telegram_user_id)
            ->token(sysConfig('telegram_token'))
            ->content('💰'.__('Payment for #:sn has been received! Total amount: :amount.', ['sn' => $this->sn, 'amount' => $this->amount]));
    }
}
