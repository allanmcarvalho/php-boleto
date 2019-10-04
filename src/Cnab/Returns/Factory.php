<?php

namespace PhpBoleto\Cnab\Returns;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Util;

class Factory
{
    /**
     * @param $file
     *
     * @return Retorno
     * @throws Exception
     */
    public static function make($file)
    {
        if (!$file_content = Util::file2array($file)) {
            throw new Exception("Archive: não existe");
        }

        if (!Util::isHeaderRetorno($file_content[0])) {
            throw new Exception("Archive: $file, não é um arquivo de retorno");
        }

        $instancia = self::getBancoClass($file_content);
        return $instancia->processar();
    }

    /**
     * @param $file_content
     *
     * @return mixed
     * @throws Exception
     */
    private static function getBancoClass($file_content)
    {
        $banco = '';
        $namespace = '';
        if (Util::isCnab400($file_content)) {
            $banco = substr($file_content[0], 76, 3);
            $namespace = __NAMESPACE__ . '\\Cnab400\\';
        } elseif (Util::isCnab240($file_content)) {
            $banco = substr($file_content[0], 0, 3);
            $namespace = __NAMESPACE__ . '\\Cnab240\\';
        }

        $aBancos = [
            BoletoContract::BANK_CODE_BB => 'Banks\\Bb',
            BoletoContract::BANK_CODE_SANTANDER => 'Banks\\Santander',
            BoletoContract::BANK_CODE_CEF => 'Banks\\Caixa',
            BoletoContract::BANK_CODE_BRADESCO => 'Banks\\Bradesco',
            BoletoContract::BANK_CODE_ITAU => 'Banks\\Itau',
            BoletoContract::BANK_CODE_HSBC => 'Banks\\Hsbc',
            BoletoContract::BANK_CODE_SICREDI => 'Banks\\Sicredi',
            BoletoContract::BANK_CODE_BANRISUL => 'Banks\\Banrisul',
            BoletoContract::BANK_CODE_BANCOOB => 'Banks\\Bancoob',
            BoletoContract::BANK_CODE_BNB => 'Banks\\Bnb',
        ];

        if (array_key_exists($banco, $aBancos)) {
            $bancoClass = $namespace . $aBancos[$banco];
            return new $bancoClass($file_content);
        }

        throw new Exception("Banks: $banco, inválido");
    }
}
