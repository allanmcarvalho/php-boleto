<?php
namespace PhpBoleto\Slip\Banco;

use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\CalculoDV;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Util;

/**
 * Class Bancoob
 * @package PhpBoleto\SlipInterface\Banco
 */
class Bancoob extends SlipAbstract implements BoletoContract
{
    /**
     * Bancoob constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredFiled('convenio');
    }

    /**
     * Código do banco
     * @var string
     */
    protected $bankCode = self::BANK_CODE_BANCOOB;

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $wallets = ['1','3'];

    /**
     * Espécie do documento, coódigo para remessa
     * @var string
     */
    protected $documentTypes = [
        'DM' => '01',
        'NP' => '02',
        'DS' => '12',
    ];

    /**
     * Define o número do convênio (4, 6 ou 7 caracteres)
     *
     * @var string
     */
    protected $convenio;

    /**
     * Define o número do convênio. Sempre use string pois a quantidade de caracteres é validada.
     *
     * @param  string $convenio
     * @return Bancoob
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }

    /**
     * Retorna o número do convênio
     *
     * @return string
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * Gera o Nosso Número.
     *
     * @throws \Exception
     * @return string
     */
    protected function generateOurNumber()
    {
        return $this->getNumber()
            . CalculoDV::bancoobNossoNumero($this->getAgency(), $this->getConvenio(), $this->getNumber());
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return substr_replace($this->getOurNumber(), '-', -1, 0);
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

        $nossoNumero = $this->getOurNumber();

        $campoLivre = Util::numberFormatGeral($this->getWallet(), 1);
        $campoLivre .= Util::numberFormatGeral($this->getAgency(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getWallet(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getConvenio(), 7);
        $campoLivre .= Util::numberFormatGeral($nossoNumero, 8);
        $campoLivre .= Util::numberFormatGeral($this->getParcela(), 3); //Numero da parcela - Não implementado

        return $this->fieldFree = $campoLivre;
    }

    /**
     * Método para gerar a Agencia e o Codigo do Beneficiario
     *
     * @return string
     * @throws \Exception
     */
    public function getAgencyAndAccount()
    {
        $agencia = $this->getAgency();
        $conta = $this->getCodigoCliente();

        return $agencia . ' / ' . $conta;
    }

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
     * Parcela do boleto.
     *
     * @var string
     */
    protected $parcela;

    /**
     * Seta a Parcela.
     *
     * @param mixed $parcela
     *
     * @return $this
     */
    public function setParcela($parcela)
    {
        $this->parcela = $parcela;

        return $this;
    }

    /**
     * Retorna a Parcela.
     *
     * @return string
     */
    public function getParcela()
    {
        return $this->parcela;
    }

}
