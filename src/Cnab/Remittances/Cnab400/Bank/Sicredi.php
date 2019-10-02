<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use DateTime;
use Exception;
use PhpBoleto\Cnab\Remittances\Cnab400\RemittanceAbstract;
use PhpBoleto\Interfaces\Cnab\RemittanceInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\Util;

/**
 * Class Sicredi
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banco
 */
class Sicredi extends RemittanceAbstract implements RemittanceInterface
{
    const ESPECIE_DUPLICATA = 'A';
    const ESPECIE_DUPLICATA_RURAL = 'B';
    const ESPECIE_NOTA_PROMISSORIA = 'C';
    const ESPECIE_NOTA_PROMISSORIA_RURAL = 'D';
    const ESPECIE_NOTA_SEGURO = 'E';
    const ESPECIE_RECIBO = 'G';
    const ESPECIE_LETRA_CAMBIO = 'H';
    const ESPECIE_NOTA_DEBITOS = 'I';
    const ESPECIE_NOTA_SERVICOS = 'J';
    const ESPECIE_OUTROS = 'K';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_CARTEIRA = '19';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_PROTESTO = '06';

    /**
     * Sicredi constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->setWallet('A'); //Carteira Simples 'A'
        $this->addRequiredField('remittanceId');
    }

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param string $carteira
     * @return $this
     */
    public function setWallet($carteira)
    {
        $this->wallet = 'A';
        return $this;
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = SlipInterface::BANK_CODE_SICREDI;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $wallets = ['A'];

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $eolChar = "\r\n";

    /**
     * Caracter de fim de arquivo
     *
     * @var null
     */
    protected $endOfFileChar = "\r\n";

    /**
     * @return $this
     * @throws Exception
     */
    protected function header()
    {
        $this->initiateHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 31, Util::formatCnab('9', $this->getAccount(), 5));
        $this->add(32, 45, Util::formatCnab('9L', $this->getBeneficiary()->getDocument(), 14, 0, 0));
        $this->add(46, 76, '');
        $this->add(77, 79, $this->getBankCode());
        $this->add(80, 94, Util::formatCnab('X', 'Sicredi', 15));
        $this->add(95, 102, date('Ymd'));
        $this->add(103, 110, '');
        $this->add(111, 117, Util::formatCnab('9', $this->getRemittanceId(), 7));
        $this->add(118, 390, '');
        $this->add(391, 394, '2.00');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param SlipInterface $slip
     * @return $this
     * @throws Exception
     */
    public function addSlip(SlipInterface $slip)
    {
        if (!$slip->isRegistry()) {
            return $this;
        }

        $this->initiateDetail();

        $this->add(1, 1, '1');
        $this->add(2, 2, 'A');
        $this->add(3, 3, $this->getWalletNumber());
        $this->add(4, 4, 'A');
        $this->add(5, 16, '');
        $this->add(17, 17, 'A');
        $this->add(18, 18, 'A');
        $this->add(19, 19, 'B');
        $this->add(20, 47, '');
        $this->add(48, 56, Util::formatCnab('9', $slip->getOurNumber(), 9));
        $this->add(57, 62, '');
        $this->add(63, 70, (new DateTime())->format('Ymd'));
        $this->add(71, 71, '');
        $this->add(72, 72, $slip->getByte() == 1 ? 'S' : 'N');
        $this->add(73, 73, '');
        $this->add(74, 74, $slip->getByte() == 1 ? 'A' : 'B');
        $this->add(75, 76, '');
        $this->add(77, 78, '');
        $this->add(79, 82, '');
        $this->add(83, 92, Util::formatCnab('9', 0, 10, 2));
        $this->add(93, 96, Util::formatCnab('9', $slip->getFine(), 4, 2));
        $this->add(97, 108, '');
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($slip->getStatus() == $slip::STATUS_DROP) {
            $this->add(109, 110, self::OCORRENCIA_BAIXA); // BAIXA
        }
        if ($slip->getStatus() == $slip::STATUS_ALTER) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        $this->add(111, 120, Util::formatCnab('X', $slip->getDocumentNumber(), 10));
        $this->add(121, 126, $slip->getDueDate()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $slip->getValue(), 13, 2));
        $this->add(140, 148, '');
        $this->add(149, 149, $slip->getDocumentTypeCode('A'));
        $this->add(150, 150, $slip->getAcceptance());
        $this->add(151, 156, $slip->getDocumentDate()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($slip->getProtestAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTO);
            $this->add(159, 160, Util::formatCnab('9', $slip->getProtestAfter(), 2));
        }
        $this->add(161, 173, Util::formatCnab('9', $slip->getInterest(), 13, 2));
        $this->add(174, 179, $slip->getDiscount() > 0 ? $slip->getDiscountDate()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $slip->getDiscount(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($slip->getPayer()->getDocument())) == 14 ? '20' : '10');
        $this->add(221, 234, Util::formatCnab('9L', $slip->getPayer()->getDocument(), 14));
        $this->add(235, 274, Util::formatCnab('X', $slip->getPayer()->getName(), 40));
        $this->add(275, 314, Util::formatCnab('X', $slip->getPayer()->getAddress(), 40));
        $this->add(315, 319, '00000');
        $this->add(320, 325, '000000');
        $this->add(326, 326, ' ');
        $this->add(327, 334, Util::formatCnab('9L', $slip->getPayer()->getPostalCode(), 8));
        $this->add(335, 339, '00000');
        $this->add(340, 353, Util::formatCnab('9L', $slip->getGuarantor() ? $slip->getGuarantor()->getDocument() : '', 14));
        $this->add(354, 394, Util::formatCnab('X', $slip->getGuarantor() ? $slip->getGuarantor()->getName() : '', 41));
        $this->add(395, 400, Util::formatCnab('9', $this->registryCount + 1, 6));

        if ($slip->getByte() == 1) {
            $this->initiateDetail();

            $this->add(1, 1, '2');
            $this->add(2, 12, '');
            $this->add(13, 21, Util::formatCnab('9', $slip->getOurNumber(), 9));
            $this->add(22, 101, Util::formatCnab('X', $slip->getInstructions()[0], 80));
            $this->add(102, 181, Util::formatCnab('X', $slip->getInstructions()[1], 80));
            $this->add(122, 261, Util::formatCnab('X', $slip->getInstructions()[2], 80));
            $this->add(262, 341, Util::formatCnab('X', $slip->getInstructions()[3], 80));
            $this->add(342, 351, Util::formatCnab('9', $slip->getDocumentNumber(), 10));
            $this->add(352, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->registryCount + 1, 6));
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function trailer()
    {
        $this->InitiateTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 2, '1');
        $this->add(3, 5, $this->getBankCode());
        $this->add(6, 10, $this->getAccount());
        $this->add(11, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
