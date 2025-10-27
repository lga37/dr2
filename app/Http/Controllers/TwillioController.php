<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

    use Twilio\Rest\Client;


class TwillioController extends Controller
{
    

    public function get(){

        $sid    = "ACcd60c49c070107a05a31886f96bf8a9a";
        $token  = "[AuthToken]";
        $twilio = new Client($sid, $token);

        $message = $twilio->messages->create("whatsapp:+5511983958277", // to
            [
            "from" => "whatsapp:+14155238886",
            "contentSid" => "HXb5b62575e6e4ff6129ad7c8efe1f983e",
            "contentVariables" => "{"1":"12/1","2":"3pm"}",
            "body" => "Your Message"
            ]
        );

        print($message->sid);

    }


// virtual phone
// "from" => "YourTwilioSender"
// $twilio->messages->create("+18777804236", // to


    

// 201 - CREATED - The request was successful. We created a new resource and the response body contains the representation.
// {
//   "account_sid": "ACcd60c49c070107a05a31886f96bf8a9a",
//   "api_version": "2010-04-01",
//   "body": "Your appointment is coming up on 12/1 at 3pm",
//   "date_created": "Sun, 05 Oct 2025 23:16:24 +0000",
//   "date_sent": null,
//   "date_updated": "Sun, 05 Oct 2025 23:16:24 +0000",
//   "direction": "outbound-api",
//   "error_code": null,
//   "error_message": null,
//   "from": "whatsapp:+14155238886",
//   "messaging_service_sid": null,
//   "num_media": "0",
//   "num_segments": "1",
//   "price": null,
//   "price_unit": null,
//   "sid": "MM917266e9be812d8cb8d67b914ce950ac",
//   "status": "queued",
//   "subresource_uris": {
//     "media": "/2010-04-01/Accounts/ACcd60c49c070107a05a31886f96bf8a9a/Messages/MM917266e9be812d8cb8d67b914ce950ac/Media.json"
//   },
//   "to": "whatsapp:+5511983958277",
//   "uri": "/2010-04-01/Accounts/ACcd60c49c070107a05a31886f96bf8a9a/Messages/MM917266e9be812d8cb8d67b914ce950ac.json"
// }




    public function handle(Request $req)
    {
        // 1) Validar assinatura (garante que veio da Twilio)
        $signature = $req->header('X-Twilio-Signature', '');
        $validator = new RequestValidator(config('services.twilio.token') ?? env('TWILIO_AUTH_TOKEN'));
        $url = rtrim(env('APP_URL'), '/') . $req->getRequestUri();

        if (!$validator->validate($signature, $url, $req->all())) {
            return response('Invalid signature', 403);
        }

        // 2) Normalizar campos principais
        $from     = $req->input('From');            // ex: whatsapp:+55...
        $to       = $req->input('To');              // seu número Twilio
        $body     = trim($req->input('Body', ''));
        $numMedia = (int) $req->input('NumMedia', 0);

        // 3) Salvar inbound (payload cru + dados)
        $media = [];
        for ($i = 0; $i < $numMedia; $i++) {
            $media[] = [
                'url'  => $req->input("MediaUrl{$i}"),
                'type' => $req->input("MediaContentType{$i}"),
            ];
        }

        $in = WaMessage::create([
            'direction'   => 'inbound',
            'from'        => $from,
            'to'          => $to,
            'body'        => $body ?: null,
            'media'       => $media ?: null,
            'message_sid' => $req->input('SmsMessageSid') ?? $req->input('MessageSid'),
            'status'      => 'received',
            'raw'         => $req->all(),
        ]);

        // (Opcional) baixar a mídia pro teu storage local/S3
        // if (!empty($media)) { ... fazer GET autenticado e gravar em storage ... }

        // 4) Lógica simples (FSM “Hello World”)
        $reply = match (true) {
            $body === '' && $numMedia > 0        => 'Recebi sua mídia, valeu!',
            preg_match('/^menu/i', $body)        => "Menu:\n1) Ajuda\n2) Docs\n3) Humano",
            preg_match('/^1$/', $body)           => "🤖 Ajuda: mande 'docs' para materiais.",
            preg_match('/^2$/', $body)           => "📚 Docs: https://seu-site/docs",
            preg_match('/^3$/', $body)           => "👤 Encaminhando para humano…",
            default                               => "Oi, xará! Manda *menu* pra ver opções. Envie foto/PDF que eu reconheço mídia."
        };

        // 5) Responder via Twilio
        $twilio = new Client(config('services.twilio.sid') ?? env('TWILIO_SID'),
                             config('services.twilio.token') ?? env('TWILIO_AUTH_TOKEN'));

        $msgData = [
            'from' => config('services.twilio.from') ?? env('TWILIO_WHATSAPP_FROM'),
            'body' => $reply,
        ];

        // Ecoa a primeira mídia recebida (demonstra envio de mídia)
        if ($numMedia > 0) {
            $msgData['mediaUrl'] = [$media[0]['url']];
        }

        $out = $twilio->messages->create($from, $msgData);

        // 6) Registrar outbound
        WaMessage::create([
            'direction'   => 'outbound',
            'from'        => $msgData['from'],
            'to'          => $from,
            'body'        => $msgData['body'] ?? null,
            'media'       => $msgData['mediaUrl'] ?? null,
            'message_sid' => $out->sid ?? null,
            'status'      => $out->status ?? null,
            'raw'         => $out->toArray() ?? null,
        ]);

        return response('OK', 200);
    }



}
