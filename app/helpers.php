<?php


use Illuminate\Support\Str;





function datetimeTZtoDateMysql(string $datetimeTZ)
{
    $timestamp = new \DateTimeImmutable($datetimeTZ);
    $bd = $timestamp->format('Y-m-d H:i');
    return $bd;
}

# 123,7 mi para 123700000
#2,999



function retornaFloat($txt)
{
    if (empty($txt) || is_array($txt)) {
        return 0;
    }

    // Remove tudo que não for dígito, ponto ou vírgula
    $txt_limpo = preg_replace('/[^\d.,]/', '', $txt);

    $tem_ponto = strpos($txt_limpo, '.') !== false;
    $tem_virgula = strpos($txt_limpo, ',') !== false;

    if ($tem_ponto && $tem_virgula) {
        // Verifica qual vem por último (define o separador decimal)
        if (strrpos($txt_limpo, ',') > strrpos($txt_limpo, '.')) {
            // Caso: "1.320,90" — ponto separa milhar, vírgula é decimal
            $numero = str_replace(['.', ','], ['', '.'], $txt_limpo);
        } else {
            // Caso: "123,456.78" — vírgula separa milhar, ponto é decimal
            $numero = str_replace(',', '', $txt_limpo);
        }
    } elseif ($tem_virgula) {
        // Caso: "1.200,00" ou "1200,50"
        $numero = str_replace(['.', ','], ['', '.'], $txt_limpo);
    } elseif ($tem_ponto) {
        // Caso: "1.200.50" ou "1200.50"
        $pedaços = explode('.', $txt_limpo);
        $ultima_parte = array_pop($pedaços);

        if (strlen($ultima_parte) <= 2) {
            // Última parte parece centavos
            $numero = str_replace('.', '', implode('.', $pedaços)) . '.' . $ultima_parte;
        } else {
            // Não parece centavos, então remove todos os pontos
            $numero = str_replace('.', '', $txt_limpo);
        }
    } else {
        // Somente dígitos
        $numero = $txt_limpo;
    }

    return (float) $numero;
}


#$v = '2,999';
#dd(retornaMilMilhaoBilhaoToInt($v));


function retorna_float($input)
{
    if (preg_match('/\d+\.?\d+/', $input, $tokens)) {
        return $tokens[0];
    }
    return null;
}


function retornaMilMilhaoBilhaoToInt(?string $txt): int
{
    if (!$txt) return 0;

    $s = trim($txt);

    // Detecta sufixo (k/mil, mi/m, b/bi/bilhão...)
    $mult = 1;
    $hasSuffix = false;
    if (preg_match('/\b(k|mil|mi|m|b|bi|bilh(?:a|õ|o)es?)\b/iu', $s, $m)) {
        $hasSuffix = true;
        $suf = mb_strtolower($m[1], 'UTF-8');
        if ($suf === 'k' || $suf === 'mil') $mult = 1_000;
        elseif (in_array($suf, ['m', 'mi', 'milhão', 'milhoes', 'milhões'])) $mult = 1_000_000;
        elseif (in_array($suf, ['b', 'bi', 'bilhao', 'bilhão', 'bilhoes', 'bilhões'])) $mult = 1_000_000_000;
    }

    // Extrai parte numérica
    if (!preg_match('/\d[\d\.\,\s  ]*/u', $s, $mm)) return 0; // inclui NBSP
    $num = preg_replace('/[\s  ]+/u', '', $mm[0]); // remove espaços

    if (!$hasSuffix) {
        // Sem sufixo => considere sempre separadores de milhar
        // "4,123,237" / "1.234.567" / "12 345" -> só dígitos
        $base = (int) preg_replace('/\D/', '', $num);
        return $base; // multiplicador = 1
    }

    // Com sufixo => aceitar decimal (1,2 mi / 1.2M etc.)
    // Normaliza decimal para ponto: mantém só o último como decimal
    $num = str_replace(',', '.', $num);
    $num = preg_replace('/\.(?=.*\.)/', '', $num); // remove pontos exceto o último
    $base = (float) $num;

    return (int) round($base * $mult);
}



function limpaEspacosAcentuacao($str)
{
    $limpo = Str::ascii($str);
    $limpo = preg_replace('/&nbsp;/', ' ', $limpo);
    $limpo = Str::squish($limpo);
    return $limpo;
}

function limpaEspacosTabs($txt)
{
    $limpo = preg_replace('/&nbsp;/', ' ', $txt);

    #Str::squish(); esse tira extra spacos

    $limpo = preg_replace('/\s\s+/', ' ', $limpo);

    $limpo = preg_replace('/(?:\s\s+|\n|\t|\r)/', '', $limpo);

    return trim($limpo);
}




function filtraDigitos($txt)
{
    $limpo = preg_replace('/\D/', '', $txt);
    return $limpo;
}

function filtraLetras($txt)
{
    $limpo = preg_replace('/\W/', '', $txt);
    return $limpo;
}

function filtraDateTime($txt, $toMysql = true)
{
    $re_d2y4 = '((\d{2})[-\/](\d{2})[-\/](\d{2,4}))';
    $re_y4d2 = '(\d{4}[-\/]\d{2}[-\/]\d{2})';
    $re_hora = '([01]?[0-9]|2[0-3])[:|h]([0-5][0-9])(?::([0-9][0-9]))?';

    $date = $time = '';
    if (preg_match('/' . $re_d2y4 . '/', $txt, $res)) {
        if ($toMysql) {
            $y = (string) $res[4];
            if (strlen($y) == 2) {
                $ano = '20' . $y;
                $date = $ano . '-' . $res[3] . '-' . $res[2];
            } else {
                $date = $y . '-' . $res[3] . '-' . $res[2];
            }
        } else {
            $date = $res[1];
        }
    }

    if (preg_match('/' . $re_hora . '/', $txt, $res)) {
        $hora = $res[1];
        $min = $res[2];
        $seg = $res[3] ?? null;
        $time = $hora . ':' . $min;
        if ($seg) {
            $time .= ':' . $seg;
        }
    }

    $datetime = $date . ' ' . $time;

    return $datetime;
}


function ISO8601ToSeconds($ISO8601)
{
    $interval = new \DateInterval($ISO8601);

    return ($interval->d * 24 * 60 * 60) +    ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;
}

#50000000 para 5T
function kmbt($number)
{
    $abbrevs = [12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => ''];

    foreach ($abbrevs as $exponent => $abbrev) {
        if (abs($number) >= pow(10, $exponent)) {
            $display = $number / pow(10, $exponent);
            $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
            $number = number_format($display, $decimals) . $abbrev;
            break;
        }
    }

    return $number;
}



#5b para 5000000
function return_kmb_to_integer($val)
{
    #dump($val);
    if ($val) {
        $val = trim($val, '$');


        #var_dump($val);
        if (strlen($val) > 1) {
            $last = strtolower($val[strlen($val) - 1]);
            $val = (float) $val;
            switch ($last) {
                case 'b':
                    $val *= 1000;
                case 'm':
                    $val *= 1000;
                case 'k':
                    #dd((float) $val);
                    $val *= 1000;
                default:
                    $val *= 1;
            }
        }
    }

    return (int) $val;
}



function timeToSeconds(string $time): int
{
    $arr = explode(':', $time);
    if (count($arr) === 3) {
        return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
    }
    return $arr[0] * 60 + $arr[1];
}

function colorLog($msg, $type = 'i')
{
    switch ($type) {
        case 'e': //error
            echo "\033[31m $msg \033[0m\n";
            break;
        case 's': //success
            echo "\033[32m $msg \033[0m\n";
            break;
        case 'w': //warning
            echo "\033[33m $msg \033[0m\n";
            break;
        case 'i': //info
            echo "\033[36m $msg \033[0m\n";
            break;
        default:
            break;
    }
}

function isolaTrechoHtml($dom, $die = false)
{
    echo "\n\n\n";
    echo colorLog($dom->saveHTML(), 'e');
    echo "\n\n\n";
    if ($die) {
        die;
    }
}
function squish($value)
{
    return preg_replace('~(\s|\x{3164}|\x{1160})+~u', ' ', preg_replace('~^[\s\x{FEFF}]+|[\s\x{FEFF}]+$~u', '', $value));
}





function limpaStr2BD($str)
{
    ######## intervalos permitidos : da white list
    #32 - 38 = espaco - &
    #40 - 90 = ( - Z
    #97 - 122 = a - z

    $permitidos1 = range(32, 38);
    $permitidos2 = range(40, 93); #inclui mais 3 91[ 92 \ 93]
    $permitidos3 = [95]; #underline _
    $permitidos4 = range(97, 122);
    $permit = array_merge($permitidos1, $permitidos2, $permitidos3, $permitidos4);

    $str_nova = "";

    for ($i = 0; $i < strlen($str); $i++) {
        if (in_array(ord($str[$i]), $permit)) {
            $str_nova .= $str[$i];
        }
    }

    return $str_nova;
}


function limpaStrAlfaNum($str)
{
    ######## intervalos permitidos : da white list
    #32       = espaco 
    #48 - 57  = 0 - 9
    #65 - 90 = A - Z
    #97 - 122 = a - z

    $permitidos1 = [32];
    $permitidos2 = range(48, 57);
    $permitidos3 = range(65, 90);
    $permitidos4 = range(97, 122);
    $permit = array_merge($permitidos1, $permitidos2, $permitidos3, $permitidos4);

    $str_nova = "";

    for ($i = 0; $i < strlen($str); $i++) {
        if (in_array(ord($str[$i]), $permit)) {
            $str_nova .= $str[$i];
        }
    }

    return $str_nova;
}



function retornaMes(string $mes)
{
    $mes = substr($mes, 0, 3);
    $mes = strtolower($mes);

    switch (trim($mes, ".")) {
        case 'jan':
            $mes = 1;
            break;
        case 'fev':
        case 'feb':
            $mes = 2;
            break;
        case 'mar':
            $mes = 3;
            break;
        case 'abr':
        case 'apr':
            $mes = 4;
            break;
        case 'mai':
        case 'may':
            $mes = 5;
            break;
        case 'jun':
            $mes = 6;
            break;
        case 'jul':
            $mes = 7;
            break;
        case 'ago':
        case 'aug':
            $mes = 8;
            break;
        case 'set':
        case 'sep':
            $mes = 9;
            break;
        case 'out':
        case 'oct':
            $mes = 10;
            break;
        case 'nov':
            $mes = 11;
            break;
        case 'dez':
        case 'dec':
            $mes = 12;
            break;
    }
    return $mes;
}
