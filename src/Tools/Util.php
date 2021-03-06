<?php

namespace PhpBoleto\Tools;

use Carbon\Carbon;
use DateTime;
use DateTimeInterface;
use Exception;
use NumberFormatter;

/**
 * Class Util
 *
 * @TODO validar tamanho nosso numero
 * @TODO validar processar
 * @TODO validar float nos numeros
 *
 * @package PhpBoleto
 */
final class Util
{
    public static $bancos = [
        '246' => 'Banks ABC Brasil S.A.',
        '025' => 'Banks Alfa S.A.',
        '641' => 'Banks Alvorada S.A.',
        '029' => 'Banks Banerj S.A.',
        '000' => 'Banks Bankpar S.A.',
        '740' => 'Banks Barclays S.A.',
        '107' => 'Banks BBM S.A.',
        '031' => 'Banks Beg S.A.',
        '739' => 'Banks BGN S.A.',
        '096' => 'Banks BM&F de Serviços de Liquidação e Custódia S.A',
        '318' => 'Banks BMG S.A.',
        '752' => 'Banks BNP Paribas Brasil S.A.',
        '248' => 'Banks Boavista Interatlântico S.A.',
        '218' => 'Banks Bonsucesso S.A.',
        '065' => 'Banks Bracce S.A.',
        '036' => 'Banks Bradesco BBI S.A.',
        '204' => 'Banks Bradesco Cartões S.A.',
        '394' => 'Banks Bradesco Financiamentos S.A.',
        '237' => 'Banks Bradesco S.A.',
        '225' => 'Banks Brascan S.A.',
        '208' => 'Banks BTG Pactual S.A.',
        '044' => 'Banks BVA S.A.',
        '263' => 'Banks Cacique S.A.',
        '473' => 'Banks Caixa Geral - Brasil S.A.',
        '040' => 'Banks Cargill S.A.',
        '233' => 'Banks Cifra S.A.',
        '745' => 'Banks Citibank S.A.',
        'M08' => 'Banks Citicard S.A.',
        'M19' => 'Banks CNH Capital S.A.',
        '215' => 'Banks Comercial e de Investimento Sudameris S.A.',
        '756' => 'Banks Cooperativo do Brasil S.A. - BANCOOB',
        '748' => 'Banks Cooperativo Sicredi S.A.',
        '222' => 'Banks Credit Agricole Brasil S.A.',
        '505' => 'Banks Credit Suisse (Brasil) S.A.',
        '229' => 'Banks Cruzeiro do Sul S.A.',
        '003' => 'Banks da Amazônia S.A.',
        '083' => 'Banks da China Brasil S.A.',
        '707' => 'Banks Daycoval S.A.',
        'M06' => 'Banks de Lage Landen Brasil S.A.',
        '024' => 'Banks de Pernambuco S.A. - BANDEPE',
        '456' => 'Banks de Tokyo-Mitsubishi UFJ Brasil S.A.',
        '214' => 'Banks Dibens S.A.',
        '001' => 'Banks do Brasil S.A.',
        '047' => 'Banks do Estado de Sergipe S.A.',
        '037' => 'Banks do Estado do Pará S.A.',
        '041' => 'Banks do Estado do Rio Grande do Sul S.A.',
        '004' => 'Banks do Nordeste do Brasil S.A.',
        '265' => 'Banks Fator S.A.',
        'M03' => 'Banks Fiat S.A.',
        '224' => 'Banks Fibra S.A.',
        '626' => 'Banks Ficsa S.A.',
        'M18' => 'Banks Ford S.A.',
        'M07' => 'Banks GMAC S.A.',
        '612' => 'Banks Guanabara S.A.',
        'M22' => 'Banks Honda S.A.',
        '063' => 'Banks Ibi S.A. Banks Múltiplo',
        'M11' => 'Banks IBM S.A.',
        '604' => 'Banks Industrial do Brasil S.A.',
        '320' => 'Banks Industrial e Comercial S.A.',
        '653' => 'Banks Indusval S.A.',
        '249' => 'Banks Investcred Unibanco S.A.',
        '184' => 'Banks Itaú BBA S.A.',
        '479' => 'Banks ItaúBank S.A',
        'M09' => 'Banks Itaucred Financiamentos S.A.',
        '376' => 'Banks J. P. Morgan S.A.',
        '074' => 'Banks J. Safra S.A.',
        '217' => 'Banks John Deere S.A.',
        '600' => 'Banks Luso Brasileiro S.A.',
        '389' => 'Banks Mercantil do Brasil S.A.',
        '746' => 'Banks Modal S.A.',
        '045' => 'Banks Opportunity S.A.',
        '079' => 'Banks Original do Agronegócio S.A.',
        '623' => 'Banks Panamericano S.A.',
        '611' => 'Banks Paulista S.A.',
        '643' => 'Banks Pine S.A.',
        '638' => 'Banks Prosper S.A.',
        '747' => 'Banks Rabobank International Brasil S.A.',
        '356' => 'Banks Real S.A.',
        '633' => 'Banks Rendimento S.A.',
        'M16' => 'Banks Rodobens S.A.',
        '072' => 'Banks Rural Mais S.A.',
        '453' => 'Banks Rural S.A.',
        '422' => 'Banks Safra S.A.',
        '033' => 'Banks Santander (Brasil) S.A.',
        '749' => 'Banks Simples S.A.',
        '366' => 'Banks Société Générale Brasil S.A.',
        '637' => 'Banks Sofisa S.A.',
        '012' => 'Banks Standard de Investimentos S.A.',
        '464' => 'Banks Sumitomo Mitsui Brasileiro S.A.',
        '082' => 'Banks Topázio S.A.',
        'M20' => 'Banks Toyota do Brasil S.A.',
        '634' => 'Banks Triângulo S.A.',
        'M14' => 'Banks Volkswagen S.A.',
        'M23' => 'Banks Volvo (Brasil) S.A.',
        '655' => 'Banks Votorantim S.A.',
        '610' => 'Banks VR S.A.',
        '119' => 'Banks Western Union do Brasil S.A.',
        '370' => 'Banks WestLB do Brasil S.A.',
        '021' => 'BANESTES S.A. Banks do Estado do Espírito Santo',
        '719' => 'Banif-Banks Internacional do Funchal (Brasil)S.A.',
        '755' => 'Bank of America Merrill Lynch Banks Múltiplo S.A.',
        '073' => 'BB Banks Popular do Brasil S.A.',
        '250' => 'BCV - Banks de Crédito e Varejo S.A.',
        '078' => 'BES Investimento do Brasil S.A.-Banks de Investimento',
        '069' => 'BPN Brasil Banks Múltiplo S.A.',
        '070' => 'BRB - Banks de Brasília S.A.',
        '104' => 'Caixa Econômica Federal',
        '477' => 'Citibank S.A.',
        '081' => 'Concórdia Banks S.A.',
        '487' => 'Deutsche Bank S.A. - Banks Alemão',
        '064' => 'Goldman Sachs do Brasil Banks Múltiplo S.A.',
        '062' => 'Hipercard Banks Múltiplo S.A.',
        '399' => 'HSBC Bank Brasil S.A.',
        '492' => 'ING Bank N.V.',
        '652' => 'Itaú Unibanco Holding S.A.',
        '341' => 'Itaú Unibanco S.A.',
        '488' => 'JPMorgan Chase Bank',
        '751' => 'Scotiabank Brasil S.A. Banks Múltiplo',
        '409' => 'UNIBANCO - União de Bancos Brasileiros S.A.',
        '230' => 'Unicard Banks Múltiplo S.A.',
        'XXX' => 'Desconhecido',
    ];

    /**
     * Retorna a String em MAIUSCULO
     *
     * @param $string
     * @return string
     */
    public static function upper($string)
    {
        return strtr(mb_strtoupper($string), "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß");
    }

    /**
     * Retorna a String em minusculo
     *
     * @param $string
     * @return string
     */
    public static function lower($string)
    {
        return strtr(mb_strtolower($string), "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ");
    }

    /**
     * Retorna a String em minusculo
     *
     * @param $string
     * @return string
     */
    public static function upFirst($string)
    {
        return ucfirst(self::lower($string));
    }

    /**
     * Retorna somente as letras da string
     *
     * @param $string
     * @return mixed
     */
    public static function lettersOnly($string)
    {
        return preg_replace('/[^[:alpha:]]/', '', $string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param $string
     * @return mixed
     */
    public static function onlyLetters($string)
    {
        return self::lettersOnly($string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param $string
     * @return mixed
     */
    public static function lettersNot($string)
    {
        return preg_replace('/[[:alpha:]]/', '', $string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param $string
     * @return mixed
     */
    public static function notLetters($string)
    {
        return self::lettersNot($string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param $string
     * @return mixed
     */
    public static function numbersOnly($string)
    {
        return preg_replace('/[^[:digit:]]/', '', $string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param $string
     * @return mixed
     */
    public static function onlyNumbers($string)
    {
        return self::numbersOnly($string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param $string
     * @return mixed
     */
    public static function numbersNot($string)
    {
        return preg_replace('/[[:digit:]]/', '', $string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param $string
     * @return mixed
     */
    public static function notNumbers($string)
    {
        return self::numbersNot($string);
    }

    /**
     * Retorna somente alfanumericos
     *
     * @param $string
     * @return mixed
     */
    public static function alphanumberOnly($string)
    {
        return preg_replace('/[^[:alnum:]]/', '', $string);
    }

    /**
     * Retorna somente alfanumericos
     *
     * @param $string
     * @return mixed
     */
    public static function onlyAlphanumber($string)
    {
        return self::alphanumberOnly($string);
    }

    /**
     * Função para limpar acentos de uma string
     *
     * @param $string
     * @return string
     */
    public static function normalizeChars($string)
    {
        $normalizeChars = array(
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Å' => 'A', 'Ä' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'Eth',
            'Ñ' => 'N', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Ŕ' => 'R',

            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'å' => 'a', 'ä' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'eth',
            'ñ' => 'n', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ŕ' => 'r', 'ÿ' => 'y',

            'ß' => 'sz', 'þ' => 'thorn',
        );
        return strtr($string, $normalizeChars);
    }

    /**
     * Mostra o Valor no float Formatado
     *
     * @param string $number
     * @param integer $decimals
     * @param boolean $showThousands
     * @return string
     */
    public static function nFloat($number, $decimals = 2, $showThousands = false)
    {
        if (is_null($number) || empty(self::onlyNumbers($number))) {
            return '';
        }
        $pontuacao = preg_replace('/[0-9]/', '', $number);
        $locale = (mb_substr($pontuacao, -1, 1) == ',') ? "pt-BR" : "en-US";
        $formater = new NumberFormatter($locale, NumberFormatter::DECIMAL);

        if ($decimals === false) {
            $decimals = 2;
            preg_match_all('/[0-9][^0-9]([0-9]+)/', $number, $matches);
            if (!empty($matches[1])) {
                $decimals = mb_strlen(rtrim($matches[1][0], 0));
            }
        }

        return number_format($formater->parse($number, NumberFormatter::TYPE_DOUBLE), $decimals, '.', ($showThousands ? ',' : ''));
    }

    /**
     * Mostra o Valor no real Formatado
     *
     * @param float $number
     * @param boolean $fixed
     * @param boolean $symbol
     * @param integer $decimals
     * @return string
     */
    public static function numberInReal($number, $decimals = 2, $symbol = true, $fixed = true)
    {
        if (is_null($number) || empty(self::onlyNumbers($number))) {
            return '';
        }
        $formatter = new NumberFormatter("pt-BR", NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, ($fixed ? $decimals : 1));
        if ($decimals === false) {
            $decimals = 2;
            preg_match_all('/[0-9][^0-9]([0-9]+)/', $number, $matches);
            if (!empty($matches[1])) {
                $decimals = mb_strlen(rtrim($matches[1][0], 0));
            }
        }
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        if (!$symbol) {
            $pattern = preg_replace("/[¤]/", '', $formatter->getPattern());
            $formatter->setPattern($pattern);
        } else {
            // ESPAÇO DEPOIS DO SIMBOLO
            $pattern = str_replace("¤", "¤ ", $formatter->getPattern());
            $formatter->setPattern($pattern);
        }
        return $formatter->formatCurrency($number, $formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE));
    }

    /**
     * Return percent x of y;
     *
     * @param $big
     * @param $small
     * @param int $defaultOnZero
     * @return string
     */
    public static function percentOf($big, $small, $defaultOnZero = 0)
    {
        $result = $big > 0.01 ? (($small * 100) / $big) : $defaultOnZero;
        return self::nFloat($result);
    }

    /**
     * Return percentage of value;
     *
     * @param $big
     * @param $percent
     * @return int|string
     */
    public static function percent($big, $percent)
    {
        if ($percent < 0.01) {
            return 0;
        }
        return self::nFloat($big * ($percent / 100));
    }

    /**
     * Função para mascarar uma string, mascara tipo ##-##-##
     *
     * @param $val
     * @param $mask
     * @return string
     */
    public static function maskString($val, $mask)
    {
        if (empty($val)) {
            return $val;
        }
        $maskared = '';
        $k = 0;
        if (is_numeric($val)) {
            $val = sprintf('%0' . mb_strlen(preg_replace('/[^#]/', '', $mask)) . 's', $val);
        }
        for ($i = 0; $i <= mb_strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }

        return $maskared;
    }

    /**
     * @param $n
     * @param $loop
     * @param int $insert
     * @return string
     */
    public static function numberFormatGeral($n, $loop, $insert = 0)
    {
        // Removo os caracteras a mais do que o pad solicitado caso a string seja maior
        $n = mb_substr(self::onlyNumbers($n), 0, $loop);
        return str_pad($n, $loop, $insert, STR_PAD_LEFT);
    }

    /**
     * @param $tipo
     * @param $valor
     * @param $tamanho
     * @param int $dec
     * @param string $sFill
     * @return string
     * @throws Exception
     */
    public static function formatCnab($tipo, $valor, $tamanho, $dec = 0, $sFill = '')
    {
        $tipo = self::upper($tipo);
        if (in_array($tipo, array('9', 9, 'N', '9L', 'NL'))) {
            if ($tipo == '9L' || $tipo == 'NL') {
                $valor = self::onlyNumbers($valor);
            }
            $left = '';
            $sFill = 0;
            $type = 's';
            $valor = ($dec > 0) ? sprintf("%.{$dec}f", $valor) : $valor;
            $valor = str_replace(array(',', '.'), '', $valor);
        } elseif (in_array($tipo, array('A', 'X'))) {
            $left = '-';
            $type = 's';
            $valor = self::upper(self::normalizeChars($valor));
        } else {
            throw new Exception('Tipo inválido');
        }
        return sprintf("%{$left}{$sFill}{$tamanho}{$type}", mb_substr($valor, 0, $tamanho));
    }

    /**
     * @param DateTimeInterface $date
     * @param string $format
     * @return int
     */
    public static function dueDateFactor(DateTimeInterface $date, $format = 'Y-m-d'): int
    {
        return (int)DateTime::createFromFormat($format, '1997-10-07')->diff($date, true)->format('%r%a');
    }

    /**
     * @param Carbon $date
     * @param string $format
     * @return string
     */
    public static function dataJuliano(Carbon $date, $format = 'Y-m-d')
    {
        $date = ($date instanceof Carbon) ? $date : Carbon::createFromFormat($format, $date);
        $dateDiff = $date->copy()->day(31)->month(12)->subYear(1)->diffInDays($date);
        return $dateDiff . mb_substr($date->year, -1);
    }

    /**
     * @param $factor
     * @param string $format
     * @return string|static
     */
    public static function fatorVencimentoBack($factor, $format = 'Y-m-d')
    {
        $date = Carbon::create(1997, 10, 7, 0, 0, 0)->addDay($factor);
        return $format ? $date->format($format) : $date;
    }

    /**
     * @param $n
     * @param int $factor
     * @param int $base
     * @param int $x10
     * @param int $resto10
     * @return int
     */
    public static function modulo11($n, $factor = 2, $base = 9, $x10 = 0, $resto10 = 0)
    {
        $sum = 0;
        for ($i = mb_strlen($n); $i > 0; $i--) {
            $sum += mb_substr($n, $i - 1, 1) * $factor;
            if ($factor == $base) {
                $factor = 1;
            }
            $factor++;
        }

        if ($x10 == 0) {
            $sum *= 10;
            $digito = $sum % 11;
            if ($digito == 10) {
                $digito = $resto10;
            }
            return $digito;
        }
        return $sum % 11;
    }

    /**
     * @param $n
     * @return int
     */
    public static function modulo10($n)
    {
        $chars = array_reverse(str_split($n, 1));
        $odd = array_intersect_key($chars, array_fill_keys(range(1, count($chars), 2), null));
        $even = array_intersect_key($chars, array_fill_keys(range(0, count($chars), 2), null));
        $even = array_map(
            function ($n) {
                return ($n >= 5) ? 2 * $n - 9 : 2 * $n;
            }, $even
        );
        $total = array_sum($odd) + array_sum($even);
        return ((floor($total / 10) + 1) * 10 - $total) % 10;
    }

    /**
     * @param array $a
     * @return string
     * @throws Exception
     */
    public static function array2Controle(array $a)
    {
        if (preg_match('/[0-9]/', implode('', array_keys($a)))) {
            throw new Exception('Somente chave alfanumérica no array, para separar o controle pela chave');
        }

        $controle = '';
        foreach ($a as $key => $value) {
            $controle .= sprintf('%s%s', $key, $value);
        }

        if (mb_strlen($controle) > 25) {
            throw new Exception('Controle muito grande, máximo permitido de 25 caracteres');
        }

        return $controle;
    }

    /**
     * @param $controle
     * @return array|string
     */
    public static function controle2array($controle)
    {
        $matches = '';
        $matches_founded = '';
        preg_match_all('/(([A-Za-zÀ-Úà-ú]+)([0-9]*))/', $controle, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $match) {
                $matches_founded[$match[2]] = (int)$match[3];
            }
            return $matches_founded;
        }
        return [$controle];
    }

    /**
     * Pela remessa cria um retorno fake para testes.
     *
     * @param $file
     * @param string $ocorrencia
     * @return string
     * @throws Exception
     */
    public static function criarRetornoFake($file, $ocorrencia = '02')
    {
        $remessa = file($file);
        $banco = self::remove(77, 79, $remessa[0]);
        $retorno[0] = array_fill(0, 400, '0');

        // header
        self::addLine($retorno[0], 1, 9, '02RETORNO');
        switch ($banco) {
            case Interfaces\Slip\SlipInterface::BANK_CODE_BB:
                self::addLine($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::addLine($retorno[0], 31, 31, self::remove(31, 31, $remessa[0]));
                self::addLine($retorno[0], 32, 39, self::remove(32, 39, $remessa[0]));
                self::addLine($retorno[0], 40, 40, self::remove(40, 40, $remessa[0]));
                self::addLine($retorno[0], 150, 156, self::remove(130, 136, $remessa[0]));
                break;
            case Interfaces\Slip\SlipInterface::BANK_CODE_SANTANDER:
                self::addLine($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::addLine($retorno[0], 39, 46, '0' . self::remove(40, 46, $remessa[0]));
                break;
            case Interfaces\Slip\SlipInterface::BANK_CODE_CEF:
                self::addLine($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::addLine($retorno[0], 31, 36, self::remove(31, 36, $remessa[0]));
                break;
            case Interfaces\Slip\SlipInterface::BANK_CODE_BRADESCO:
                self::addLine($retorno[0], 27, 46, self::remove(27, 46, $remessa[0]));
                break;
            case Interfaces\Slip\SlipInterface::BANK_CODE_ITAU:
                self::addLine($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::addLine($retorno[0], 33, 37, self::remove(33, 37, $remessa[0]));
                self::addLine($retorno[0], 38, 38, self::remove(38, 38, $remessa[0]));
                break;
            case Interfaces\Slip\SlipInterface::BANK_CODE_HSBC:
                self::addLine($retorno[0], 28, 31, self::remove(28, 31, $remessa[0]));
                self::addLine($retorno[0], 38, 43, self::remove(38, 43, $remessa[0]));
                self::addLine($retorno[0], 44, 44, self::remove(44, 44, $remessa[0]));
                break;
            case Interfaces\Slip\SlipInterface::BANK_CODE_SICREDI:
                self::addLine($retorno[0], 27, 31, self::remove(27, 31, $remessa[0]));
                self::addLine($retorno[0], 32, 45, self::remove(32, 45, $remessa[0]));
                self::addLine($retorno[0], 111, 117, self::remove(111, 117, $remessa[0]));
                break;
            case Interfaces\Slip\SlipInterface::BANK_CODE_BANRISUL:
                self::addLine($retorno[0], 27, 39, self::remove(18, 30, $remessa[0]));
                self::addLine($retorno[0], 47, 76, self::remove(47, 76, $remessa[0]));
                break;
            default:
                throw new Exception("Banks: $banco, inválido");
        }
        self::addLine($retorno[0], 77, 79, $banco);
        self::addLine($retorno[0], 95, 100, date('dmy'));
        self::addLine($retorno[0], 395, 400, sprintf('%06s', count($retorno)));

        array_shift($remessa); // removo o header
        array_pop($remessa); // remove o trailer

        foreach ($remessa as $detalhe) {
            $i = count($retorno);
            $retorno[$i] = array_fill(0, 400, '0');
            self::addLine($retorno[$i], 1, 1, '1');
            self::addLine($retorno[$i], 109, 110, sprintf('%02s', $ocorrencia));
            self::addLine($retorno[$i], 111, 116, date('dmy'));
            self::addLine($retorno[$i], 153, 165, self::remove(127, 139, $detalhe));
            self::addLine($retorno[$i], 254, 266, self::remove(127, 139, $detalhe));
            self::addLine($retorno[$i], 147, 152, self::remove(121, 126, $detalhe));
            self::addLine($retorno[$i], 117, 126, self::remove(111, 120, $detalhe));
            self::addLine($retorno[$i], 395, 400, sprintf('%06s', count($retorno)));
            switch ($banco) {
                case Interfaces\Slip\SlipInterface::BANK_CODE_BB:
                    if (self::remove(1, 1, $detalhe) != 7) {
                        unset($retorno[$i]);
                        continue 2;
                    }
                    self::addLine($retorno[$i], 1, 1, '7');
                    self::addLine($retorno[$i], 64, 80, self::remove(64, 80, $detalhe));
                    break;
                case Interfaces\Slip\SlipInterface::BANK_CODE_SANTANDER:
                    self::addLine($retorno[$i], 63, 71, self::remove(63, 71, $detalhe));
                    self::addLine($retorno[$i], 384, 385, self::remove(384, 385, $detalhe));
                    break;
                case Interfaces\Slip\SlipInterface::BANK_CODE_CEF:
                    self::addLine($retorno[$i], 57, 73, self::remove(57, 73, $detalhe));
                    break;
                case Interfaces\Slip\SlipInterface::BANK_CODE_BRADESCO:
                    self::addLine($retorno[$i], 25, 29, self::remove(25, 29, $detalhe));
                    self::addLine($retorno[$i], 30, 36, self::remove(30, 36, $detalhe));
                    self::addLine($retorno[$i], 37, 37, self::remove(37, 37, $detalhe));
                    self::addLine($retorno[$i], 71, 82, self::remove(71, 82, $detalhe));
                    break;
                case Interfaces\Slip\SlipInterface::BANK_CODE_ITAU:
                    self::addLine($retorno[$i], 86, 94, self::remove(63, 70, $detalhe));
                    break;
                case Interfaces\Slip\SlipInterface::BANK_CODE_HSBC:
                    self::addLine($retorno[$i], 63, 73, self::remove(63, 73, $detalhe));
                    break;
                case Interfaces\Slip\SlipInterface::BANK_CODE_SICREDI:
                    self::addLine($retorno[$i], 48, 62, '00000' . self::remove(48, 56, $detalhe));
                    break;
                case Interfaces\Slip\SlipInterface::BANK_CODE_BANRISUL:
                    self::addLine($retorno[$i], 38, 62, self::remove(38, 62, $detalhe));
                    self::addLine($retorno[$i], 63, 72, self::remove(111, 120, $detalhe));
                    self::addLine($retorno[$i], 18, 30, self::remove(18, 30, $detalhe));
                    break;
                default:
                    throw new Exception("Banks: $banco, inválido");
            }
        }

        $i = count($retorno);
        $retorno[$i] = array_fill(0, 400, '0');
        self::addLine($retorno[$i], 1, 1, '9');
        self::addLine($retorno[$i], 395, 400, sprintf('%06s', count($retorno)));

        $retorno = array_map(
            function ($a) {
                return implode('', $a);
            }, $retorno
        );

        return implode("\r\n", $retorno);
    }

    /**
     * Remove trecho do array.
     *
     * @param $i
     * @param $f
     * @param $array
     * @return string
     * @throws Exception
     */
    public static function remove($i, $f, &$array)
    {
        if (is_string($array)) {
            $array = str_split(rtrim($array, chr(10) . chr(13) . "\n" . "\r"), 1);
        }
        $i--;

        if ($i > 398 || $f > 400) {
            throw new Exception('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new Exception('$ini é maior que o $fim');
        }

        $t = $f - $i;
        $toSplice = $array;

        if ($toSplice == null) {
            return "";
        } else {
            return trim(implode('', array_splice($toSplice, $i, $t)));
        }
    }

    /**
     * Função para add valor a linha nas posições informadas.
     *
     * @param $line
     * @param $i
     * @param $f
     * @param $value
     * @return array
     * @throws Exception
     */
    public static function addLine(&$line, $i, $f, $value)
    {
        $i--;

        if ($i > 398 || $f > 400) {
            throw new Exception('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new Exception('$ini é maior que o $fim');
        }

        $t = $f - $i;

        if (mb_strlen($value) > $t) {
            throw new Exception(sprintf('String $valor maior que o tamanho definido em $ini e $fim: $valor=%s e tamanho é de: %s', mb_strlen($value), $t));
        }

        $value = sprintf("%{$t}s", $value);
        $value = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

        return array_splice($line, $i, $t, $value);
    }

    /**
     * Validação para o tipo de cnab 240
     *
     * @param $content
     * @return bool
     */
    public static function isCnab240($content)
    {
        $content = is_array($content) ? $content[0] : $content;
        return mb_strlen(rtrim($content, "\r\n")) == 240 ? true : false;
    }

    /**
     * Validação para o tipo de cnab 400
     *
     * @param $content
     * @return bool
     */
    public static function isCnab400($content)
    {
        $content = is_array($content) ? $content[0] : $content;
        return mb_strlen(rtrim($content, "\r\n")) == 400 ? true : false;
    }

    /**
     * @param $file
     * @return array|bool
     */
    public static function file2array($file)
    {
        if (is_array($file) && isset($file[0]) && is_string($file[0])) {
            return $file;
        } elseif (is_string($file) && is_file($file) && file_exists($file)) {
            return file($file);
        } elseif (is_string($file) && strstr($file, PHP_EOL) !== false) {
            $file_content = explode(PHP_EOL, $file);
            if (empty(end($file_content))) {
                array_pop($file_content);
            }
            reset($file_content);
            return $file_content;
        }
        return false;
    }

    /**
     * Valida se o header é de um arquivo retorno valido, 240 ou 400 posicoes
     *
     * @param $header
     * @return bool
     */
    public static function isHeaderRetorno($header)
    {
        if (!self::isCnab240($header) && !self::isCnab400($header)) {
            return false;
        }
        if (self::isCnab400($header) && mb_substr($header, 0, 9) != '02RETORNO') {
            return false;
        }
        if (self::isCnab240($header) && mb_substr($header, 142, 1) != '2') {
            return false;
        }
        return true;
    }

    /**
     * @param $obj
     * @param array $params
     */
    public static function fillClass(&$obj, array $params)
    {
        foreach ($params as $param => $value) {
            $param = str_replace(' ', '', ucwords(str_replace('_', ' ', $param)));
            if (method_exists($obj, 'getProtectedFields') && in_array(lcfirst($param), $obj->getProtectedFields())) {
                continue;
            }
            if (method_exists($obj, 'set' . ucwords($param))) {
                $obj->{'set' . ucwords($param)}($value);
            }
        }
    }

    /**
     * @param $property
     * @param $obj
     * @return Person
     * @throws Exception
     */
    public static function addPerson(&$property, $obj)
    {
        if (is_subclass_of($obj, 'PhpBoleto\\Interfaces\\Person\\PersonInterface')) {
            $property = $obj;
            return $obj;
        } elseif (is_array($obj)) {
            $obj = new Person($obj);
            $property = $obj;
            return $obj;
        }
        throw new Exception('Objeto inválido, somente Person e Array');
    }
}
