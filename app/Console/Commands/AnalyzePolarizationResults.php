<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AnalyzePolarizationResults extends Command
{
    protected $signature = 'bench:polarization-analyze
        {--input=bench/polarization_results.jsonl : arquivo JSONL de resultados}
        {--output=bench/polarization_summary.json : arquivo de saída}
        {--csv=bench/polarization_summary.csv : CSV resumido}';

    protected $description = 'Analisa métricas agregadas do benchmark de polarização';

    public function handle(): int
    {
        $input = $this->option('input');

        if (!Storage::exists($input)) {
            $this->error("Arquivo não encontrado: storage/app/{$input}");
            return self::FAILURE;
        }

        $rows = $this->readJsonl($input);

        if (!$rows) {
            $this->warn('Nenhum registro válido encontrado.');
            return self::SUCCESS;
        }

        $summary = [
            'geral' => $this->summarize($rows),

            #'por_prompt_level' => $this->groupAndSummarize($rows, 'prompt_level'),
            #'por_prompt_level_e_dimensao' => $this->groupAndSummarizeMulti($rows, ['prompt_level', 'dimension']),

            'por_labs' => $this->groupAndSummarize($rows, 'labs_key'),
            'por_labs_e_dimensao' => $this->groupAndSummarizeMulti($rows, ['labs_key', 'dimension']),



            'por_dimensao' => $this->groupAndSummarize($rows, 'dimension'),
            'por_canal' => $this->groupAndSummarize($rows, 'seed_name'),
            'por_label_esperado' => $this->groupAndSummarize($rows, 'expected_label'),
            'por_label_predito' => $this->groupAndSummarize($rows, 'predicted_label'),
        ];

        Storage::put(
            $this->option('output'),
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->writeSummaryCsv($summary);

        $this->info('Resumo JSON salvo em: storage/app/' . $this->option('output'));
        $this->info('Resumo CSV salvo em: storage/app/' . $this->option('csv'));

        return self::SUCCESS;
    }

    protected function readJsonl(string $path): array
    {
        $lines = explode("\n", trim(Storage::get($path)));
        $rows = [];

        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }

            $row = json_decode($line, true);

            if (!$row) {
                continue;
            }

            $rows[] = $this->normalizeRow($row);
        }

        return $rows;
    }

    protected function normalizeRow(array $row): array
    {
        $expected = $row['expected_label'] ?? null;
        $predicted = $row['predicted_label'] ?? null;

        $row['hit'] = isset($row['hit'])
            ? (int) $row['hit']
            : (($expected && $predicted) ? (int) ($expected === $predicted) : null);

        $row['intra_channel_deviation'] = ($expected && $predicted && $expected !== $predicted)
            ? 1
            : 0;

        $row['confidence'] = isset($row['confidence']) ? (float) $row['confidence'] : null;
        #$row['polarization_level'] = isset($row['polarization_level']) ? (float) $row['polarization_level'] : null;
        $row['is_ambiguous'] = isset($row['is_ambiguous']) ? (int) $row['is_ambiguous'] : null;
        $row['sentiment_intensity'] = isset($row['sentiment_intensity']) ? (float) $row['sentiment_intensity'] : null;

        $row['viewCount'] = isset($row['viewCount']) ? (int) $row['viewCount'] : null;
        $row['likeCount'] = isset($row['likeCount']) ? (int) $row['likeCount'] : null;
        $row['commentCount'] = isset($row['commentCount']) ? (int) $row['commentCount'] : null;
        $row['duration'] = isset($row['duration']) ? (int) $row['duration'] : null;

        return $row;
    }

   


    protected function summarize(array $rows): array
{
    $n = count($rows);

    $aligned = array_values(array_filter($rows, fn ($r) => (int)($r['hit'] ?? 0) === 1));
    $deviated = array_values(array_filter($rows, fn ($r) => (int)($r['hit'] ?? 0) === 0));

    return [
        'n' => $n,

        'hits' => $this->sum($rows, 'hit'),
        'hit_rate' => $this->rate($rows, 'hit'),

        'channel_alignment_rate' => $this->rate($rows, 'hit'),

        'intra_channel_deviation_count' => count($deviated),
        'intra_channel_deviation_rate' => $n ? round(count($deviated) / $n, 4) : null,


############################### added
'confidence_when_hit' => $this->avgWhere($rows, 'confidence', 'hit', 1),
'confidence_when_deviation' => $this->avgWhere($rows, 'confidence', 'intra_channel_deviation', 1),

#'polarization_when_hit' => $this->avgWhere($rows, 'polarization_level', 'hit', 1),
#'polarization_when_deviation' => $this->avgWhere($rows, 'polarization_level', 'intra_channel_deviation', 1),

'ambiguous_when_hit' => $this->rateWhere($rows, 'is_ambiguous', 'hit', 1),
'ambiguous_when_deviation' => $this->rateWhere($rows, 'is_ambiguous', 'intra_channel_deviation', 1),

'deviation_confident_rate' => $this->confidentDeviationRate($rows),
############################### added




############################### added 2
'sentiment_negative_rate' => $this->sentimentRate($rows, 'negativo'),
'sentiment_positive_rate' => $this->sentimentRate($rows, 'positivo'),
'sentiment_neutral_rate'  => $this->sentimentRate($rows, 'neutro'),

'sentiment_intensity_when_hit' => $this->avgWhere($rows, 'sentiment_intensity', 'hit', 1),
'sentiment_intensity_when_deviation' => $this->avgWhere($rows, 'sentiment_intensity', 'intra_channel_deviation', 1),

'negative_when_hit' => $this->sentimentRateWhere($rows, 'negativo', 'hit', 1),
'negative_when_deviation' => $this->sentimentRateWhere($rows, 'negativo', 'intra_channel_deviation', 1),

'emotional_deviation_rate' => $this->emotionalDeviationRate($rows),
############################### added 2



        'confidence_avg' => $this->avg($rows, 'confidence'),
        'confidence_aligned_avg' => $this->avg($aligned, 'confidence'),
        'confidence_deviation_avg' => $this->avg($deviated, 'confidence'),

        #'polarization_avg' => $this->avg($rows, 'polarization_level'),
        #'polarization_aligned_avg' => $this->avg($aligned, 'polarization_level'),
        #'polarization_deviation_avg' => $this->avg($deviated, 'polarization_level'),

        'ambiguous_count' => $this->sum($rows, 'is_ambiguous'),
        'ambiguous_rate' => $this->rate($rows, 'is_ambiguous'),
        'ambiguous_aligned_rate' => $this->rate($aligned, 'is_ambiguous'),
        'ambiguous_deviation_rate' => $this->rate($deviated, 'is_ambiguous'),

        'sentiment_intensity_avg' => $this->avg($rows, 'sentiment_intensity'),
        'sentiment_intensity_aligned_avg' => $this->avg($aligned, 'sentiment_intensity'),
        'sentiment_intensity_deviation_avg' => $this->avg($deviated, 'sentiment_intensity'),

        'labels_preditos' => $this->countValues($rows, 'predicted_label'),
        'labels_esperados' => $this->countValues($rows, 'expected_label'),
        'sentiment_valence' => $this->countValues($rows, 'sentiment_valence'),

        'view_avg' => $this->avg($rows, 'viewCount'),
        'like_avg' => $this->avg($rows, 'likeCount'),
        'comment_avg' => $this->avg($rows, 'commentCount'),
        'duration_avg' => $this->avg($rows, 'duration'),
    ];
}


protected function sentimentRate(array $rows, string $valence): ?float
{
    $valid = array_values(array_filter($rows, fn ($r) => !empty($r['sentiment_valence'])));

    if (!$valid) {
        return null;
    }

    $count = 0;

    foreach ($valid as $row) {
        if (($row['sentiment_valence'] ?? '') === $valence) {
            $count++;
        }
    }

    return round($count / count($valid), 4);
}

protected function sentimentRateWhere(array $rows, string $valence, string $whereField, $whereValue): ?float
{
    $filtered = array_values(array_filter($rows, function ($row) use ($whereField, $whereValue) {
        return isset($row[$whereField]) && (string) $row[$whereField] === (string) $whereValue;
    }));

    return $this->sentimentRate($filtered, $valence);
}

protected function emotionalDeviationRate(array $rows): ?float
{
    $valid = array_values(array_filter($rows, function ($row) {
        return isset(
            $row['intra_channel_deviation'],
            $row['sentiment_intensity']
        );
    }));

    if (!$valid) {
        return null;
    }

    $count = 0;

    foreach ($valid as $row) {
        if (
            (int) $row['intra_channel_deviation'] === 1 &&
            (float) $row['sentiment_intensity'] >= 0.6
        ) {
            $count++;
        }
    }

    return round($count / count($valid), 4);
}

protected function avgWhere(array $rows, string $field, string $whereField, $whereValue): ?float
{
    $filtered = array_values(array_filter($rows, function ($row) use ($whereField, $whereValue) {
        return isset($row[$whereField]) && (string) $row[$whereField] === (string) $whereValue;
    }));

    return $this->avg($filtered, $field);
}

protected function rateWhere(array $rows, string $field, string $whereField, $whereValue): ?float
{
    $filtered = array_values(array_filter($rows, function ($row) use ($whereField, $whereValue) {
        return isset($row[$whereField]) && (string) $row[$whereField] === (string) $whereValue;
    }));

    return $this->rate($filtered, $field);
}

protected function confidentDeviationRate(array $rows): ?float
{
    $valid = array_values(array_filter($rows, function ($row) {
        return isset($row['intra_channel_deviation'], $row['confidence'], $row['is_ambiguous']);
    }));

    if (!$valid) {
        return null;
    }

    $count = 0;

    foreach ($valid as $row) {
        if (
            (int) $row['intra_channel_deviation'] === 1 &&
            (float) $row['confidence'] >= 0.8 &&
            (int) $row['is_ambiguous'] === 0
        ) {
            $count++;
        }
    }

    return round($count / count($valid), 4);
}


    protected function groupAndSummarize(array $rows, string $field): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = (string) ($row[$field] ?? 'n.d.');
            $groups[$key][] = $row;
        }

        ksort($groups);

        return array_map(fn ($items) => $this->summarize($items), $groups);
    }

    protected function groupAndSummarizeMulti(array $rows, array $fields): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = implode(' | ', array_map(fn ($f) => (string) ($row[$f] ?? 'n.d.'), $fields));
            $groups[$key][] = $row;
        }

        ksort($groups);

        return array_map(fn ($items) => $this->summarize($items), $groups);
    }

    protected function sum(array $rows, string $field): int
    {
        return array_sum(array_map(fn ($r) => (int) ($r[$field] ?? 0), $rows));
    }

    protected function rate(array $rows, string $field): ?float
    {
        $valid = array_filter($rows, fn ($r) => isset($r[$field]));

        if (!count($valid)) {
            return null;
        }

        return round($this->sum($valid, $field) / count($valid), 4);
    }

    protected function avg(array $rows, string $field): ?float
    {
        $values = array_values(array_filter(
            array_map(fn ($r) => $r[$field] ?? null, $rows),
            fn ($v) => $v !== null && $v !== ''
        ));

        if (!$values) {
            return null;
        }

        return round(array_sum($values) / count($values), 4);
    }

    protected function median(array $rows, string $field): ?float
    {
        $values = array_values(array_filter(
            array_map(fn ($r) => $r[$field] ?? null, $rows),
            fn ($v) => $v !== null && $v !== ''
        ));

        if (!$values) {
            return null;
        }

        sort($values);
        $count = count($values);
        $mid = intdiv($count, 2);

        if ($count % 2) {
            return round($values[$mid], 4);
        }

        return round(($values[$mid - 1] + $values[$mid]) / 2, 4);
    }

    protected function countValues(array $rows, string $field): array
    {
        $out = [];

        foreach ($rows as $row) {
            $key = (string) ($row[$field] ?? 'n.d.');
            $out[$key] = ($out[$key] ?? 0) + 1;
        }

        arsort($out);

        return $out;
    }

    protected function writeSummaryCsv(array $summary): void
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'grupo',
            'chave',
            'n',
            'hit_rate',
            'hits',
            'ambiguous_rate',

'confidence_when_hit',
'confidence_when_deviation',
#'polarization_when_hit',
#'polarization_when_deviation',
'ambiguous_when_deviation',
'deviation_confident_rate',


'sentiment_negative_rate',
'sentiment_positive_rate',
'sentiment_neutral_rate',
'sentiment_intensity_when_hit',
'sentiment_intensity_when_deviation',
'negative_when_hit',
'negative_when_deviation',
'emotional_deviation_rate',


            'intra_channel_deviation_rate',
            'confidence_avg',
            'polarization_avg',
            'sentiment_intensity_avg',
            'duration_avg',
            'view_avg',
            'like_avg',
            'comment_avg',
        ], ';');

        foreach ($summary as $groupName => $data) {
            if ($groupName === 'geral') {
                $this->writeCsvLine($handle, $groupName, 'geral', $data);
                continue;
            }

            foreach ($data as $key => $stats) {
                $this->writeCsvLine($handle, $groupName, $key, $stats);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::put($this->option('csv'), $content);
    }

    protected function writeCsvLine($handle, string $groupName, string $key, array $stats): void
    {
        fputcsv($handle, [
            $groupName,
            $key,
            $stats['n'] ?? '',
            $stats['hit_rate'] ?? '',
            $stats['hits'] ?? '',
            $stats['ambiguous_rate'] ?? '',


$stats['confidence_when_hit'] ?? '',
$stats['confidence_when_deviation'] ?? '',
#$stats['polarization_when_hit'] ?? '',
#$stats['polarization_when_deviation'] ?? '',
$stats['ambiguous_when_deviation'] ?? '',
$stats['deviation_confident_rate'] ?? '',


$stats['sentiment_negative_rate'] ?? '',
$stats['sentiment_positive_rate'] ?? '',
$stats['sentiment_neutral_rate'] ?? '',
$stats['sentiment_intensity_when_hit'] ?? '',
$stats['sentiment_intensity_when_deviation'] ?? '',
$stats['negative_when_hit'] ?? '',
$stats['negative_when_deviation'] ?? '',
$stats['emotional_deviation_rate'] ?? '',


            $stats['intra_channel_deviation_rate'] ?? '',
            $stats['confidence_avg'] ?? '',
            $stats['polarization_avg'] ?? '',
            $stats['sentiment_intensity_avg'] ?? '',
            $stats['duration_avg'] ?? '',
            $stats['view_avg'] ?? '',
            $stats['like_avg'] ?? '',
            $stats['comment_avg'] ?? '',
        ], ';');
    }
}