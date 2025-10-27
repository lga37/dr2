<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;
use Symfony\Component\HttpFoundation\Response;

class TwilioSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        $validator = new RequestValidator(env('TWILIO_AUTH_TOKEN'));
        if (!$validator->validate($request->header('X-Twilio-Signature', ''), rtrim(env('NGROK_URL'), '/') . $request->getRequestUri(), $request->all())) {
            return response('Invalid signature - ', 403);
        }


        // habilita/desabilita via .env
        // if (!filter_var(env('TWILIO_VERIFY_SIGNATURE', true), FILTER_VALIDATE_BOOLEAN)) {
        //     return $next($request);
        // }

        // $sig = $request->header('X-Twilio-Signature', '');
        // if ($sig === '') {
        //     return response('Invalid signature resp', 403);
        // }

        // $token = env('TWILIO_AUTH_TOKEN');
        // if (!$token) {
        //     // evita 500 quando token não está setado
        //     return response('Missing TWILIO_AUTH_TOKEN', 500);
        // }

        // $validator = new RequestValidator($token);

        // // Opção A (preferida): APP_URL idêntica à do Console
        // $url = rtrim(env('NGROK_URL', ''), '/') . $request->getRequestUri();

        // // Fallback: se APP_URL não estiver setada, usa a URL percebida
        // if ($url === $request->getRequestUri()) {
        //     // requer TrustProxies configurado
        //     $url = $request->fullUrl();
        // }

        // if (!$validator->validate($sig, $url, $request->all())) {
        //     return response('Invalid signature res', 403);
        // }

        return $next($request);
    }
}
