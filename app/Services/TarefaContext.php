<?php

namespace App\Support;

use App\Models\Tarefa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TarefaContext
{
    /** cache por tipo, para evitar re-hit ao DB */
    private array $cache = [];

    private function key(string $tipo): string
    {
        return "tarefa:{$tipo}";
    }

    /** Pega (ou cria) a tarefa aberta do usuário para o tipo */
    public function current(string $tipo): Tarefa
    {
        $k = $this->key($tipo);

        // cache
        if (isset($this->cache[$k])) return $this->cache[$k];

        // sessão
        if ($id = Session::get($k)) {
            $t = Tarefa::whereKey($id)
                ->where('tipo', $tipo)
                ->where('status', 0)
                ->first();
            if ($t) return $this->cache[$k] = $t;

            Session::forget($k); // sessão aponta para tarefa fechada/inválida
        }

        // última aberta no banco
        $t = Tarefa::where('user_id', Auth::id())
            ->where('tipo', $tipo)
            ->where('status', 0)
            ->latest('id')
            ->first();

        // se não há, cria
        if (!$t) {
            $t = Tarefa::create([
                'user_id' => Auth::id(),
                'tipo'    => $tipo,
                'status'  => 0,
            ]);
        }

        Session::put($k, $t->id);
        return $this->cache[$k] = $t;
    }

    /** Fecha a tarefa aberta do tipo e remove da sessão */
    public function close(string $tipo, array $extra = []): Tarefa
    {
        $t = $this->current($tipo);
        $t->status = 1;
        if (array_key_exists('feedback', $extra)) $t->feedback = $extra['feedback'];
        if (array_key_exists('finished_at', $extra)) $t->finished_at = $extra['finished_at'];
        else $t->finished_at = now();
        $t->save();

        $k = $this->key($tipo);
        Session::forget($k);
        unset($this->cache[$k]);

        return $t;
    }

    /** Se quiser apenas limpar o id da sessão, sem fechar */
    public function forget(string $tipo): void
    {
        $k = $this->key($tipo);
        Session::forget($k);
        unset($this->cache[$k]);
    }
}
