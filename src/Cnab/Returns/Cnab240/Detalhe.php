<?php

namespace PhpBoleto\Cnab\Returns\Cnab240;

use Carbon\Carbon;
use Exception;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\DetailInterface as DetalheContract;
use PhpBoleto\Interfaces\PersonInterface as PessoaContract;
use PhpBoleto\Traits\MagicTrait;
use PhpBoleto\Util;

class Detalhe implements DetalheContract
{
    use MagicTrait;

    /**
     * @var string
     */
    protected $ocorrencia;

    /**
     * @var string
     */
    protected $ocorrenciaTipo;

    /**
     * @var string
     */
    protected $ocorrenciaDescricao;

    /**
     * @var int
     */
    protected $numeroControle;

    /**
     * @var string
     */
    protected $numeroDocumento;

    /**
     * @var string
     */
    protected $nossoNumero;

    /**
     * @var string
     */
    protected $carteira;

    /**
     * @var Carbon
     */
    protected $dataVencimento;

    /**
     * @var Carbon
     */
    protected $dataOcorrencia;
    /**
     * @var Carbon
     */
    protected $dataCredito;

    /**
     * @var string
     */
    protected $valor;

    /**
     * @var string
     */
    protected $valorRecebido;

    /**
     * @var string
     */
    protected $valorTarifa;

    /**
     * @var string
     */
    protected $valorIOF;
    /**
     * @var string
     */
    protected $valorAbatimento;
    /**
     * @var string
     */
    protected $valorDesconto;
    /**
     * @var string
     */
    protected $valorMora;
    /**
     * @var string
     */
    protected $valorMulta;

    /**
     * @var PessoaContract
     */
    protected $pagador;

    /**
     * @var array
     */
    protected $cheques = [];

    /**
     * @var string
     */
    protected $error;

    /**
     * @return string
     */
    public function getOccurrence()
    {
        return $this->ocorrencia;
    }

    /**
     * @return boolean
     */
    public function hasOccurrence()
    {
        $ocorrencias = func_get_args();

        if (count($ocorrencias) == 0 && !empty($this->getOccurrence())) {
            return true;
        }

        if (count($ocorrencias) == 1 && is_array(func_get_arg(0))) {
            $ocorrencias = func_get_arg(0);
        }

        if (in_array($this->getOccurrence(), $ocorrencias)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $ocorrencia
     *
     * @return $this
     */
    public function setOcorrencia($ocorrencia)
    {
        $this->ocorrencia = $ocorrencia;

        return $this;
    }

    /**
     * @return string
     */
    public function getOccurrenceType()
    {
        return $this->ocorrenciaTipo;
    }

    /**
     * @param string $ocorrenciaTipo
     *
     * @return $this
     */
    public function setOcorrenciaTipo($ocorrenciaTipo)
    {
        $this->ocorrenciaTipo = $ocorrenciaTipo;

        return $this;
    }

    /**
     * @return string
     */
    public function getOccurrenceDescription()
    {
        return $this->ocorrenciaDescricao;
    }

    /**
     * @param string $ocorrenciaDescricao
     *
     * @return $this
     */
    public function setOcorrenciaDescricao($ocorrenciaDescricao)
    {
        $this->ocorrenciaDescricao = $ocorrenciaDescricao;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumeroControle()
    {
        return $this->numeroControle;
    }

    /**
     * @param int $numeroControle
     *
     * @return $this
     */
    public function setNumeroControle($numeroControle)
    {
        $this->numeroControle = $numeroControle;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentNumber()
    {
        return $this->numeroDocumento;
    }

    /**
     * @param string $numeroDocumento
     *
     * @return $this
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;

        return $this;
    }

    /**
     * @return string
     */
    public function getOurNumber()
    {
        return $this->nossoNumero;
    }

    /**
     * @param string $nossoNumero
     *
     * @return $this
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;

        return $this;
    }

    /**
     * @return string
     */
    public function getCarteira()
    {
        return $this->carteira;
    }

    /**
     * @param string $carteira
     *
     * @return $this
     */
    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return Carbon|null|string
     */
    public function getDueDate($format = 'd/m/Y')
    {
        return $this->dataVencimento instanceof Carbon
            ? $format === false ? $this->dataVencimento : $this->dataVencimento->format($format)
            : null;
    }

    /**
     * @param $dataVencimento
     *
     * @param string $format
     *
     * @return $this
     */
    public function setDataVencimento($dataVencimento, $format = 'dmY')
    {
        $this->dataVencimento = trim($dataVencimento, '0 ') ? Carbon::createFromFormat($format, $dataVencimento) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return Carbon|null|string
     */
    public function getCreditDate($format = 'd/m/Y')
    {
        return $this->dataCredito instanceof Carbon
            ? $format === false ? $this->dataCredito : $this->dataCredito->format($format)
            : null;
    }

    /**
     * @param $dataCredito
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
     * @param string $format
     *
     * @return Carbon|null|string
     */
    public function getOccurrenceDate($format = 'd/m/Y')
    {
        return $this->dataOcorrencia instanceof Carbon
            ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
            : null;
    }

    /**
     * @param $dataOcorrencia
     *
     * @param string $format
     *
     * @return $this
     */
    public function setDataOcorrencia($dataOcorrencia, $format = 'dmY')
    {
        $this->dataOcorrencia = trim($dataOcorrencia, '0 ') ? Carbon::createFromFormat($format, $dataOcorrencia) : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->valor;
    }

    /**
     * @param string $valor
     *
     * @return $this
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * @return string
     */
    public function getIOFValue()
    {
        return $this->valorIOF;
    }

    /**
     * @param string $valorIOF
     *
     * @return $this
     */
    public function setValorIOF($valorIOF)
    {
        $this->valorIOF = $valorIOF;

        return $this;
    }

    /**
     * @return string
     */
    public function getAbatementValue()
    {
        return $this->valorAbatimento;
    }

    /**
     * @param string $valorAbatimento
     *
     * @return $this
     */
    public function setValorAbatimento($valorAbatimento)
    {
        $this->valorAbatimento = $valorAbatimento;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountValue()
    {
        return $this->valorDesconto;
    }

    /**
     * @param string $valorDesconto
     *
     * @return $this
     */
    public function setValorDesconto($valorDesconto)
    {
        $this->valorDesconto = $valorDesconto;

        return $this;
    }

    /**
     * @return string
     */
    public function getInterestValue()
    {
        return $this->valorMora;
    }

    /**
     * @param string $valorMora
     *
     * @return $this
     */
    public function setValorMora($valorMora)
    {
        $this->valorMora = $valorMora;

        return $this;
    }

    /**
     * @return string
     */
    public function getFineValue()
    {
        return $this->valorMulta;
    }

    /**
     * @param string $valorMulta
     *
     * @return $this
     */
    public function setValorMulta($valorMulta)
    {
        $this->valorMulta = $valorMulta;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceivedValue()
    {
        return $this->valorRecebido;
    }

    /**
     * @param string $valorRecebido
     *
     * @return $this
     */
    public function setValorRecebido($valorRecebido)
    {
        $this->valorRecebido = $valorRecebido;

        return $this;
    }

    /**
     * @return string
     */
    public function getFareValue()
    {
        return $this->valorTarifa;
    }

    /**
     * @param string $valorTarifa
     *
     * @return $this
     */
    public function setValorTarifa($valorTarifa)
    {
        $this->valorTarifa = $valorTarifa;

        return $this;
    }

    /**
     * @return PessoaContract
     */
    public function getPagador()
    {
        return $this->pagador;
    }

    /**
     * @param $pagador
     *
     * @return $this
     * @throws Exception
     */
    public function setPagador($pagador)
    {
        Util::addPessoa($this->pagador, $pagador);
        return $this;
    }

    /**
     * Retorna se tem erro.
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->getOccurrence() == self::OCORRENCIA_ERRO;
    }

    /**
     * @return array
     */
    public function getCheques()
    {
        return $this->cheques;
    }

    /**
     * @param array $cheques
     *
     * @return Detalhe
     */
    public function setCheques(array $cheques)
    {
        $this->cheques = $cheques;

        return $this;
    }


    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->ocorrenciaTipo = self::OCORRENCIA_ERRO;
        $this->error = $error;

        return $this;
    }
}
