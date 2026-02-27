<?php

namespace App\Notifications\Links;

use App\Models\Link;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a new short link is created.
 * Only dispatched when the owning user has opted into link creation notifications.
 * Delivered via mail and stored in the database notification inbox.
 */
class LinkCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Link $link)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your new short link is ready')
            ->greeting("Hi {$notifiable->name}!")
            ->line('Your short link has been created successfully.')
            ->line('**Short URL:** ' . ($this->link->short_url ?? url('/' . $this->link->short_code)))
            ->line('**Destination:** ' . $this->link->destination_url)
            ->action('View Link Analytics', $this->linkUrl())
            ->salutation('The ' . config('app.name') . ' Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'link_created',
            'link_ulid'       => $this->link->ulid,
            'short_code'      => $this->link->short_code,
            'destination_url' => $this->link->destination_url,
            'link_url'        => $this->linkUrl(),
        ];
    }

    private function linkUrl(): string
    {
        try {
            return route('app.links.show', $this->link->ulid);
        } catch (\Throwable) {
            return url('/app/links/' . $this->link->ulid);
        }
    }
}
