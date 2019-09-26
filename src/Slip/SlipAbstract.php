<?php

namespace PhpBoleto\Slip;

use DateTime;
use DateTimeInterface;
use Exception;
use PhpBoleto\Persons\Person;
use PhpBoleto\Slip\Render\Html;
use PhpBoleto\Slip\Render\Pdf;
use PhpBoleto\Interfaces\Person\PersonInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\Util;

/**
 * Class SlipAbstract
 *
 * @package PhpBoleto\SlipInterface
 */
abstract class SlipAbstract implements SlipInterface
{
    /**
     * Campos que são necessários para o boleto
     *
     * @var array
     */
    private $requiredFields = [
        'number',
        'agency',
        'account',
        'wallet',
    ];

    protected $protectedFields = [
        'outNumber',
    ];

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode;

    /**
     * Moeda
     *
     * @var int
     */
    protected $currency = 9;

    /**
     * Valor total do boleto
     *
     * @var float
     */
    protected $value;

    /**
     * Desconto total do boleto
     *
     * @var float
     */
    protected $discount;

    /**
     * Valor para multa
     *
     * @var float
     */
    protected $fine = 0;

    /**
     * Valor para mora juros
     *
     * @var float
     */
    protected $interest = 0;

    /**
     * Dias apos vencimento do juros
     *
     * @var int
     */
    protected $chargeInterestAfter = 0;

    /**
     * Dias para protesto
     *
     * @var integer
     */
    protected $protestAfter = 0;

    /**
     * Dias para baixa automática
     *
     * @var integer
     */
    protected $automaticDropAfter;

    /**
     * Data do documento
     *
     * @var DateTimeInterface
     */
    protected $documentDate;

    /**
     * Data de emissão
     *
     * @var DateTimeInterface
     */
    protected $processingDate;

    /**
     * Data de vencimento
     *
     * @var DateTimeInterface
     */
    protected $dueDate;

    /**
     * Data de limite de desconto
     *
     * @var DateTimeInterface
     */
    protected $discountDate;

    /**
     * Campo de aceite
     *
     * @var string
     */
    protected $acceptance = 'N';

    /**
     * Espécie do documento, geralmente DM (Duplicata Mercantil)
     *
     * @var string
     */
    protected $documentType = 'DM';

    /**
     * Espécie do documento, código para remessa
     *
     * @var array
     */
    protected $documentTypes = [];

    /**
     * Número do documento
     *
     * @var int
     */
    protected $documentNumber;

    /**
     * Define o número definido pelo cliente para compor o Nosso Número
     *
     * @var int
     */
    protected $number;

    /**
     * Define o número definido pelo cliente para controle da remessa
     *
     * @var string
     */
    protected $controlNumber;

    /**
     * Campo de uso do banco no boleto
     *
     * @var string
     */
    protected $bankUsage;

    /**
     * Agência
     *
     * @var string
     */
    protected $agency;

    /**
     * Dígito da agência
     *
     * @var string
     */
    protected $agencyCheckDigit;

    /**
     * Conta
     *
     * @var string
     */
    protected $account;

    /**
     * Dígito da conta
     *
     * @var string
     */
    protected $accountCheckDigit;

    /**
     * Modalidade de cobrança do cliente, geralmente Cobrança Simples ou Registrada
     *
     * @var string
     */
    protected $wallet;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $wallets = [];

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $walletNames = [];

    /**
     * Entidade beneficiário (quem emite o boleto)
     *
     * @var PersonInterface
     */
    protected $beneficiary;

    /**
     * Entidade que pagará o boleto
     *
     * @var PersonInterface
     */
    protected $payer;

    /**
     * Entidade sacador avalista
     *
     * @var PersonInterface
     */
    protected $guarantor;

    /**
     * Array com as linhas do demonstrativo (descrição do pagamento)
     *
     * @var array
     */
    protected $demonstrative;

    /**
     * Linha de local de pagamento
     *
     * @var string
     */
    protected $paymentPlace = 'Pagável em qualquer agência bancária até o vencimento.';

    /**
     * Array com as linhas de instruções
     *
     * @var array
     */
    protected $instructions = ['Pagar até a data do vencimento.'];

    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     *
     * @var string
     */
    protected $logo;

    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $additionalVariables = [];

    /**
     * Cache do campo livre para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $fieldFree;

    /**
     * Cache do nosso numero para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $fieldOurNumber;


    /**
     * Cache da linha digitável para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $fieldDigitableLine;


    /**
     * Cache do código de barras para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $fieldBarCode;


    /**
     * Status do boleto, se vai criar alterar ou baixa no banco.
     *
     * @var int
     */
    protected $status = SlipInterface::STATUS_REGISTRY;


    /**
     * Construtor
     *
     * @param array $params Parâmetros iniciais para construção do objeto
     * @throws Exception
     */
    public function __construct($params = [])

    {
        Util::fillClass($this, $params);
        // Marca a data de emissão para hoje, caso não especificada
        if (!$this->getDocumentDate()) {
            $this->setDocumentDate(new DateTime());
        }
        // Marca a data de processamento para hoje, caso não especificada
        if (!$this->getProcessingDate()) {
            $this->setProcessingDate(new DateTime());
        }
        // Marca a data de vencimento para daqui a 5 dias, caso não especificada
        if (!$this->getDueDate()) {
            $this->setDueDate((new DateTime())->modify('+5 days'));
        }
        // Marca a data de desconto
        if (!$this->getDiscountDate()) {
            $this->setDiscountDate($this->getDueDate());
        }
    }

    /**
     * @return array
     */
    public function getProtectedFields(): array
    {
        return $this->protectedFields;
    }

    /**
     * Seta os campos obrigatórios
     *
     * @return $this
     */
    protected function setRequiredFields(): SlipAbstract
    {
        $args = func_get_args();
        $this->requiredFields = [];
        foreach ($args as $arg) {
            $this->addRequiredFiled($arg);
        }
        return $this;
    }

    /**
     * Adiciona os campos obrigatórios
     *
     * @return $this
     */
    protected function addRequiredFiled(): SlipAbstract
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            !is_array($arg) || call_user_func_array([$this, __FUNCTION__], $arg);
            !is_string($arg) || array_push($this->requiredFields, $arg);
        }
        return $this;
    }

    /**
     * Define a agência
     *
     * @param string $agency
     * @return SlipAbstract
     */
    public function setAgency($agency): SlipAbstract
    {
        $this->agency = (string)$agency;
        return $this;
    }

    /**
     * Retorna a agência
     *
     * @return string
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Define o dígito da agência
     *
     * @param string $agencyCheckDigit
     * @return SlipAbstract
     */
    public function setAgencyCheckDigit($agencyCheckDigit): SlipAbstract
    {
        $this->agencyCheckDigit = $agencyCheckDigit;
        return $this;
    }

    /**
     * Retorna o dígito da agência
     *
     * @return string
     */
    public function getAgencyCheckDigit()
    {
        return $this->agencyCheckDigit;
    }

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param string $wallet
     * @return SlipAbstract
     * @throws Exception
     */
    public function setWallet($wallet): SlipAbstract
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
     * Retorna as carteiras disponíveis para este banco
     *
     * @return array
     */
    public function getWallets(): array
    {
        return $this->wallets;
    }

    /**
     * Define a entidade beneficiário
     *
     * @param $beneficiary
     *
     * @return SlipAbstract
     * @throws Exception
     */
    public function setBeneficiary($beneficiary): SlipAbstract
    {
        if ($beneficiary instanceof PersonInterface) {
            $this->beneficiary = $beneficiary;
        } elseif (is_array($beneficiary)) {
            $this->beneficiary = new Person($beneficiary);
        } else {
            throw new Exception('Parâmetro beneficiário deve ser um objeto herdado de PersonInterface ou um array com as informações');
        }
        return $this;
    }

    /**
     * Retorna a entidade beneficiário
     *
     * @return PersonInterface
     */
    public function getBeneficiary(): PersonInterface
    {
        return $this->beneficiary;
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
     * Define o número da conta
     *
     * @param string $account
     * @return SlipAbstract
     */
    public function setAccount($account): SlipAbstract
    {
        $this->account = (string)$account;
        return $this;
    }

    /**
     * Retorna o número da conta
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Define o dígito verificador da conta
     *
     * @param string $accountCheckDigit
     * @return SlipAbstract
     */
    public function setAccountCheckDigit($accountCheckDigit): SlipAbstract
    {
        $this->accountCheckDigit = $accountCheckDigit;
        return $this;
    }

    /**
     * Retorna o dígito verificador da conta
     *
     * @return string
     */
    public function getAccountCheckDigit()
    {
        return $this->accountCheckDigit;
    }

    /**
     * Define a data de vencimento
     *
     * @param DateTimeInterface $dueDate
     * @return SlipAbstract
     */
    public function setDueDate(DateTimeInterface $dueDate): SlipAbstract
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    /**
     * Retorna a data de vencimento
     *
     * @return DateTimeInterface
     */
    public function getDueDate(): DateTimeInterface
    {
        return $this->dueDate;
    }

    /**
     * Define a data de limite de desconto
     *
     * @param DateTimeInterface $discountDate
     * @return SlipAbstract
     */
    public function setDiscountDate(DateTimeInterface $discountDate): SlipAbstract
    {
        $this->discountDate = $discountDate;
        return $this;
    }

    /**
     * Retorna a data de limite de desconto
     *
     * @return DateTimeInterface
     */
    public function getDiscountDate(): DateTimeInterface
    {
        return $this->discountDate;
    }

    /**
     * Define a data do documento
     *
     * @param DateTimeInterface $documentDate
     * @return SlipAbstract
     */
    public function setDocumentDate(DateTimeInterface $documentDate): SlipInterface
    {
        $this->documentDate = $documentDate;
        return $this;
    }

    /**
     * Retorna a data do documento
     *
     * @return DateTimeInterface
     */
    public function getDocumentDate(): DateTimeInterface
    {
        return $this->documentDate;
    }

    /**
     * Define o campo aceite
     *
     * @param string $acceptance
     * @return SlipAbstract
     */
    public function setAcceptance($acceptance): SlipAbstract
    {
        $this->acceptance = $acceptance;
        return $this;
    }

    /**
     * Retorna o campo aceite
     *
     * @return string
     */
    public function getAcceptance()
    {
        return is_numeric($this->acceptance) ? ($this->acceptance ? 'A' : 'N') : $this->acceptance;
    }

    /**
     * Define o campo Espécie Doc, geralmente DM (Duplicata Mercantil)
     *
     * @param string $documentType
     * @return SlipAbstract
     */
    public function setDocumentType(string $documentType): SlipAbstract
    {
        $this->documentType = $documentType;
        return $this;
    }

    /**
     * Retorna o campo Espécie Doc, geralmente DM (Duplicata Mercantil)
     *
     * @return string
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * Retorna o codigo da Espécie Doc
     *
     * @param int $default
     *
     * @return string
     */
    public function getDocumentTypeCode(int $default = 99)
    {
        return key_exists(strtoupper($this->documentType), $this->documentTypes)
            ? $this->documentTypes[strtoupper($this->getDocumentType())]
            : $default;
    }

    /**
     * Define o campo Número do documento
     *
     * @param int $documentNumber
     * @return SlipAbstract
     */
    public function setDocumentNumber(int $documentNumber): SlipAbstract
    {
        $this->documentNumber = $documentNumber;
        return $this;
    }

    /**
     * Retorna o campo Número do documento
     *
     * @return int
     */
    public function getDocumentNumber(): int
    {
        return $this->documentNumber;
    }

    /**
     * Define o número  definido pelo cliente para compor o nosso número
     *
     * @param int $number
     * @return SlipAbstract
     */
    public function setNumber(int $number): SlipAbstract
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para compor o nosso número
     *
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Define o número  definido pelo cliente para controle da remessa
     *
     * @param int $controlNumber
     * @return SlipAbstract
     */
    public function setControlNumber(int $controlNumber): SlipAbstract
    {
        $this->controlNumber = $controlNumber;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para controle da remessa
     *
     * @return int
     */
    public function getControlNumber(): int
    {
        return $this->controlNumber;
    }

    /**
     * Define o campo Uso do banco
     *
     * @param string $bankUsage
     * @return SlipAbstract
     */
    public function setBankUsage(string $bankUsage): SlipAbstract
    {
        $this->bankUsage = $bankUsage;

        return $this;
    }

    /**
     * Retorna o campo Uso do banco
     *
     * @return string
     */
    public function getBankUsage(): string
    {
        return $this->bankUsage;
    }

    /**
     * Define a data de geração do boleto
     *
     * @param DateTimeInterface $processingDate
     * @return SlipAbstract
     */
    public function setProcessingDate(DateTimeInterface $processingDate): SlipAbstract
    {
        $this->processingDate = $processingDate;

        return $this;
    }

    /**
     * Retorna a data de geração do boleto
     *
     * @return DateTimeInterface
     */
    public function getProcessingDate(): DateTimeInterface
    {
        return $this->processingDate;
    }

    /**
     * Adiciona uma instrução (máximo 5)
     *
     * @param string $instructionItem
     * @return SlipAbstract
     * @throws Exception
     */
    public function addInstructionItem(string $instructionItem): SlipAbstract
    {
        if (count($this->getInstructions()) > 8) {
            throw new Exception('Atingido o máximo de 5 instruções.');
        }
        array_push($this->instructions, $instructionItem);

        return $this;
    }

    /**
     * Define um array com instruções (máximo 8) para pagamento
     *
     * @param array $instructions
     *
     * @return SlipAbstract
     * @throws Exception
     */
    public function setInstructions(array $instructions): SlipAbstract
    {
        if (count($instructions) > 8) {
            throw new Exception('Máximo de 8 instruções.');
        }
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * Retorna um array com instruções (máximo 8) para pagamento
     *
     * @return array
     */
    public function getInstructions(): array
    {
        return array_slice((array)$this->instructions + [null, null, null, null, null, null, null, null], 0, 8);
    }

    /**
     * Adiciona um demonstrativo (máximo 5)
     *
     * @param string $demonstrativeItem
     *
     * @return SlipAbstract
     * @throws Exception
     */
    public function addDemonstrativeItem(string $demonstrativeItem): SlipAbstract
    {
        if (count($this->getDemonstrative()) > 5) {
            throw new Exception('Atingido o máximo de 5 demonstrativos.');
        }
        array_push($this->demonstrative, $demonstrativeItem);

        return $this;
    }

    /**
     * Define um array com a descrição do demonstrativo (máximo 5)
     *
     * @param array $demonstrative
     *
     * @return SlipAbstract
     * @throws Exception
     */
    public function setDemonstrative(array $demonstrative): SlipAbstract
    {
        if (count($demonstrative) > 5) {
            throw new Exception('Máximo de 5 demonstrativos.');
        }
        $this->demonstrative = $demonstrative;

        return $this;
    }

    /**
     * Retorna um array com a descrição do demonstrativo (máximo 5)
     *
     * @return array
     */
    public function getDemonstrative(): array
    {
        return array_slice((array)$this->demonstrative + [null, null, null, null, null], 0, 5);
    }

    /**
     * Define o local de pagamento do boleto
     *
     * @param string $paymentPlace
     *
     * @return SlipAbstract
     */
    public function setPaymentPlace(string $paymentPlace): SlipAbstract
    {
        $this->paymentPlace = $paymentPlace;

        return $this;
    }

    /**
     * Retorna o local de pagamento do boleto
     *
     * @return string
     */
    public function getPaymentPlace(): string
    {
        return $this->paymentPlace;
    }

    /**
     * Define a moeda utilizada pelo boleto
     *
     * @param int $currency
     *
     * @return SlipAbstract
     */
    public function setCurrency(int $currency): SlipAbstract
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Retorna a moeda utilizada pelo boleto
     *
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }

    /**
     * Define o objeto do pagador
     *
     * @param PersonInterface|array $payer
     * @return SlipAbstract
     * @throws Exception
     */
    public function setPayer($payer): SlipAbstract
    {
        if ($payer instanceof PersonInterface) {
            $this->payer = $payer;
        } elseif (is_array($payer)) {
            $this->payer = new Person($payer);
        } else {
            throw new Exception('Parâmetro pagador deve ser um objeto herdado de PersonInterface ou um array com as informações');
        }
        return $this;
    }

    /**
     * Retorna o objeto do pagador
     *
     * @return PersonInterface
     */
    public function getPayer(): PersonInterface
    {
        return $this->payer;
    }

    /**
     * Define o objeto sacador avalista do boleto
     *
     * @param PersonInterface|array $guarantor
     * @return SlipAbstract
     * @throws Exception
     */
    public function setGuarantor($guarantor): SlipAbstract
    {
        if ($guarantor instanceof PersonInterface) {
            $this->guarantor = $guarantor;
        } elseif (is_array($guarantor)) {
            $this->guarantor = new Person($guarantor);
        } else {
            throw new Exception('Parâmetro avalista deve ser um objeto herdado de PersonInterface ou um array com as informações');
        }
        return $this;
    }

    /**
     * Retorna o objeto sacador avalista do boleto
     *
     * @return PersonInterface
     */
    public function getGuarantor(): PersonInterface
    {
        return $this->guarantor;
    }

    /**
     * Define o valor total do boleto (incluindo taxas)
     *
     * @param float $value
     *
     * @return SlipAbstract
     */
    public function setValue(float $value): SlipAbstract
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retorna o valor total do boleto (incluindo taxas)
     *
     * @return float
     */
    public function getValue(): float
    {
        return round($this->value, 2);
    }

    /**
     * Define o desconto total do boleto (incluindo taxas)
     *
     * @param float $discount
     * @return SlipAbstract
     */
    public function setDiscount(float $discount): SlipAbstract
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Retorna o desconto total do boleto (incluindo taxas)
     *
     * @return float
     */
    public function getDiscount(): float
    {
        return round($this->discount, 2);
    }

    /**
     * Seta a % de multa
     *
     * @param float $fine
     * @return SlipAbstract
     */
    public function setFine(float $fine): SlipAbstract
    {
        $this->fine = (float)($fine > 0.00 ? $fine : 0.00);

        return $this;
    }

    /**
     * Retorna % de multa
     *
     * @return float
     */
    public function getFine(): float
    {
        return round($this->fine, 2);
    }

    /**
     * Seta a % de juros
     *
     * @param float $interest
     * @return SlipAbstract
     */
    public function setInterest(float $interest): SlipAbstract
    {
        $this->interest = (float)($interest > 0.00 ? $interest : 0.00);

        return $this;
    }

    /**
     * Retorna % juros
     *
     * @return float
     */
    public function getInterest(): float
    {
        return $this->interest;
    }

    /**
     * Seta a quantidade de dias apos o vencimento que cobra o juros
     *
     * @param int $chargeInterestAfter
     *
     * @return SlipAbstract
     */
    public function setChargeInterestAfter(int $chargeInterestAfter): SlipAbstract
    {
        $chargeInterestAfter = (int)$chargeInterestAfter;
        $this->chargeInterestAfter = $chargeInterestAfter > 0 ? $chargeInterestAfter : 0;

        return $this;
    }

    /**
     * Retorna a quantidade de dias apos o vencimento que cobrar a juros
     *
     * @return int
     */
    public function getChargeInterestAfter(): int
    {
        return $this->chargeInterestAfter;
    }

    /**
     * Seta dias para protesto
     *
     * @param int $protestAfter
     *
     * @return SlipAbstract
     * @throws Exception
     */
    public function setProtestAfter(int $protestAfter): SlipAbstract
    {
        if ($this->getAutomaticDropAfter() > 0) {
            throw new Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $protestAfter = (int)$protestAfter;
        $this->protestAfter = $protestAfter > 0 ? $protestAfter : 0;

        return $this;
    }

    /**
     * Retorna os diasProtesto
     *
     * @param int $default
     *
     * @return int
     */
    public function getProtestAfter(int $default = 0): int
    {
        return $this->protestAfter > 0 ? $this->protestAfter : $default;
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $automaticDrop
     *
     * @throws Exception
     */
    public function setAutomaticDropAfter(int $automaticDrop)
    {
        $exception = sprintf('O banco %s não suporta baixa automática, pode usar também: setDiasProtesto(%s)', basename(get_class($this)), $automaticDrop);
        throw new Exception($exception);
    }

    /**
     * Retorna os diasProtesto
     *
     * @param int $default
     *
     * @return int
     */
    public function getAutomaticDropAfter(int $default = 0): int
    {
        return $this->automaticDropAfter > 0 ? $this->automaticDropAfter : $default;
    }

    /**
     * Define a localização do logotipo
     *
     * @param string $logo
     *
     * @return SlipAbstract
     */
    public function setLogo(string $logo): SlipAbstract
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Retorna a localização do logotipo
     *
     * @return string
     */
    public function getLogo(): string
    {
        return $this->logo ? $this->logo : "http://dummyimage.com/300x70/f5/0.png&text=Sem+Logo";
    }

    /**
     * Retorna o logotipo em Base64, pronto para ser inserido na página
     *
     * @return string
     */
    public function getBase64Logo(): string
    {
        return 'data:image/' . pathinfo($this->getLogo(), PATHINFO_EXTENSION) .
            ';base64,' . base64_encode(file_get_contents($this->getLogo()));
    }

    /**
     * Retorna o logotipo do banco em Base64, pronto para ser inserido na página
     *
     * @return string
     */
    public function getBase64BankLogo(): string
    {
        return 'data:image/' . pathinfo($this->getBankLogo(),
                PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($this->getBankLogo()));
    }

    /**
     * Retorna a localização do logotipo do banco relativo à pasta de imagens
     *
     * @return string
     */
    public function getBankLogo(): string
    {
        return realpath(__DIR__ . '/../../logos/' . $this->getBankCode() . '.png');
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Marca o boleto para ser alterado no banco
     *
     * @return SlipAbstract
     */
    public function alterSlip(): SlipInterface
    {
        $this->status = SlipInterface::STATUS_ALTER;

        return $this;
    }

    /**
     * Marca o boleto para ser baixado no banco
     *
     * @return SlipAbstract
     */
    public function dropSlip(): SlipInterface
    {
        $this->status = SlipInterface::STATUS_DROP;

        return $this;
    }

    /**
     * Retorna o Nosso Número calculado.
     *
     * @return string
     */
    public function getOurNumber()
    {
        if (empty($this->fieldOurNumber)) {
            return $this->fieldOurNumber = $this->generateOurNumber();
        }
        return $this->fieldOurNumber;
    }

    /**
     * Mostra exception ao sem querer tentar configurar o nosso número
     *
     * @throws Exception
     */
    final public function setOurNumber()
    {
        throw new Exception('Não é possível definir o nosso número diretamente. Utilize o método setNumero.');
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return $this->getOurNumber();
    }

    /**
     * Método onde o boleto deverá gerar o Nosso Número.
     *
     * @return string
     */
    abstract protected function generateOurNumber();

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    abstract protected function getFieldFree();

    /**
     * Método que valida se o banco tem todos os campos obrigatórios preenchidos
     *
     * @param $messages
     * @return boolean
     */
    public function isValid(&$messages): bool
    {
        foreach ($this->requiredFields as $field) {
            if (call_user_func([$this, 'get' . ucwords($field)]) == '') {
                $messages .= "Campo $field está em branco";
                return false;
            }
        }
        return true;
    }

    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgencyAndAccount(): string
    {
        $agency = $this->getAgencyCheckDigit() !== null ? $this->getAgency() . '-' . $this->getAgencyCheckDigit() : $this->getAgency();
        $account = $this->getAccountCheckDigit() !== null ? $this->getAccount() . '-' . $this->getAccountCheckDigit() : $this->getAccount();

        return $agency . ' / ' . $account;
    }

    /**
     * Retorna o nome da carteira para impressão no boleto
     *
     * Caso o nome da carteira a ser impresso no boleto seja diferente do número
     * Então crie uma variável na classe do banco correspondente $carteirasNomes
     * sendo uma array cujos índices sejam os números das carteiras e os valores
     * seus respectivos nomes
     *
     * @return string
     */
    public function getWalletName(): string
    {
        return isset($this->walletNames[$this->getWallet()]) ? $this->walletNames[$this->getWallet()] : $this->getWallet();
    }

    /**
     * Retorna o código de barras
     *
     * @return string
     * @throws Exception
     */
    public function getBarCode(): string
    {
        if (!empty($this->fieldBarCode)) {
            return $this->fieldBarCode;
        }

        if (!$this->isValid($message)) {
            throw new Exception('Campos requeridos pelo banco, aparentam estar ausentes ' . $message);
        }

        $bankCode = Util::numberFormatGeral($this->getBankCode(), 3)
            . $this->getCurrency()
            . Util::dueDateFactor($this->getDueDate())
            . Util::numberFormatGeral($this->getValue(), 10)
            . $this->getFieldFree();

        $resto = Util::modulo11($bankCode, 2, 9, 0);
        $dv = (in_array($resto, [0, 10, 11])) ? 1 : $resto;

        return $this->fieldBarCode = substr($bankCode, 0, 4) . $dv . substr($bankCode, 4);
    }

    /**
     * Retorna o código do banco com o dígito verificador
     *
     * @return string
     */
    public function getBankCodeWithCheckDigit(): string
    {
        $bankCode = $this->getBankCode();
        $semX = [SlipInterface::BANK_CODE_CEF];
        $x10 = in_array($bankCode, $semX) ? 0 : 'X';
        return $bankCode . '-' . Util::modulo11($bankCode, 2, 9, 0, $x10);
    }

    /**
     * Retorna a linha digitável do boleto
     *
     * @return string
     * @throws Exception
     */
    public function getDigitableLine(): string
    {
        if (!empty($this->fieldDigitableLine)) {
            return $this->fieldDigitableLine;
        }

        $barCode = $this->getBarCode();

        $s1 = substr($barCode, 0, 4) . substr($barCode, 19, 5);
        $s1 = $s1 . Util::modulo10($s1);
        $s1 = substr_replace($s1, '.', 5, 0);

        $s2 = substr($barCode, 24, 10);
        $s2 = $s2 . Util::modulo10($s2);
        $s2 = substr_replace($s2, '.', 5, 0);

        $s3 = substr($barCode, 34, 10);
        $s3 = $s3 . Util::modulo10($s3);
        $s3 = substr_replace($s3, '.', 5, 0);

        $s4 = substr($barCode, 4, 1);

        $s5 = substr($barCode, 5, 14);

        return $this->fieldDigitableLine = sprintf('%s %s %s %s %s', $s1, $s2, $s3, $s4, $s5);
    }

    /**
     * Render PDF
     *
     * @param bool $print
     * @param bool $showInstructions
     * @return string
     * @throws Exception
     */
    public function renderPDF(bool $print = false, bool $showInstructions = true)
    {
        $pdf = new Pdf();
        $pdf->addBoleto($this);
        if ($print) $pdf->showPrint();
        if (!$showInstructions) $pdf->hideInstrucoes();
        return $pdf->gerarBoleto('S', null);
    }

    /**
     * Render HTML
     *
     * @param bool $print
     * @param bool $showInstructions
     * @return string
     * @throws Exception
     */
    public function renderHTML(bool $print = false, bool $showInstructions = true)
    {
        $html = new Html();
        $html->addBoleto($this);
        if ($print) $html->showPrint();
        if (!$showInstructions) $html->hideInstrucoes();
        return $html->gerarBoleto();
    }

    /**
     * @return SlipAbstract
     */
    public function copy(): SlipAbstract
    {
        return clone $this;
    }

    /**
     * On clone clean variables
     */
    function __clone()
    {
        $this->fieldFree = null;
        $this->fieldOurNumber = null;
        $this->fieldDigitableLine = null;
        $this->fieldBarCode = null;
    }

    /**
     * Return SlipInterface Array.
     *
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'digitableLina' => $this->getDigitableLine(),
                'barCode' => $this->getBarCode(),
                'beneficiary' => $this->getPayer()->toArray(),
                'base64Logo' => $this->getBase64Logo(),
                'logo' => $this->getLogo(),
                'base64BankLogo' => $this->getBase64BankLogo(),
                'bankLogo' => $this->getBankLogo(),
                'bankCodeWithCheckDigit' => $this->getBankCodeWithCheckDigit(),
                'currency' => 'R$',
                'dueDate' => $this->getDueDate(),
                'processingDate' => $this->getProcessingDate(),
                'documentDate' => $this->getDocumentDate(),
                'discountDate' => $this->getDiscountDate(),
                'value' => Util::numberInReal($this->getValue(), 2, false),
                'discount' => Util::numberInReal($this->getDiscount(), 2, false),
                'fine' => Util::numberInReal($this->getFine(), 2, false),
                'interest' => Util::numberInReal($this->getInterest(), 2, false),
                'chargeInterestAfter' => $this->getChargeInterestAfter(),
                'protestAfter' => $this->getProtestAfter(),
                'guarantor' => empty($this->getGuarantor()) ? $this->getGuarantor()->toArray() : [],
                'payer' => $this->getPayer()->toArray(),
                'demonstrative' => $this->getDemonstrative(),
                'instructions' => $this->getInstructions(),
                'paymentPlace' => $this->getPaymentPlace(),
                'number' => $this->getNumber(),
                'documentNumber' => $this->getDocumentNumber(),
                'controlNumber' => $this->getControlNumber(),
                'agencyAndAccount' => $this->getAgencyAndAccount(),
                'ourNumber' => $this->getOurNumber(),
                'outNumberCustom' => $this->getOurNumberCustom(),
                'documentType' => $this->getDocumentType(),
                'documentTypeCode' => $this->getDocumentTypeCode(),
                'acceptance' => $this->getAcceptance(),
                'wallet' => $this->getWallet(),
                'walletName' => $this->getWalletName(),
                'bankUsage' => $this->getBankUsage(),
                'status' => $this->getStatus(),
            ], $this->additionalVariables
        );
    }
}
