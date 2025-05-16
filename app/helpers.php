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

#dd(retornaMilMilhaoBilhaoToInt('512 mil assinantes'));

function retornaMilMilhaoBilhaoToInt(string $txt)
{
    #dump($txt);
    $txt = str_replace(",", ".", $txt);
    #dump($txt);

    # atencao se for so m ele pega primeiro em relacao ao mi
    $re = '([\d\.]+)\s?(mil|M|mi|k|K|B|b)?';
    if (preg_match('/' . $re . '/', $txt, $res)) {
        $num = $res[1];
        $num = str_replace(".", "", $num);

        $letra = "";
        if (isset($res[2])) {
            $letras = strtolower($res[2]);
            #dump($letras);
            switch ($letras) {
                case "mil":
                case "k":
                case "m":
                    $letra = 'k';
                    break;

                case "milhoes":
                case "mi":
                    $letra = 'm';
                    break;

                case "bi":
                case "b":
                    $letra = 'b';
                    break;
            }
            $val = $num . $letra;
            $num = return_kmb_to_integer($val);

        }
        #dd($num);
        return $num;
    }
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



function retornaFloat($txt)
{
    #dump($txt);
    $so_digitos = filtraDigitos($txt);
    if (is_numeric($so_digitos)) {
        $fl = (float) round($so_digitos / 100, 2);
        return $fl;
    }
    return 0;
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

function retorna_float($input)
{
    if (preg_match('/\d+\.\d+/', $input, $tokens)) {
        return $tokens[0];
    }
    return null;
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
