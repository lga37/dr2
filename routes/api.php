<?php

use Twilio\Rest\Client;
use App\Models\WaMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Twilio\Security\RequestValidator;
use App\Http\Controllers\TwilioWebhookController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');



Route::post('/webhooks/twilio/whatsapp',
    [TwilioWebhookController::class, 'handle']
)->name('twilio');
#->middleware('twilio.signature');  // ✅ validação aqui

Route::post('/webhooks/twilio/status', function (Request $r) {
    #Log::info('twilio-status', $r->all());
    return response('OK', 200);
});


Route::get('tw', function () {

    echo rtrim(env('NGROK_URL'), '/') . route('twilio');
   
    return view('tw.blade.php'); #pagina com todas as msgs 
})->name('tw');


Route::get('tw/test', function (Request $req) {
    $to = 'whatsapp:+5521995177140';
    $tw = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    $msg = $tw->messages->create($to, [
        'from' => env('TWILIO_WHATSAPP_FROM'),
        'body' => 'dddddd eeeee ffff',
    ]);


    return [
        'sid' => $msg->sid,
        'status' => $msg->status,
    ];
});

