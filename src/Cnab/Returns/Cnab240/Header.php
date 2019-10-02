<?php

namespace PhpBoleto\Cnab\Returns\Cnab240;

use Carbon\Carbon;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\Header as HeaderContract;
use PhpBoleto\Traits\MagicTrait;

class Header implements HeaderContract
{
    use MagicTrait;
    /**
     * @var integer
     */
    protected $codBanco;

    /**
     * @var string
     */
    protected $nomeBanco;

    /**
     * @var integer
     */
    protected $codigoRemessaRetorno;

    /**
     * @var string
     */
    protected $loteServico;

    /**
     * @var string
     */
    protected $tipoRegistro;

    /**
     * @var Carbon
     */
    protected $data;

    /**
     * @var string
     */
    protected $tipoInscricao;

    /**
     * @var string
     */
    protected $agencia;
    /**
     * @var string
     */
    protected $agenciaDv;

    /**
     * @var string
     */
    protected $nomeEmpresa;

    /**
     * @var string
     */
    protected $numeroSequencialArquivo;

    /**
     * @var string
     */
    protected $versaoLayoutArquivo;

    /**
     * @var string
     */
    protected $numeroInscricao;

    /**
     * @var string
     */
    protected $conta;

    /**
     * @var string
     */
    protected $contaDv;

    /**
     * @var string
     */
    protected $codigoCedente;

    /**
     * @var string
     */
    protected $horaGeracao;

    /**
     * @var string
     */
    protected $convenio;

    /**
     * @return string
     */
    public function getServiceLot()
    {
        return $this->loteServico;
    }

    /**
     * @param string $loteServico
     *
     * @return $this
     */
    public function setLoteServico($loteServico)
    {
        $this->loteServico = $loteServico;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegistryType()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param string $tipoRegistro
     *
     * @return $this
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubscriptionType()
    {
        return $this->tipoInscricao;
    }

    /**
     * @param string $tipoInscricao
     *
     * @return $this
     */
    public function setTipoInscricao($tipoInscricao)
    {
        $this->tipoInscricao = $tipoInscricao;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgency()
    {
        return $this->agencia;
    }

    /**
     * @param string $agencia
     *
     * @return $this
     */
    public function setAgencia($agencia)
    {
        $this->agencia = ltrim(trim($agencia, ' '), '0');

        return $this;
    }

    /**
     * @return string
     */
    public function getAgencyCheckDigit()
    {
        return $this->agenciaDv;
    }

    /**
     * @param string $agenciaDv
     *
     * @return $this
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = $agenciaDv;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanySocialName()
    {
        return $this->nomeEmpresa;
    }

    /**
     * @param string $nomeEmpresa
     *
     * @return $this
     */
    public function setNomeEmpresa($nomeEmpresa)
    {
        $this->nomeEmpresa = $nomeEmpresa;

        return $this;
    }

    /**
     * @return string
     */
    public function getGenerationHour()
    {
        return $this->horaGeracao;
    }

    /**
     * @param string $horaGeracao
     *
     * @return $this
     */
    public function setHoraGeracao($horaGeracao)
    {
        $this->horaGeracao = $horaGeracao;

        return $this;
    }

    /**
     * @return string
     */
    public function getSequentialFileNumber()
    {
        return $this->numeroSequencialArquivo;
    }

    /**
     *
     * @param string $numeroSequencialArquivo
     * @return $this
     */
    public function setNumeroSequencialArquivo($numeroSequencialArquivo)
    {
        $this->numeroSequencialArquivo = $numeroSequencialArquivo;

        return $this;
    }

    /**
     * @return string
     */
    public function getLayoutFileVersion()
    {
        return $this->versaoLayoutArquivo;
    }

    /**
     * @param string $versaoLayoutArquivo
     *
     * @return $this
     */
    public function setVersaoLayoutArquivo($versaoLayoutArquivo)
    {
        $this->versaoLayoutArquivo = $versaoLayoutArquivo;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubscriptionNumber()
    {
        return $this->numeroInscricao;
    }

    /**
     * @param string $numeroInscricao
     *
     * @return $this
     */
    public function setNumeroInscricao($numeroInscricao)
    {
        $this->numeroInscricao = $numeroInscricao;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->conta;
    }

    /**
     * @param string $conta
     *
     * @return $this
     */
    public function setConta($conta)
    {
        $this->conta = ltrim(trim($conta, ' '), '0');

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountCheckDigit()
    {
        return $this->contaDv;
    }

    /**
     * @param string $contaDv
     *
     * @return $this
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = $contaDv;

        return $this;
    }

    /**
     * @return string
     */
    public function getAssignorCode()
    {
        return $this->codigoCedente;
    }

    /**
     * @param string $codigoCedente
     *
     * @return $this
     */
    public function setCodigoCedente($codigoCedente)
    {
        $this->codigoCedente = $codigoCedente;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getDate($format = 'd/m/Y')
    {
        return $this->data instanceof Carbon
            ? $format === false ? $this->data : $this->data->format($format)
            : null;
    }

    /**
     * @param string $data
     *
     * @param string $format
     *
     * @return $this
     */
    public function setData($data, $format = 'dmY')
    {
        $this->data = trim($data, '0 ') ? Carbon::createFromFormat($format, $data) : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getCovenant()
    {
        return $this->convenio;
    }

    /**
     * @param string $convenio
     *
     * @return $this
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;

        return $this;
    }

    /**
     * @return int
     */
    public function getBankCode()
    {
        return $this->codBanco;
    }

    /**
     * @param int $codBanco
     *
     * @return $this
     */
    public function setCodBanco($codBanco)
    {
        $this->codBanco = $codBanco;

        return $this;
    }

    /**
     * @return int
     */
    public function getRemittanceReturnCode()
    {
        return $this->codigoRemessaRetorno;
    }

    /**
     * @param int $codigoRemessaRetorno
     *
     * @return $this
     */
    public function setCodigoRemessaRetorno($codigoRemessaRetorno)
    {
        $this->codigoRemessaRetorno = $codigoRemessaRetorno;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->nomeBanco;
    }

    /**
     * @param string $nomeBanco
     *
     * @return $this
     */
    public function setNomeBanco($nomeBanco)
    {
        $this->nomeBanco = $nomeBanco;

        return $this;
    }
}
