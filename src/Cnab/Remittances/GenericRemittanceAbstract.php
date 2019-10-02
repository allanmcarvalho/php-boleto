<?php

namespace PhpBoleto\Cnab\Remittances;

use Exception;
use PhpBoleto\Interfaces\Person\PersonInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Support\Collection;
use PhpBoleto\Tools\Util;

/**
 * Class GenericRemittanceAbstract
 * @package PhpBoleto\CnabInterface\Remessa
 */
abstract class GenericRemittanceAbstract
{

    const HEADER = 'header';
    const HEADER_LOTE = 'header_lote';
    const DETALHE = 'detalhe';
    const TRAILER_LOTE = 'trailer_lote';
    const TRAILER = 'trailer';

    protected $lineSize = false;

    /**
     * Campos que são necessários para a remessa
     *
     * @var array
     */
    private $requiredFields = [
        'wallet',
        'agency',
        'account',
        'beneficiary',
    ];

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode;

    /**
     * Contagem dos registros Detalhes
     *
     * @var int
     */
    protected $registryCount = 0;

    /**
     * Array contendo o cnab.
     *
     * @var array
     */
    protected $registryArray = [
        self::HEADER => [],
        self::DETALHE => [],
        self::TRAILER => [],
    ];

    /**
     * Variável com ponteiro para linha que esta sendo editada.
     *
     * @var
     */
    protected $linePointer;

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $eolChar = "\n";

    /**
     * Caracter de fim de arquivo
     *
     * @var null
     */
    protected $endOfFileChar = null;

    /**
     * ID do arquivo remessa, sequencial.
     *
     * @var
     */
    protected $remittanceId;

    /**
     * Agência
     *
     * @var int
     */
    protected $agency;

    /**
     * Conta
     *
     * @var int
     */
    protected $account;

    /**
     * Dígito da conta
     *
     * @var int
     */
    protected $accountCheckDigit;

    /**
     * Carteira de cobrança.
     *
     * @var
     */
    protected $wallet;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $wallets = [];

    /**
     * Entidade beneficiário (quem esta gerando a remessa)
     *
     * @var PersonInterface
     */
    protected $beneficiary;

    /**
     * Construtor
     *
     * @param array $params Parâmetros iniciais para construção do objeto
     */
    public function __construct($params = [])
    {
        Util::fillClass($this, $params);
    }

    /**
     * Seta os campos obrigatórios
     *
     * @return $this
     */
    protected function setRequiredFields()
    {
        $args = func_get_args();
        $this->requiredFields = [];
        foreach ($args as $arg) {
            $this->addRequiredField($arg);
        }

        return $this;
    }

    /**
     * Adiciona os campos obrigatórios
     *
     * @return $this
     */
    protected function addRequiredField()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            !is_array($arg) || call_user_func_array([$this, __FUNCTION__], $arg);
            !is_string($arg) || array_push($this->requiredFields, $arg);
        }

        return $this;
    }

    /**
     * Retorna o código do banco
     *
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * @return mixed
     */
    public function getRemittanceId()
    {
        return $this->remittanceId;
    }

    /**
     * @param mixed $remittanceId
     *
     * @return GenericRemittanceAbstract
     */
    public function setRemittanceId($remittanceId)
    {
        $this->remittanceId = $remittanceId;

        return $this;
    }

    /**
     * @return PersonInterface
     */
    public function getBeneficiary()
    {
        return $this->beneficiary;
    }

    /**
     * @param $beneficiary
     *
     * @return GenericRemittanceAbstract
     * @throws Exception
     */
    public function setBeneficiary($beneficiary)
    {
        Util::addPerson($this->beneficiary, $beneficiary);

        return $this;
    }

    /**
     * Define a agência
     *
     * @param $agency
     * @return $this
     */
    public function setAgency($agency)
    {
        $this->agency = (string)$agency;

        return $this;
    }

    /**
     * Retorna a agência
     *
     * @return int
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Define o número da conta
     *
     * @param $account
     * @return $this
     */
    public function setAccount($account)
    {
        $this->account = (string)$account;

        return $this;
    }

    /**
     * Retorna o número da conta
     *
     * @return int
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Define o dígito verificador da conta
     *
     * @param $accountCheckDigit
     * @return $this
     */
    public function setAccountCheckDigit($accountCheckDigit)
    {
        $this->accountCheckDigit = substr($accountCheckDigit, -1);

        return $this;
    }

    /**
     * Retorna o dígito verificador da conta
     *
     * @return int
     */
    public function getAccountCheckDigit()
    {
        return $this->accountCheckDigit;
    }

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param $wallet
     * @return $this
     * @throws Exception
     */
    public function setWallet($wallet)
    {
        if (!in_array($wallet, $this->getWallets())) {
            throw new Exception("Carteira não disponível!");
        }
        $this->wallet = $wallet;

        return $this;
    }

    /**
     * Retorna o código da carteira (Com ou sem registro)
     *
     * @return string
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * Retorna o código da carteira (Com ou sem registro)
     *
     * @return string
     */
    public function getWalletNumber()
    {
        return $this->wallet;
    }

    /**
     * Retorna as carteiras disponíveis para este banco
     *
     * @return array
     */
    public function getWallets()
    {
        return $this->wallets;
    }

    /**
     * Método que valida se o banco tem todos os campos obrigatórios preenchidos
     *
     * @return boolean
     */
    public function isValid()
    {
        foreach ($this->requiredFields as $campo) {
            if (call_user_func([$this, 'get' . ucwords($campo)]) == '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Função para gerar o cabeçalho do arquivo.
     *
     * @return mixed
     */
    abstract protected function header();

    /**
     * Função para adicionar detalhe ao arquivo.
     *
     * @param SlipInterface $slip
     * @return mixed
     */
    abstract public function addSlip(SlipInterface $slip);

    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return mixed
     */
    abstract protected function trailer();

    /**
     * Função que mostra a quantidade de linhas do arquivo.
     *
     * @return int
     */
    protected function getCount()
    {
        return count($this->registryArray[self::DETALHE]) + 2;
    }

    /**
     * Função para adicionar múltiplos boletos.
     *
     * @param SlipInterface[] $slips
     * @return $this
     */
    public function addSlips(array $slips)
    {
        foreach ($slips as $slip) {
            $this->addSlip($slip);
        }

        return $this;
    }

    /**
     * Função para add valor a linha nas posições informadas.
     *
     * @param $i
     * @param $f
     * @param $value
     * @return array
     * @throws Exception
     */
    protected function add($i, $f, $value)
    {
        return Util::addLine($this->linePointer, $i, $f, $value);
    }

    /**
     * Retorna o header do arquivo.
     *
     * @return mixed
     */
    protected function getHeader()
    {
        return $this->registryArray[self::HEADER];
    }

    /**
     * Retorna os detalhes do arquivo
     *
     * @return Collection
     */
    protected function getDetails()
    {
        return new Collection($this->registryArray[self::DETALHE]);
    }

    /**
     * Retorna o trailer do arquivo.
     *
     * @return mixed
     */
    protected function getTrailer()
    {
        return $this->registryArray[self::TRAILER];
    }

    /**
     * Valida se a linha esta correta.
     *
     * @param array $a
     * @return string
     * @throws Exception
     */
    protected function validate(array $a)
    {
        if ($this->lineSize === false) {
            throw new Exception('Classe remessa deve informar o tamanho da linha');
        }

        $a = array_filter($a, 'strlen');
        if (count($a) != $this->lineSize) {
            throw new Exception(sprintf('$a não possui %s posições, possui: %s', $this->lineSize, count($a)));
        }

        return implode('', $a);
    }

    /**
     * Gera o arquivo, retorna a string.
     *
     * @throws Exception
     */
    public function generate()
    {
        throw new Exception('Método não implementado');
    }

    /**
     * Salva o arquivo no path informado
     *
     * @param $path
     * @return mixed
     * @throws Exception
     */
    public function save($path)
    {
        $folder = dirname($path);
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (!is_writable(dirname($path))) {
            throw new Exception('Path ' . $folder . ' não possui permissao de escrita');
        }

        $string = $this->generate();
        file_put_contents($path, $string);

        return $path;
    }

    /**
     * Realiza o download da string retornada do metodo gerar
     *
     * @param null $filename
     * @throws Exception
     */
    public function download($filename = null)
    {
        if ($filename === null) {
            $filename = 'remessa.txt';
        }
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $this->generate();
    }
}
