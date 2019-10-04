<?php
namespace PhpBoleto\Tools;

class CheckDigitCalculation
{

    /*
    |--------------------------------------------------------------------------
    | 001 - Banks do Brasil
    |--------------------------------------------------------------------------
    */
    public static function bbAgency($agency)
    {
        return Util::modulo11($agency, 2, 9, 0, 'X');
    }

    public static function bbAccount($account)
    {
        return Util::modulo11($account, 2, 9, 0, 'X');
    }

    public static function bbOurNumber($nossoNumero)
    {
        return strlen($nossoNumero) >= 17 ? null : Util::modulo11($nossoNumero);
    }

    /*
    |--------------------------------------------------------------------------
    | 004 - Banks do Nordeste
    |--------------------------------------------------------------------------
    */
    public static function bnbAgency($agency)
    {
        $checkDigit = Util::modulo11($agency, 2, 9, 0);
        return $checkDigit == 1 ? 'X' : $checkDigit;
    }

    public static function bnbAccount($agency, $account)
    {
        $account = sprintf('%03s%09s', self::bnbRealAgency($agency), $account);
        $checkDigit = Util::modulo11($account, 2, 9, 1);
        if ($checkDigit > 1) {
            return 11 - $checkDigit;
        }
        return 0;
    }

    public static function bnbOurNumber($ourNumber)
    {
        return Util::modulo11(Util::numberFormatGeral($ourNumber, 7));
    }

    private static function bnbRealAgency($agency)
    {
        $oldAgency = [
            '1' => '99', '2' => '44', '3' => '74', '4' => '73', '5' => '81', '6' => '1',
            '7' => '2', '8' => '53', '9' => '46', '10' => '20', '11' => '82', '12' => '47',
            '13' => '9', '14' => '10', '15' => '54', '16' => '0', '17' => '55', '18' => '83',
            '19' => '11', '20' => '48', '21' => '25', '22' => '12', '23' => '49', '24' => '26',
            '25' => '40', '26' => '75', '27' => '16', '28' => '50', '29' => '27', '30' => '29',
            '31' => '3', '32' => '4', '33' => '76', '34' => '41', '35' => '77', '36' => '30',
            '37' => '67', '38' => '68', '39' => '78', '40' => '58', '41' => '59', '42' => '42',
            '43' => '31', '44' => '60', '45' => '62', '46' => '17', '47' => '79', '48' => '32',
            '49' => '70', '50' => '63', '51' => '86', '52' => '33', '53' => '52', '54' => '64',
            '55' => '34', '56' => '71', '57' => '72', '58' => '14', '59' => '38', '60' => '43',
            '61' => '80', '62' => '21', '63' => '57', '64' => '91', '66' => '6', '67' => '51',
            '68' => '66', '69' => '85', '70' => '39', '71' => '92', '72' => '28', '73' => '19',
            '74' => '87', '75' => '18', '76' => '61', '77' => '88', '78' => '89', '80' => '5',
            '81' => '90', '82' => '15', '83' => '7', '84' => '13', '85' => '93', '86' => '69',
            '87' => '94', '88' => '101', '89' => '107', '90' => '95', '91' => '45', '92' => '8',
            '93' => '35', '95' => '106', '96' => '103', '97' => '117', '98' => '118', '99' => '104',
            '100' => '108', '101' => '102', '102' => '112', '103' => '113', '104' => '115', '105' => '105',
            '106' => '96', '107' => '97', '108' => '24', '109' => '111', '110' => '119', '111' => '84',
            '112' => '36', '113' => '37', '114' => '114', '115' => '100', '116' => '116', '117' => '56',
            '118' => '65', '119' => '109',
        ];
        return array_key_exists($agency, $oldAgency) ? $oldAgency[$agency] : $agency;
    }

    /*
    |--------------------------------------------------------------------------
    | 033 - Santander
    |--------------------------------------------------------------------------
    */
    public static function santanderAccount($agency, $account)
    {
        $n = Util::numberFormatGeral($agency, 4)
            . '00'
            . Util::numberFormatGeral($account, 8);
        $chars = array_reverse(str_split($n, 1));
        $sums = array_reverse(str_split('97310097131973', 1));
        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += substr($char*$sums[$i], -1);
        }
        $unity = substr($sum, -1);
        return $unity == 0 ? $unity : 10 - $unity;
    }

    public static function santanderOurNumber($ourNumber)
    {
        return Util::modulo11($ourNumber);
    }

    /*
    |--------------------------------------------------------------------------
    | 041 - Banrisul
    |--------------------------------------------------------------------------
    */
    public static function banrisulAgency($agency)
    {
        $newCheckDigit1 = $checkDigit1 = Util::modulo10($agency);
        $checkDigit2 = Util::modulo11($agency . $checkDigit1, 2, 7);

        if ($checkDigit2 == 1 && $checkDigit1 != 9) {
            $newCheckDigit1 = 1;
        }
        if ($checkDigit2 == 1 && $checkDigit1 == 9) {
            $newCheckDigit1 = 0;
        }

        if ($checkDigit1 != $newCheckDigit1) {
            $checkDigit1 = $newCheckDigit1;
            $checkDigit2 = Util::modulo11($agency . $checkDigit1, 2, 7);
        }

        return $checkDigit1 . $checkDigit2;
    }

    public static function banrisulAccount($account)
    {
        $chars = array_reverse(str_split($account, 1));
        $sums = str_split('234567423', 1);

        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += $char*$sums[$i];
        }

        $rest = $sum % 11;

        if ($rest == 0) {
            return $rest;
        }

        if ($rest == 1) {
            return 6;
        }

        return 11 - $rest;
    }

    public static function banrisulOurNumber($ourNumber)
    {
        return self::banrisulDoubleDigit($ourNumber);
    }

    public static function banrisulDoubleDigit($field)
    {
        $dv1 = Util::modulo10($field);
        $dv2 = Util::modulo11($field . $dv1, 2, 7, 0, 10);
        if ($dv2 == 10) {
            $dv1++;
            $dv2 = Util::modulo11($field . $dv1, 2, 7, 0, 10);
            if ($dv1 > 9) {
                $dv1 = 0;
            }
        }
        return $dv1 . $dv2;
    }

    /*
    |--------------------------------------------------------------------------
    | 104 - Caixa Econômica Federal
    |--------------------------------------------------------------------------
    */
    public static function cefAccount($agency, $account)
    {
        $n = Util::numberFormatGeral($agency, 4)
            . Util::numberFormatGeral($account, 11);
        return Util::modulo11($n);
    }

    public static function cefOurNumber($ourNumber)
    {
        return Util::modulo11($ourNumber);
    }

    /*
    |--------------------------------------------------------------------------
    | 237 - Bradesco
    |--------------------------------------------------------------------------
    */
    public static function bradescoAgency($agency)
    {
        $checkDigit = Util::modulo11($agency, 2, 9, 0, 'P');
        return $checkDigit == 11 ? 0 : $checkDigit;
    }

    public static function bradescoAccount($account)
    {
        return Util::modulo11($account, 2, 9, 0, 'P');
    }

    public static function bradescoOurNumber($wallet, $ourNumber)
    {
        return Util::modulo11($wallet . $ourNumber, 2, 7, 0, 'P');
    }

    /*
    |--------------------------------------------------------------------------
    | 341 - Itau
    |--------------------------------------------------------------------------
    */
    public static function itauAccount($agency, $account)
    {
        $n = Util::numberFormatGeral($agency, 4)
            . Util::numberFormatGeral($account, 5);
        return Util::modulo10($n);
    }

    public static function itauOurNumber($agency, $account, $wallet, $slipNumber)
    {
        $n = Util::numberFormatGeral($agency, 4)
            . Util::numberFormatGeral($account, 5)
            . Util::numberFormatGeral($wallet, 3)
            . Util::numberFormatGeral($slipNumber, 8);
        return Util::modulo10($n);
    }

    /*
    |--------------------------------------------------------------------------
    | 748 - Sicredi - Falta o calculo agencia e conta
    |--------------------------------------------------------------------------
    */
    public static function sicrediOurNumber($agency, $unity, $account, $year, $byte, $slipNumber)
    {
        $n = Util::numberFormatGeral($agency, 4)
            . Util::numberFormatGeral($unity, 2)
            . Util::numberFormatGeral($account, 5)
            . Util::numberFormatGeral($year, 2)
            . Util::numberFormatGeral($byte, 1)
            . Util::numberFormatGeral($slipNumber, 5);
        return  Util::modulo11($n);
    }

    /*
    |--------------------------------------------------------------------------
    | 756 - Bancoob - Falta o calculo conta e confirmar agencia
    |--------------------------------------------------------------------------
    */
    public static function bancoobAgency($agency)
    {
        return Util::modulo11($agency);
    }

    public static function bancoobOurNumber($agency, $covenant, $slipNumber)
    {
        $n = Util::numberFormatGeral($agency, 4)
            . Util::numberFormatGeral($covenant, 10)
            . Util::numberFormatGeral($slipNumber, 7);

        $chars = str_split($n, 1);
        $sums = str_split('3197319731973197319731973197', 1);
        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += $char*$sums[$i];
        }
        $rest = $sum % 11;
        $checkDigit = 0;

        if (($rest != 0) && ($rest != 1)) {
            $checkDigit = 11 - $rest;
        }
        return $checkDigit;
    }
}
