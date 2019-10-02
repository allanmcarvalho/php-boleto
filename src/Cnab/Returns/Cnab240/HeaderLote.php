<?php

namespace PhpBoleto\Cnab\Returns\Cnab240;

use Carbon\Carbon;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\HeaderLote as HeaderLoteContract;
use PhpBoleto\Traits\MagicTrait;

class HeaderLote implements HeaderLoteContract
{
    use MagicTrait;
    /**
     * @var string
     */
    protected $codBanco;

    /**
     * @var string
     */
    protected $numeroLoteRetorno;

    /**
     * @var string
     */
    protected $tipoRegistro;

    /**
     * @var string
     */
    protected $tipoOperacao;

    /**
     * @var string
     */
    protected $tipoServico;

    /**
     * @var string
     */
    protected $versaoLayoutLote;

    /**
     * @var string
     */
    protected $tipoInscricao;

    /**
     * @var string
     */
    protected $numeroInscricao;

    /**
     * @var string
     */
    protected $agenciaDv;

    /**
     * @var string
     */
    protected $codigoCedente;

    /**
     * @var string
     */
    protected $nomeEmpresa;

    /**
     * @var string
     */
    protected $numeroRetorno;

    /**
     * @var Carbon
     */
    protected $dataGravacao;

    /**
     * @var string
     */
    protected $agencia;

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
    protected $codigoBanco;

    /**
     * @var string
     */
    protected $mensagem_1;

    /**
     * @var Carbon
     */
    protected $dataCredito;

    /**
     * @var string
     */
    protected $convenio;

    /**
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * @param string $codigoBanco
     *
     * @return $this
     */
    public function setCodigoBanco($codigoBanco)
    {
        $this->codigoBanco = $codigoBanco;

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
    public function getCodBanco()
    {
        return $this->codBanco;
    }

    /**
     * @param string $codBanco
     *
     * @return $this
     */
    public function setCodBanco($codBanco)
    {
        $this->codBanco = $codBanco;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroLoteRetorno()
    {
        return $this->numeroLoteRetorno;
    }

    /**
     * @param string $numeroLoteRetorno
     *
     * @return $this
     */
    public function setNumeroLoteRetorno($numeroLoteRetorno)
    {
        $this->numeroLoteRetorno = $numeroLoteRetorno;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperationType()
    {
        return $this->tipoOperacao;
    }

    /**
     *
     * @param string $tipoOperacao
     * @return $this
     */
    public function setTipoOperacao($tipoOperacao)
    {
        $this->tipoOperacao = $tipoOperacao;

        return $this;
    }


    /**
     * @return string
     */
    public function getServiceType()
    {
        return $this->tipoServico;
    }

    /**
     * @param string $tipoServico
     *
     * @return $this
     */
    public function setTipoServico($tipoServico)
    {
        $this->tipoServico = $tipoServico;

        return $this;
    }


    /**
     * @return string
     */
    public function getVersaoLayoutLote()
    {
        return $this->versaoLayoutLote;
    }

    /**
     * @param string $versaoLayoutLote
     *
     * @return $this
     */
    public function setVersaoLayoutLote($versaoLayoutLote)
    {
        $this->versaoLayoutLote = $versaoLayoutLote;

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
     *
     * @param $tipoInscricao
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
    public function getMensagem1()
    {
        return $this->mensagem_1;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getWritingDate($format = 'd/m/Y')
    {
        return $this->dataGravacao instanceof Carbon
            ? $format === false ? $this->dataGravacao : $this->dataGravacao->format($format)
            : null;
    }

    /**
     * @param string $dataGravacao
     *
     * @param string $format
     *
     * @return $this
     */
    public function setDataGravacao($dataGravacao, $format = 'dmY')
    {
        $this->dataGravacao = trim($dataGravacao, '0 ') ? Carbon::createFromFormat($format, $dataGravacao) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getCreditDate($format = 'd/m/Y')
    {
        return $this->dataCredito instanceof Carbon
            ? $format === false ? $this->dataCredito : $this->dataCredito->format($format)
            : null;
    }

    /**
     * @param string $dataCredito
     *
     * @param string $format
     *
     * @return $this
     */
    public function setDataCredito($dataCredito, $format = 'dmY')
    {
        $this->dataCredito = trim($dataCredito, '0 ') ? Carbon::createFromFormat($format, $dataCredito) : null;

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
        $this->agencia = $agencia;

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
        $this->conta = $conta;

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnNumber()
    {
        return $this->numeroRetorno;
    }

    /**
     * @param string $numeroRetorno
     *
     * @return $this
     */
    public function setNumeroRetorno($numeroRetorno)
    {
        $this->numeroRetorno = $numeroRetorno;

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
}
