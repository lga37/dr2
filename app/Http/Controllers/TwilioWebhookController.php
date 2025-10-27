<?php

// app/Http/Controllers/TwilioWebhookController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenAiService;
use App\Services\TwilioService;
use App\Services\TwilioMessenger;
use Illuminate\Support\Facades\Log;

class TwilioWebhookController extends Controller
{
    public function handle(Request $req, TwilioService $twilio, OpenAiService $ai)
    {

        #Log::info('controller', $req->all());

        Log::info('inicio-processamento', $req->all());

        // 0) Normaliza
        $waId = (string) $req->input('WaId');
        $from = (string) $req->input('From');
        $to   = (string) $req->input('To');
        $body = trim((string) $req->input('Body', ''));
        $name = $req->input('ProfileName');


        // 1) Thread (message_id)
        $prof = '+5521999981079';

        $thread = $twilio->getOrStart($waId, $from, $name);

        // 2) Salva inbound
        $twilio->saveInbound($thread, [
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'raw' => $req->all()
        ]);

        // 3) Roteia ação
        $reply = 'Hello World - fc num1 num2, tpl, ai ou fallback ?';

        if ($body !== '') {
            if (preg_match('/tpl/', $body, $m)) {
                // /tpl HXxxxxxxxx nome
                $tpl=['HX85491672b1094bd57383a8728d818585','HX41bc0870c839897aa975f985fa69597f','HXdfc84fce4ec62c35cf7111977ebf46ae'];
                         
                $contentSid = $tpl[array_rand($tpl)];
                # [$all, $contentSid, $nome] = $m + [null, null, 'amigo'];
                $res = app(TwilioService::class)->sendTemplate($from, $contentSid, ['1' => 'param 1']);
                $reply = "Template enviado (sid: {$res['sid']})";

            } elseif (preg_match('/^\/ai(.*)/i', $body, $m)) {
                // /ai texte alguma coisa
                $json = $ai->jsonReply($m[1], [
                    'name' => 'reply',
                    'schema' => [
                        'type' => 'object',
                        'properties' => ['reply' => ['type' => 'string']],
                        'required' => ['reply']
                    ]
                ]);
                $reply = is_array($json) ? ($json['reply'] ?? 'ok') : (string) $json;
            } elseif (preg_match('/^\/fc\s+(\d+)\s+(\d+)/i', $body, $m)) {
                $reply = (string) $ai->withFunction("Some $m[1] e $m[2]");
            } else {
                $reply = "você disse: {$body}";
            }
        }

        // 4) Envia e salva outbound
        $sent = $twilio->sendText($from, $reply);


        $twilio->saveOutbound($thread, [
            'from' => env('TWILIO_WHATSAPP_FROM'),
            'to' => $from,
            'body' => $reply,
            #'sid' => $sent['sid'] ?? null,
            #'status' => $sent['status'] ?? null,
        ]);

        return response('OK', 200);
    }
    public function handle2(Request $req, TwilioService $twilio, OpenAiService $ai)
    {

        Log::info('controller', $req->all());

        // 0) Normaliza
        $waId = (string) $req->input('WaId');
        $from = (string) $req->input('From');
        $to   = (string) $req->input('To');
        $body = trim((string) $req->input('Body', ''));
        $name = $req->input('ProfileName');

        Log::info('twilio-inbound', $req->all());

        // 1) Thread (message_id)
        $to = '+5521999981079';

        $thread = $twilio->getOrStart($waId, $from, $name);

        // 2) Salva inbound
        $twilio->saveInbound($thread, [
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'raw' => $req->all()
        ]);

        // 3) Roteia ação
        $reply = 'Hello World - fc num1 num2, tpl, ai ou fallback ?';

        if ($body !== '') {
            if (preg_match('/tpl/', $body, $m)) {
                // /tpl HXxxxxxxxx nome
                $tpl=['HX85491672b1094bd57383a8728d818585','HX41bc0870c839897aa975f985fa69597f','HXdfc84fce4ec62c35cf7111977ebf46ae'];
                         
                $contentSid = $tpl[array_rand($tpl)];
                # [$all, $contentSid, $nome] = $m + [null, null, 'amigo'];
                $res = app(TwilioService::class)->sendTemplate($from, $contentSid, ['1' => 'param 1']);
                $reply = "Template enviado (sid: {$res['sid']})";

            } elseif (preg_match('/^\/ai(.*)/i', $body, $m)) {
                // /ai texte alguma coisa
                $json = $ai->jsonReply($m[1], [
                    'name' => 'reply',
                    'schema' => [
                        'type' => 'object',
                        'properties' => ['reply' => ['type' => 'string']],
                        'required' => ['reply']
                    ]
                ]);
                $reply = is_array($json) ? ($json['reply'] ?? 'ok') : (string) $json;
            } elseif (preg_match('/^\/fc\s+(\d+)\s+(\d+)/i', $body, $m)) {
                $reply = (string) $ai->withFunction("Some $m[1] e $m[2]");
            } else {
                $reply = "você disse: {$body}";
            }
        }

        // 4) Envia e salva outbound
        $sent = $twilio->sendText($from, $reply);




        $twilio->saveOutbound($thread, [
            'from' => env('TWILIO_WHATSAPP_FROM'),
            'to' => $from,
            'body' => $reply,
            #'sid' => $sent['sid'] ?? null,
            #'status' => $sent['status'] ?? null,
        ]);

        return response('OK', 200);
    }
}
