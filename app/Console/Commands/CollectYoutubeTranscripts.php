<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CollectYoutubeTranscripts extends Command
{
    protected $signature = 'bench:transcripts
        {--max=20 : máximo de transcrições}
        {--lang= : idioma preferido, ex: pt, en}
        {--type=auto : auto|manual}
        {--only-available=1 : usa fallback para primeira transcrição disponível}
        {--retry-errors=0 : tentar novamente vídeos que já deram erro}';

    protected $description = 'Coleta transcrições de vídeos do YouTube via SearchAPI';

    protected string $videosFile = 'bench/polarization_videos.json';
    protected string $transcriptsDir = 'polarization/transcripts';
    protected string $errorsDir = 'polarization/transcripts/errors';

    public function handle(): int
    {
        if (!Storage::exists($this->videosFile)) {
            $this->error("Arquivo de vídeos não encontrado: storage/app/{$this->videosFile}");
            return self::FAILURE;
        }

        $videos = json_decode(Storage::get($this->videosFile), true) ?? [];

        if (!$videos) {
            $this->warn('Nenhum vídeo encontrado.');
            return self::SUCCESS;
        }

        Storage::makeDirectory($this->transcriptsDir);
        Storage::makeDirectory($this->errorsDir);

        shuffle($videos);

      
        

        $retryErrors = (bool) $this->option('retry-errors');

        $videos = array_values(array_filter($videos, function ($video) use ($retryErrors) {
            $videoId = $video['videoId'] ?? null;

            if (!$videoId) {
                return false;
            }

            if ($this->transcriptExists($videoId)) {
                return false;
            }

            if (!$retryErrors && $this->transcriptErrorExists($videoId)) {
                return false;
            }

            return true;
        }));

        shuffle($videos);

        $max = (int) $this->option('max');
        if ($max > 0) {
            $videos = array_slice($videos, 0, $max);
        }





        $this->info('Total selecionado: ' . count($videos));

        foreach ($videos as $i => $video) {
            $videoId = $video['videoId'] ?? null;

            if (!$videoId) {
                continue;
            }

            if ($this->transcriptExists($videoId)) {
                $this->line(($i + 1) . '/' . count($videos) . " já existe: {$videoId}");
                continue;
            }

            if (!$this->option('retry-errors') && $this->transcriptErrorExists($videoId)) {
                $this->warn(($i + 1) . '/' . count($videos) . " já deu erro antes: {$videoId}");
                continue;
            }

            $this->line(($i + 1) . '/' . count($videos) . " coletando: {$videoId}");

            try {
                $json = $this->fetchTranscript($videoId);

                $plainText = $this->transcriptToPlainText($json);
                $timedText = $this->transcriptToTimedText($json);

                $payload = [
                    'videoId' => $videoId,
                    'videoTitle' => $video['videoTitle'] ?? null,
                    'channelTitle' => $video['channelTitle'] ?? null,
                    'plain_text' => $plainText,
                    'timed_text' => $timedText,
                    'raw' => $json,
                    'created_at' => now()->toDateTimeString(),
                ];

                Storage::put(
                    $this->transcriptPath($videoId),
                    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                $this->info("OK: {$videoId}");
            } catch (\Throwable $e) {
                Log::error('Erro transcript SearchAPI', [
                    'videoId' => $videoId,
                    'error' => $e->getMessage(),
                ]);

                Storage::put(
                    $this->transcriptErrorPath($videoId),
                    json_encode([
                        'videoId' => $videoId,
                        'error' => $e->getMessage(),
                        'created_at' => now()->toDateTimeString(),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                $this->warn("Erro: {$videoId}");
            }

            usleep(500000);
        }

        $this->info('Finalizado.');
        return self::SUCCESS;
    }

    protected function transcriptPath(string $videoId): string
    {
        return $this->transcriptsDir . '/' . $videoId . '.json';
    }

    protected function transcriptErrorPath(string $videoId): string
    {
        return $this->errorsDir . '/' . $videoId . '.json';
    }

    protected function transcriptExists(string $videoId): bool
    {
        return Storage::exists($this->transcriptPath($videoId));
    }

    protected function transcriptErrorExists(string $videoId): bool
    {
        return Storage::exists($this->transcriptErrorPath($videoId));
    }

    protected function fetchTranscript(string $videoId): array
    {
        $apiKey = env('SEARCHAPI_TRANSCRIPTS_YOUTUBE_API');

        if (!$apiKey) {
            throw new \RuntimeException('SEARCHAPI_TRANSCRIPTS_YOUTUBE_API vazio no .env');
        }

        $params = [
            'engine' => 'youtube_transcripts',
            'video_id' => $videoId,
            'api_key' => $apiKey,
            'only_available' => $this->option('only-available') ? 'true' : 'false',
            'transcript_type' => $this->option('type') ?: 'auto',
        ];

        if ($this->option('lang')) {
            $params['lang'] = $this->option('lang');
        }

        $response = Http::connectTimeout(20)
            ->timeout(240)
            ->retry(3, 5000)
            ->get('https://www.searchapi.io/api/v1/search', $params);

        if (!$response->successful()) {
            throw new \RuntimeException($response->body());
        }

        $json = $response->json();

        if (!empty($json['error'])) {
            throw new \RuntimeException($json['error']);
        }

        return $json;
    }

    protected function transcriptToPlainText(array $json, int $maxChars = 20000): string
    {
        $items = $json['transcripts'] ?? $json['raw']['transcripts'] ?? [];
        $texts = [];

        foreach ($items as $item) {
            $text = trim($item['text'] ?? '');

            if ($text === '') {
                continue;
            }

            if (preg_match('/^\[(music|applause|laughter)\]$/i', $text)) {
                continue;
            }

            $texts[] = $text;
        }

        $full = trim(preg_replace('/\s+/', ' ', implode(' ', $texts)));

        return mb_substr($full, 0, $maxChars);
    }

    protected function transcriptToTimedText(array $json, int $maxChars = 20000): string
    {
        $items = $json['transcripts'] ?? $json['raw']['transcripts'] ?? [];
        $lines = [];

        foreach ($items as $item) {
            $text = trim($item['text'] ?? '');
            $start = $item['start'] ?? null;

            if ($text === '') {
                continue;
            }

            $time = $start !== null ? gmdate('H:i:s', (int) $start) : '00:00:00';

            $lines[] = "[{$time}] {$text}";
        }

        return mb_substr(trim(implode("\n", $lines)), 0, $maxChars);
    }
}