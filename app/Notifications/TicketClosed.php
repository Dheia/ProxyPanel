<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TicketClosed extends Notification implements ShouldQueue
{
    use Queueable;

    private int $ticketId;

    private string $title;

    private string $url;

    private string $reason;

    private bool $is_user;

    public function __construct(int $ticketId, string $title, string $url, string $reason, bool $is_user = false)
    {
        $this->ticketId = $ticketId;
        $this->title = $title;
        $this->url = $url;
        $this->reason = $reason;
        $this->is_user = $is_user;
    }

    public function via($notifiable)
    {
        return $this->is_user ? ['mail'] : sysConfig('ticket_closed_notification');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('notification.close_ticket', ['id' => $this->ticketId, 'title' => $this->title]))
            ->line($this->reason)
            ->action(trans('notification.view_ticket'), $this->url)
            ->line(__('If your issue is not resolved, please create another ticket.'));
    }

    public function toCustom($notifiable): array
    {
        return [
            'title' => trans('notification.close_ticket', ['id' => $this->ticketId, 'title' => $this->title]),
            'content' => $this->reason,
        ];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->reason);
    }

    public function toBark($notifiable): array
    {
        return [
            'title' => trans('notification.close_ticket', ['id' => $this->ticketId, 'title' => $this->title]),
            'content' => $this->reason,
            'group' => '工单',
            'icon' => asset('assets/images/notification/ticket.png'),
            'url' => $this->url,
        ];
    }
}
