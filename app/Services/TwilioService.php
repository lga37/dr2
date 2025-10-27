<?php

// app/Services/TwilioTemplateService.php
namespace App\Services;

use App\Models\Message;
use Twilio\Rest\Client;
use App\Models\WaMessage;
use Illuminate\Support\Facades\Log;

class TwilioService
{

    private Client $tw;

    public function __construct(?Client $tw = null)
    {
        // usa config(), não env() direto
        $this->tw = new Client(
            env('TWILIO_SID'),
            env('TWILIO_AUTH_TOKEN'),
        );

        #Log::info('tw1', [$this->tw]);
    }



    /** via Content API (template aprovado) */
    public function sendTemplate(string $toWhatsApp, string $contentSid, array $vars = []): array
    {
        $msg = $this->tw->messages->create($toWhatsApp, [
            'from' => env('TWILIO_WHATSAPP_FROM'),
            'contentSid' => $contentSid,                  // HX...
            'contentVariables' => json_encode($vars, JSON_UNESCAPED_UNICODE),
        ]);
        return ['sid' => $msg->sid ?? null, 'status' => $msg->status ?? null];
    }

    public function sendText(string $toWhatsApp, string $body): array
    {


        $msg = $this->tw->messages->create($toWhatsApp, [
            'from' => env('TWILIO_WHATSAPP_FROM'),
            'body' => $body,
        ]);


        return ['sid' => $msg->sid ?? null, 'status' => $msg->status ?? null];
    }



    public function getOrStart(string $waId, string $contact, ?string $displayName = null): Message
    {
        $thread = Message::firstOrCreate(
            ['wa_id' => $waId],
            ['contact' => $contact, 'display_name' => $displayName,]
        );
        #$thread->update(['last_message_at' => now()]);
        return $thread;
    }

    public function saveInbound(Message $thread, array $data): WaMessage
    {
        return WaMessage::create([
            'message_id' => $thread->id,
            'direction'  => 'inbound',
            'from'       => $data['from'],
            'to'         => $data['to'],
            'body'       => $data['body'] ?? null,
            #'raw'        => $data['raw'] ?? null,
        ]);
    }

    public function saveOutbound(Message $thread, array $data): WaMessage
    {
        return WaMessage::create([
            'message_id'  => $thread->id,
            'direction'   => 'outbound',
            'from'        => $data['from'],
            'to'          => $data['to'],
            'body'        => $data['body'] ?? null,
            #'message_sid' => $data['sid'] ?? null,
            #'status'      => $data['status'] ?? null,
        ]);
    }
}
