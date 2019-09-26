<?php

namespace PhpBoleto\Slip\Banco;

use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\CalculoDV;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Util;

/**
 * Class Caixa
 * @package PhpBoleto\SlipInterface\Banco
 */
class Caixa extends SlipAbstract implements BoletoContract
{
    /**
     * Caixa constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setRequiredFields('numero', 'agencia', 'carteira', 'codigoCliente');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_CEF;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $wallets = ['RG', 'SR'];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $documentTypes = [
        'DM' => '01',
        'NP' => '02',
        'DS' => '03',
        'NS' => '05',
        'LC' => '06',
    ];

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return $this
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * Retorna o codigo do cliente.
     *
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Retorna o codigo do cliente como se fosse a conta
     * ja que a caixa não faz uso da conta para nada.
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->getCodigoCliente();
    }

    /**
     * Gera o Nosso Número.
     *
     * @throws \Exception
     * @return string
     */
    protected function generateOurNumber()
    {
        $numero_boleto = $this->getNumber();
        $composicao = '1';
        if ($this->getWallet() == 'SR') {
            $composicao = '2';
        }

        $carteira = $composicao . '4';
        // As 15 próximas posições no nosso número são a critério do beneficiário, utilizando o sequencial
        // Depois, calcula-se o código verificador por módulo 11
        $numero = $carteira . Util::numberFormatGeral($numero_boleto, 15);
        return $numero;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return $this->getOurNumber() . '-' . CalculoDV::cefNossoNumero($this->getOurNumber());
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $automaticDrop
     *
     * @return $this
     * @throws \Exception
     */
    public function setAutomaticDropAfter($automaticDrop)
    {
        if ($this->getProtestAfter() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $automaticDrop = (int)$automaticDrop;
        $this->automaticDropAfter = $automaticDrop > 0 ? $automaticDrop : 0;
        return $this;
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws \Exception
     */
    protected function getFieldFree()
    {
        if ($this->fieldFree) {
            return $this->fieldFree;
        }
        $nossoNumero = Util::numberFormatGeral($this->generateOurNumber(), 17);
        $beneficiario = Util::numberFormatGeral($this->getCodigoCliente(), 6);
        // Código do beneficiário + DV]
        $campoLivre = $beneficiario . Util::modulo11($beneficiario);
        // Sequencia 1 (posições 3-5 NN) + Constante 1 (1 => registrada, 2 => sem registro)
        $carteira = $this->getWallet();
        if ($carteira == 'SR') {
            $constante = '2';
        } else {
            $constante = '1';
        }
        $campoLivre .= substr($nossoNumero, 2, 3) . $constante;
        // Sequencia 2 (posições 6-8 NN) + Constante 2 (4-Beneficiário)
        $campoLivre .= substr($nossoNumero, 5, 3) . '4';
        // Sequencia 3 (posições 9-17 NN)
        $campoLivre .= substr($nossoNumero, 8, 9);
        // DV do Campo Livre
        $campoLivre .= Util::modulo11($campoLivre);
        return $this->fieldFree = $campoLivre;
    }
}
