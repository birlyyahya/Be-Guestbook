<?php

namespace App\Listeners;

use App\Events\GuestCreated;
use App\Models\Events;
use App\Services\WhatsAppService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendGuestWhatsAppNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create the event listener.
     */
    protected $whatsapp;
    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Handle the event.
     */
    public function handle(GuestCreated $event): void
    {

        WhatsAppService::sendMessage(
            $event->guest, Events::find($event->guest->event_id)
        );
    }
}
