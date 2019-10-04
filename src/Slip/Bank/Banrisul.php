<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Banrisul
 * @package PhpBoleto\SlipInterface\Banks
 */
class Banrisul extends SlipAbstract implements SlipInterface
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_BANRISUL;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 -> Cobrança Simples
     * 3 -> Cobrança Caucionada
     * 4 -> Cobrança em IGPM
     * 5 -> Cobrança Caucionada CGB Especial
     * 6 -> Cobrança Simples Seguradora
     * 7 -> Cobrança em UFIR
     * 8 -> Cobrança em IDTR
     * C -> Cobrança Vinculada
     * D -> Cobrança CSB
     * E -> Cobrança Caucionada Câmbio
     * F -> Cobrança Vendor
     * H -> Cobrança Caucionada Dólar
     * I -> Cobrança Caucionada Compror
     * K -> Cobrança Simples INCC-M
     * M -> Cobrança Partilhada
     * N -> Capital de Giro CGB ICM
     * R -> Desconto de Duplicata
     * S -> Vendor Eletrônico – Valor Final (Corrigido)
     * X -> Vendor BDL – Valor Inicial (Valor da NF)
     *
     * @var array
     */
    protected $wallets = ['1', '2', '3', '4', '5', '6', '7', '8', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'M', 'N', 'R', 'S', 'X'];

    /**
     * Seta dias para baixa automática
     *
     * @param int $automaticDrop
     *
     * @return $this
     * @throws Exception
     */
    public function setAutomaticDropAfter(int $automaticDrop)
    {
        if ($this->getProtestAfter() > 0) {
            throw new Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $automaticDrop = (int)$automaticDrop;
        $this->automaticDropAfter = $automaticDrop > 0 ? $automaticDrop : 0;
        return $this;
    }

    /**
     * Gerar nosso número
     *
     * @return string
     */
    protected function generateOurNumber()
    {
        $numero_boleto = $this->getNumber();
        $nossoNumero = Util::numberFormatGeral($numero_boleto, 8)
            . CheckDigitCalculation::banrisulOurNumber(Util::numberFormatGeral($numero_boleto, 8));
        return $nossoNumero;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return substr_replace($this->getOurNumber(), '-', -2, 0);
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws Exception
     */
    protected function getFieldFree()
    {
        if ($this->fieldFree) {
            return $this->fieldFree;
        }

        // Carteira     => 20 - 20 | Valor: 1(Com registro) ou 2(Sem registro)
        $this->fieldFree = '2';

        // Constante    => 21 - 21 | Valor: 1(Constante)
        $this->fieldFree .= '1';

        // Agencia      => 22 a 25 | Valor: dinâmico(0000) ´4´
        $this->fieldFree .= Util::numberFormatGeral($this->getAgency(), 4);

        // Cod. Cedente => 26 a 32 | Valor: dinâmico(0000000) ´7´
        $this->fieldFree .= $this->getAccount();

        // Nosso numero => 33 a 40 | Valor: dinâmico(00000000) ´8´
        $this->fieldFree .= Util::numberFormatGeral($this->getNumber(), 8);

        // Constante    => 41 - 42 | Valor: 40(Constante)
        $this->fieldFree .= '40';

        // Duplo digito => 43 - 44 | Valor: calculado(00) ´2´
        $this->fieldFree .= CheckDigitCalculation::banrisulDoubleDigit(Util::onlyNumbers($this->fieldFree));
        return $this->fieldFree;
    }
}
