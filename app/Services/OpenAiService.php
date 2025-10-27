<?php

namespace App\Services;

use OpenAI;
class OpenAiService
{
    private $client;
    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    /** Texto normal */
    public function askText(string $system, string $user, ?string $model = null): string
    {
        $m = $model ?: config('openai.model_text');
        $res = $this->client->chat()->create([
            'model' => $m,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
        ]);
        return $res->choices[0]->message->content ?? '';
    }

    public function jsonReply(string $userPrompt, ?array $schema = null): array|string
    {
        $payload = [
            'model' => env('OPENAI_MODEL_JSON', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'Responda SOMENTE em JSON válido.'],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'response_format' => $schema
                ? ['type' => 'json_schema', 'json_schema' => $schema]
                : ['type' => 'json_object'],
        ];
        try {
            $r = $this->client->chat()->create($payload);
            return json_decode($r->choices[0]->message->content ?? '{}', true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            // fallback: texto
            $r = $this->client->chat()->create([
                'model' => env('OPENAI_MODEL_TEXT', 'gpt-4o-mini'),
                'messages' => [['role' => 'user', 'content' => $userPrompt]]
            ]);
            return $r->choices[0]->message->content ?? '';
        }
    }

    public function withFunction(string $user)
    {
        $r = $this->client->chat()->create([
            'model' => env('OPENAI_MODEL_TEXT', 'gpt-4o-mini'),
            'messages' => [['role' => 'user', 'content' => $user]],
            'tools' => [[
                'type' => 'function',
                'function' => [
                    'name' => 'somar',
                    'description' => 'Soma dois inteiros',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => ['a' => ['type' => 'integer'], 'b' => ['type' => 'integer']],
                        'required' => ['a', 'b']
                    ],
                ]
            ]],
            'tool_choice' => 'auto'
        ]);

        $m = $r->choices[0]->message;
        if (!$m->toolCalls) return $m->content ?? '';

        $call = $m->toolCalls[0];
        $args = json_decode($call->function->arguments, true);
        $sum  = (int)($args['a'] ?? 0) + (int)($args['b'] ?? 0);

        $r2 = $this->client->chat()->create([
            'model' => env('OPENAI_MODEL_TEXT', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'user', 'content' => $user],
                $m->toArray(),
                ['role' => 'tool', 'tool_call_id' => $call->id, 'name' => 'somar', 'content' => (string)$sum],
            ],
        ]);
        return $r2->choices[0]->message->content ?? (string)$sum;
    }




    /**
     * JSON garantido (se o modelo suportar) — com fallback para parsing/ texto
     * $schema opcional (nome + json_schema) para validar
     */
    public function askJSON(string $system, string $user, ?array $schema = null, ?string $model = null): array|string
    {
        $m = $model ?: config('openai.model_json');

        $payload = [
            'model' => $m,
            'messages' => [
                ['role' => 'system', 'content' => $system . "\nResponda SOMENTE em JSON válido."],
                ['role' => 'user',   'content' => $user],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        // se quiser schema explícito:
        if ($schema) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => $schema,   // ['name'=>'resposta','schema'=>[...]]
            ];
        }

        try {
            $res = $this->client->chat()->create($payload);
            $txt = $res->choices[0]->message->content ?? '';
            return json_decode($txt, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            // fallback: tenta extrair JSON; se não der, devolve texto cru
            try {
                if (preg_match('/\{.*\}/s', $txt ?? '', $m)) {
                    return json_decode($m[0], true, 512, JSON_THROW_ON_ERROR);
                }
            } catch (\Throwable $e2) {
            }
            return $txt ?? '';
        }
    }

    /** Exemplo de Function Calling (tool) bem simples */
    public function askWithTool(string $user): string
    {
        $res = $this->client->chat()->create([
            'model' => config('openai.model_text'),
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um assistente que calcula soma.'],
                ['role' => 'user', 'content' => $user],
            ],
            'tools' => [[
                'type' => 'function',
                'function' => [
                    'name' => 'somar',
                    'description' => 'Soma dois números inteiros',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'a' => ['type' => 'integer'],
                            'b' => ['type' => 'integer'],
                        ],
                        'required' => ['a', 'b'],
                    ],
                ],
            ]],
            'tool_choice' => 'auto',
        ]);

        $msg = $res->choices[0]->message;
        if (!empty($msg->toolCalls)) {
            $call = $msg->toolCalls[0];
            if ($call->function->name === 'somar') {
                $args = json_decode($call->function->arguments, true);
                $sum  = (int)$args['a'] + (int)$args['b'];

                // envia a “execução” da ferramenta de volta ao modelo
                $res2 = $this->client->chat()->create([
                    'model' => config('openai.model_text'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Você é um assistente que calcula soma.'],
                        ['role' => 'user', 'content' => $user],
                        $msg->toArray(),
                        [
                            'role' => 'tool',
                            'tool_call_id' => $call->id,
                            'name' => 'somar',
                            'content' => (string)$sum,
                        ],
                    ],
                ]);
                return $res2->choices[0]->message->content ?? (string)$sum;
            }
        }
        return $msg->content ?? '';
    }
}
