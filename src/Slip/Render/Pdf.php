<?php

namespace PhpBoleto\Slip\Render;

use Exception;
use PhpBoleto\Interfaces\Slip\Render\PdfPdfInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\Util;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

class Pdf extends PdfAbstract implements PdfPdfInterface
{
    const OUTPUT_STANDARD = 'I';
    const OUTPUT_DOWNLOAD = 'D';
    const OUTPUT_SAVE = 'F';
    const OUTPUT_STRING = 'S';

    private $fontDefault = 'Arial';

    /**
     * @var SlipInterface[]
     */
    private $slip = array();

    /**
     * @var bool
     */
    private $print = false;

    /**
     * @var bool
     */
    private $showInstructions = true;

    /**
     * @var bool
     */
    private $showReceipt = false;

    private $cellDescriptionSize = 3; // tamanho célula descrição
    private $cellDataSize = 4; // tamanho célula dado
    private $fontDescriptionSize = 6; // tamanho fonte descrição
    private $fontCellSize = 8; // tamanho fonte célula
    private $smallBarSize = 0.2; // tamanho barra fina
    private $slipsTotal = 0;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(false);
        $this->SetLeftMargin(20);
        $this->SetTopMargin(15);
        $this->SetRightMargin(20);
        $this->SetLineWidth($this->smallBarSize);
    }

    /**
     * Insere as instruções de impressão
     *
     * @param integer $i
     * @return Pdf
     */
    protected function instructions(int $i): Pdf
    {
        $this->SetFont($this->fontDefault, '', 8);
        if ($this->slipsTotal > 1) {
            $this->SetAutoPageBreak(true);
            $this->SetY(5);
            $this->Cell(30, 10, date('d/m/Y H:i:s'));
            $this->Cell(0, 10, "Boleto " . ($i + 1) . " de " . $this->slipsTotal, 0, 1, 'R');
        }

        $this->SetFont($this->fontDefault, 'B', 8);
        if ($this->showInstructions) {
            $this->Cell(0, 5, $this->_('Instruções de Impressão'), 0, 1, 'C');
            $this->Ln(5);
            $this->SetFont($this->fontDefault, '', 6);
            $this->Cell(0, $this->cellDescriptionSize, $this->_('- Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).'), 0, 1, 'L');
            $this->Cell(0, $this->cellDescriptionSize, $this->_('- Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.'), 0, 1, 'L');
            $this->Cell(0, $this->cellDescriptionSize, $this->_('- Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.'), 0, 1, 'L');
            $this->Cell(0, $this->cellDescriptionSize, $this->_('- Caso não apareça o código de barras no final, clique em F5 para atualizar esta tela.'), 0, 1, 'L');
            $this->Cell(0, $this->cellDescriptionSize, $this->_('- Caso tenha problemas ao imprimir, copie a seqüencia numérica abaixo e pague no caixa eletrônico ou no internet banking:'), 0, 1, 'L');
            $this->Ln(6);

            $this->SetFont($this->fontDefault, '', $this->fontCellSize);
            $this->Cell(25, $this->cellDataSize, $this->_('Linha Digitável: '), 0, 0);
            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
            $this->Cell(0, $this->cellDataSize, $this->_($this->slip[$i]->getDigitableLine()), 0, 1);
            $this->SetFont($this->fontDefault, '', $this->fontCellSize);
            $this->Cell(25, $this->cellDataSize, $this->_('Número: '), 0, 0);
            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
            $this->Cell(0, $this->cellDataSize, $this->_($this->slip[$i]->getNumber()), 0, 1);
            $this->SetFont($this->fontDefault, '', $this->fontCellSize);
            $this->Cell(25, $this->cellDataSize, $this->_('Valor: '), 0, 0);
            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
            $this->Cell(0, $this->cellDataSize, $this->_(Util::numberInReal($this->slip[$i]->getValue())), 0, 1);
            $this->SetFont($this->fontDefault, '', $this->fontCellSize);
        }

        if (!$this->showReceipt) {
            $this->traco('Recibo do Pagador', 4);
        }
        return $this;
    }

    /**
     * Insere a parte que é o recibo de entrega do boleto
     *
     * @param integer $i
     * @return Pdf
     */
    protected function receipt($i): Pdf
    {
        $this->SetFont($this->fontDefault, 'B', 8);
        if ($this->showReceipt) {
            $this->Image($this->slip[$i]->getBankLogo(), 20, ($this->GetY() - 2), 28);
            $this->Cell(29, 6, '', 'B');
            $this->SetFont('', 'B', 13);
            $this->Cell(15, 6, $this->slip[$i]->getBankCodeWithCheckDigit(), 'LBR', 0, 'C');
            $this->Ln(6);

            $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
            $this->Cell(60, $this->cellDescriptionSize, $this->_('Beneficiário'), 'TLR');
            $this->Cell(35, $this->cellDescriptionSize, $this->_('Agencia/Código do beneficiário'), 'TR');
            $this->Cell(75, $this->cellDescriptionSize, $this->_('Motivos de não entregar (Para uso da transportadora)'), 'TR', 1, 'C');

            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);

            $this->textFitCell(60, $this->cellDataSize, $this->_($this->slip[$i]->getBeneficiary()->getName()), 'LR', 0, 'L');
            $this->Cell(35, $this->cellDataSize, $this->_($this->slip[$i]->getAgencyAndAccount()), 'R');
            $this->Cell(75, $this->cellDataSize, $this->_(''), 'R', 1);

            $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
            $this->Cell(60, $this->cellDescriptionSize, $this->_('Pagador'), 'TLR');
            $this->Cell(35, $this->cellDescriptionSize, $this->_('Nosso Numero'), 'TR');
            $this->Cell(75, $this->cellDescriptionSize, $this->_(''), 'R', 1);

            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
            $this->Cell(60, $this->cellDataSize, $this->_($this->slip[$i]->getPayer()->getName()), 'LR');
            $this->Cell(35, $this->cellDataSize, $this->_($this->slip[$i]->getOurNumberCustom()), 'R');

            $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
            $this->Cell(20, $this->cellDataSize, $this->_("( ) Mudou-se"));
            $this->Cell(20, $this->cellDataSize, $this->_("( ) Ausente"));
            $this->Cell(35, $this->cellDataSize, $this->_("( ) Não existe no indicado"), 'R', 1);

            $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
            $this->Cell(19, $this->cellDescriptionSize, $this->_('Vencimento'), 'TLR');
            $this->Cell(19, $this->cellDescriptionSize, $this->_('N. do Documento'), 'TR');
            $this->Cell(10, $this->cellDescriptionSize, $this->_('Espécie'), 'TR');
            $this->Cell(13, $this->cellDescriptionSize, $this->_('Quantidade'), 'TR');
            $this->Cell(34, $this->cellDescriptionSize, $this->_('Valor'), 'TR');
            $this->Cell(75, $this->cellDescriptionSize, $this->_(''), 'R', 1);

            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
            $this->Cell(19, $this->cellDataSize, $this->_($this->slip[$i]->getDueDate()->format('d/m/Y')), 'LR');
            $this->Cell(19, $this->cellDataSize, $this->_($this->slip[$i]->getDocumentNumber()), 'R', 0, 'C');
            $this->Cell(10, $this->cellDataSize, $this->_($this->slip[$i]->getDocumentType()), 'R', 0, 'C');
            $this->Cell(13, $this->cellDataSize, $this->_('1'), 'R', 0, 'C');
            $this->Cell(34, $this->cellDataSize, $this->_(Util::numberInReal($this->slip[$i]->getValue())), 'R', 0, 'R');

            $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
            $this->Cell(20, $this->cellDataSize, $this->_("( ) Recusado"));
            $this->Cell(20, $this->cellDataSize, $this->_("( ) Não procurado"));
            $this->Cell(35, $this->cellDataSize, $this->_("( ) Endereço insuficiente"), 'R', 1);

            $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
            $this->Cell(35, $this->cellDescriptionSize, $this->_('Recebi(emos) o bloqueto/título'), 'TLR');
            $this->Cell(19, $this->cellDescriptionSize, $this->_('Data'), 'TR');
            $this->Cell(40, $this->cellDescriptionSize, $this->_('Assinatura'), 'TR');
            $this->Cell(20, $this->cellDescriptionSize, $this->_('Data'), 'TR');
            $this->Cell(56, $this->cellDescriptionSize, $this->_('Entregador'), 'TR', 1);

            $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
            $this->Cell(35, $this->cellDataSize, $this->_('com as características acima'), 'BLR');
            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
            $this->Cell(19, $this->cellDataSize, $this->_($this->slip[$i]->getDocumentDate()->format('d/m/Y')), 'BLR');
            $this->Cell(40, $this->cellDataSize, $this->_(''), 'BLR');
            $this->Cell(20, $this->cellDataSize, $this->_(''), 'BLR');
            $this->Cell(56, $this->cellDataSize, $this->_(''), 'BLR', 1);

            $jumpLine = 1;

            $this->traco('Recibo do Pagador', $jumpLine, 10);
        }

        return $this;
    }

    /**
     * Insere a logo da empresa
     *
     * @param integer $i
     * @return Pdf
     */
    protected function companyLogo(int $i): Pdf
    {
        $this->Ln(2);
        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);

        $logo = preg_replace('/&.*/', '', $this->slip[$i]->getLogo());
        $ext = pathinfo($logo, PATHINFO_EXTENSION);

        $this->Image($this->slip[$i]->getLogo(), 20, ($this->GetY()), 0, 12, $ext);
        $this->Cell(40);
        $this->Cell(0, $this->cellDescriptionSize, $this->_($this->slip[$i]->getBeneficiary()->getName()), 0, 1);
        $this->Cell(40);
        $this->Cell(0, $this->cellDescriptionSize, $this->_($this->slip[$i]->getBeneficiary()->getDocument(), '##.###.###/####-##'), 0, 1);
        $this->Cell(40);
        $this->Cell(0, $this->cellDescriptionSize, $this->_($this->slip[$i]->getBeneficiary()->getAddress()), 0, 1);
        $this->Cell(40);
        $this->Cell(0, $this->cellDescriptionSize, $this->_($this->slip[$i]->getBeneficiary()->getPostalCodeCityAndStateUf()), 0, 1);
        $this->Ln(8);

        return $this;
    }

    /**
     * Gera a parte do boleto que é a via do beneficiário
     *
     * @param integer $i
     * @return Pdf
     */
    protected function beneficiaryVia(int $i): Pdf
    {
        $this->Image($this->slip[$i]->getBankLogo(), 20, ($this->GetY() - 2), 28);
        $this->Cell(29, 6, '', 'B');
        $this->SetFont('', 'B', 13);
        $this->Cell(15, 6, $this->slip[$i]->getBankCodeWithCheckDigit(), 'LBR', 0, 'C');
        $this->SetFont('', 'B', 10);
        $this->Cell(0, 6, $this->slip[$i]->getDigitableLine(), 'B', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(75, $this->cellDescriptionSize, $this->_('Beneficiário'), 'TLR');
        $this->Cell(35, $this->cellDescriptionSize, $this->_('Agencia/Código do beneficiário'), 'TR');
        $this->Cell(10, $this->cellDescriptionSize, $this->_('Espécie'), 'TR');
        $this->Cell(15, $this->cellDescriptionSize, $this->_('Quantidade'), 'TR');
        $this->Cell(35, $this->cellDescriptionSize, $this->_('Nosso Numero'), 'TR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);

        $this->textFitCell(75, $this->cellDataSize, $this->_($this->slip[$i]->getBeneficiary()->getName()), 'LR', 0, 'L');

        $this->Cell(35, $this->cellDataSize, $this->_($this->slip[$i]->getAgencyAndAccount()), 'R');
        $this->Cell(10, $this->cellDataSize, $this->_('R$'), 'R');
        $this->Cell(15, $this->cellDataSize, $this->_(''), 'R');
        $this->Cell(35, $this->cellDataSize, $this->_($this->slip[$i]->getOurNumberCustom()), 'R', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(50, $this->cellDescriptionSize, $this->_('Número do Documento'), 'TLR');
        $this->Cell(40, $this->cellDescriptionSize, $this->_('CPF/CNPJ'), 'TR');
        $this->Cell(30, $this->cellDescriptionSize, $this->_('Vencimento'), 'TR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('Valor do Documento'), 'TR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(50, $this->cellDataSize, $this->_($this->slip[$i]->getDocumentNumber()), 'LR');
        $this->Cell(40, $this->cellDataSize, $this->_($this->slip[$i]->getBeneficiary()->getDocument(), '##.###.###/####-##'), 'R');
        $this->Cell(30, $this->cellDataSize, $this->_($this->slip[$i]->getDueDate()->format('d/m/Y')), 'R');
        $this->Cell(50, $this->cellDataSize, $this->_(Util::numberInReal($this->slip[$i]->getValue())), 'R', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(30, $this->cellDescriptionSize, $this->_('(-) Descontos/Abatimentos'), 'TLR');
        $this->Cell(30, $this->cellDescriptionSize, $this->_('(-) Outras Deduções'), 'TR');
        $this->Cell(30, $this->cellDescriptionSize, $this->_('(+) Mora Multa'), 'TR');
        $this->Cell(30, $this->cellDescriptionSize, $this->_('(+) Acréscimos'), 'TR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('(=) Valor Cobrado'), 'TR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(30, $this->cellDataSize, $this->_(''), 'LR');
        $this->Cell(30, $this->cellDataSize, $this->_(''), 'R');
        $this->Cell(30, $this->cellDataSize, $this->_(''), 'R');
        $this->Cell(30, $this->cellDataSize, $this->_(''), 'R');
        $this->Cell(50, $this->cellDataSize, $this->_(''), 'R', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(0, $this->cellDescriptionSize, $this->_('Pagador'), 'TLR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(0, $this->cellDataSize, $this->_($this->slip[$i]->getPayer()->getNameAndDocument()), 'BLR', 1);

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(100, $this->cellDescriptionSize, $this->_('Demonstrativo'), 0, 0, 'L');
        $this->Cell(0, $this->cellDescriptionSize, $this->_('Autenticação mecânica'), 0, 1, 'R');
        $this->Ln(2);

        $pulaLinha = 26;

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        if (count($this->slip[$i]->getDemonstrative()) > 0) {
            $pulaLinha = $this->listLines($this->slip[$i]->getDemonstrative(), $pulaLinha);
        }

        $this->traco('Corte na linha pontilhada', $pulaLinha, 10);

        return $this;
    }

    /**
     * Gera a pate do boleto que é a via do banco
     *
     * @param integer $i
     * @return Pdf
     */
    protected function bankVia(int $i): Pdf
    {
        $this->Image($this->slip[$i]->getBankLogo(), 20, ($this->GetY() - 2), 28);
        $this->Cell(29, 6, '', 'B');
        $this->SetFont($this->fontDefault, 'B', 13);
        $this->Cell(15, 6, $this->slip[$i]->getBankCodeWithCheckDigit(), 'LBR', 0, 'C');
        $this->SetFont($this->fontDefault, 'B', 10);
        $this->Cell(0, 6, $this->slip[$i]->getDigitableLine(), 'B', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(120, $this->cellDescriptionSize, $this->_('Local de pagamento'), 'TLR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('Vencimento'), 'TR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(120, $this->cellDataSize, $this->_($this->slip[$i]->getPaymentPlace()), 'LR');
        $this->Cell(50, $this->cellDataSize, $this->_($this->slip[$i]->getDueDate()->format('d/m/Y')), 'R', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(120, $this->cellDescriptionSize, $this->_('Beneficiário'), 'TLR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('Agência/Código beneficiário'), 'TR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(120, $this->cellDataSize, $this->_($this->slip[$i]->getBeneficiary()->getNameAndDocument()), 'LR');
        $this->Cell(50, $this->cellDataSize, $this->_($this->slip[$i]->getAgencyAndAccount()), 'R', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(30, $this->cellDescriptionSize, $this->_('Data do documento'), 'TLR');
        $this->Cell(40, $this->cellDescriptionSize, $this->_('Número do documento'), 'TR');
        $this->Cell(15, $this->cellDescriptionSize, $this->_('Espécie Doc.'), 'TR');
        $this->Cell(10, $this->cellDescriptionSize, $this->_('Aceite'), 'TR');
        $this->Cell(25, $this->cellDescriptionSize, $this->_('Data processamento'), 'TR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('Nosso número'), 'TR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(30, $this->cellDataSize, $this->_($this->slip[$i]->getDocumentDate()->format('d/m/Y')), 'LR');
        $this->Cell(40, $this->cellDataSize, $this->_($this->slip[$i]->getDocumentNumber()), 'R');
        $this->Cell(15, $this->cellDataSize, $this->_($this->slip[$i]->getDocumentType()), 'R');
        $this->Cell(10, $this->cellDataSize, $this->_($this->slip[$i]->getAcceptance()), 'R');
        $this->Cell(25, $this->cellDataSize, $this->_($this->slip[$i]->getProcessingDate()->format('d/m/Y')), 'R');
        $this->Cell(50, $this->cellDataSize, $this->_($this->slip[$i]->getOurNumberCustom()), 'R', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);

        if (isset($this->slip[$i]->additionalVariables['esconde_uso_banco']) && $this->slip[$i]->additionalVariables['esconde_uso_banco']) {
            $this->Cell(55, $this->cellDescriptionSize, $this->_('Carteira'), 'TLR');
        } else {
            $cip = isset($this->slip[$i]->additionalVariables['mostra_cip']) && $this->slip[$i]->additionalVariables['mostra_cip'];

            $this->Cell(($cip ? 23 : 30), $this->cellDescriptionSize, $this->_('Uso do Banks'), 'TLR');
            if ($cip) {
                $this->Cell(7, $this->cellDescriptionSize, $this->_('CIP'), 'TLR');
            }
            $this->Cell(25, $this->cellDescriptionSize, $this->_('Carteira'), 'TR');
        }

        $this->Cell(12, $this->cellDescriptionSize, $this->_('Espécie'), 'TR');
        $this->Cell(28, $this->cellDescriptionSize, $this->_('Quantidade'), 'TR');
        $this->Cell(25, $this->cellDescriptionSize, $this->_('Valor Documento'), 'TR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('Valor Documento'), 'TR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);

        if (isset($this->slip[$i]->additionalVariables['esconde_uso_banco']) && $this->slip[$i]->additionalVariables['esconde_uso_banco']) {
            $this->TextFitCell(55, $this->cellDataSize, $this->_($this->slip[$i]->getWalletName()), 'LR', 0, 'L');
        } else {
            $cip = isset($this->slip[$i]->additionalVariables['mostra_cip']) && $this->slip[$i]->additionalVariables['mostra_cip'];
            $this->Cell(($cip ? 23 : 30), $this->cellDataSize, $this->_(''), 'LR');
            if ($cip) {
                $this->Cell(7, $this->cellDataSize, $this->_($this->slip[$i]->getCip()), 'LR');
            }
            $this->Cell(25, $this->cellDataSize, $this->_(strtoupper($this->slip[$i]->getWalletName())), 'R');
        }

        $this->Cell(12, $this->cellDataSize, $this->_('R$'), 'R');
        $this->Cell(28, $this->cellDataSize, $this->_(''), 'R');
        $this->Cell(25, $this->cellDataSize, $this->_(''), 'R');
        $this->Cell(50, $this->cellDataSize, $this->_(Util::numberInReal($this->slip[$i]->getValue())), 'R', 1, 'R');

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(120, $this->cellDescriptionSize, $this->_("Instruções de responsabilidade do beneficiário. Qualquer dúvida sobre este boleto, contate o beneficiário"), 'TLR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('(-) Desconto / Abatimentos)'), 'TR', 1);

        $xInstructions = $this->GetX();
        $yInstructions = $this->GetY();

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(120, $this->cellDataSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDataSize, $this->_(''), 'R', 1);

        $this->Cell(120, $this->cellDescriptionSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('(-) Outras deduções'), 'TR', 1);

        $this->Cell(120, $this->cellDataSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDataSize, $this->_(''), 'R', 1);

        $this->Cell(120, $this->cellDescriptionSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('(+) Mora / Multa'), 'TR', 1);

        $this->Cell(120, $this->cellDataSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDataSize, $this->_(''), 'R', 1);

        $this->Cell(120, $this->cellDescriptionSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('(+) Outros acréscimos'), 'TR', 1);

        $this->Cell(120, $this->cellDataSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDataSize, $this->_(''), 'R', 1);

        $this->Cell(120, $this->cellDescriptionSize, $this->_(''), 'LR');
        $this->Cell(50, $this->cellDescriptionSize, $this->_('(=) Valor cobrado'), 'TR', 1);

        $this->Cell(120, $this->cellDataSize, $this->_(''), 'BLR');
        $this->Cell(50, $this->cellDataSize, $this->_(''), 'BR', 1);

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(0, $this->cellDescriptionSize, $this->_('Pagador'), 'LR', 1);

        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(0, $this->cellDataSize, $this->_($this->slip[$i]->getPayer()->getNameAndDocument()), 'LR', 1);
        $this->Cell(0, $this->cellDataSize, $this->_(trim($this->slip[$i]->getPayer()->getAddress() . ' - ' . $this->slip[$i]->getPayer()->getAddressDistrict()), ' -'), 'LR', 1);
        $this->Cell(0, $this->cellDataSize, $this->_($this->slip[$i]->getPayer()->getPostalCodeCityAndStateUf()), 'LR', 1);

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(120, $this->cellDataSize, $this->_(''), 'BLR');
        $this->Cell(12, $this->cellDataSize, $this->_('Cód. Baixa'), 'B');
        $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);
        $this->Cell(38, $this->cellDataSize, $this->_(''), 'BR', 1);

        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        $this->Cell(20, $this->cellDescriptionSize, $this->_('Sacador/Avalista'), 0);
        $this->Cell(98, $this->cellDescriptionSize, $this->_($this->slip[$i]->getGuarantor() ? $this->slip[$i]->getGuarantor()->getNameAndDocument() : ''), 0);
        $this->Cell(52, $this->cellDescriptionSize, $this->_('Autenticação mecânica - Ficha de Compensação'), 0, 1);

        $xOriginal = $this->GetX();
        $yOriginal = $this->GetY();

        if (count($this->slip[$i]->getInstructions()) > 0) {
            $this->SetXY($xInstructions, $yInstructions);
            $this->Ln(1);
            $this->SetFont($this->fontDefault, 'B', $this->fontCellSize);

            $this->listLines($this->slip[$i]->getInstructions(), 0);

            $this->SetXY($xOriginal, $yOriginal);
        }
        return $this;
    }

    /**
     * @param string $texto
     * @param integer $ln
     * @param integer $ln2
     */
    protected function traco(string $texto, int $ln = null, int $ln2 = null)
    {
        if ($ln == 1 || $ln) {
            $this->Ln($ln);
        }
        $this->SetFont($this->fontDefault, '', $this->fontDescriptionSize);
        if ($texto) {
            $this->Cell(0, 2, $this->_($texto), 0, 1, 'R');
        }
        $this->Cell(0, 2, str_pad('-', '261', ' -', STR_PAD_RIGHT), 0, 1);
        if ($ln2 == 1 || $ln2) {
            $this->Ln($ln2);
        }
    }

    /**
     * @param integer $i
     * @throws Exception
     */
    protected function barCode(int $i)
    {
        $this->Ln(3);
        $this->Cell(0, 15, '', 0, 1, 'L');
        $this->i25($this->GetX(), $this->GetY() - 15, $this->slip[$i]->getBarCode(), 0.8, 17);
    }

    /**
     * Adiciona o boletos
     *
     * @param SlipInterface[] $slips
     * @return Pdf
     */
    public function addSlips(array $slips): Pdf
    {
        $this->StartPageGroup();

        foreach ($slips as $slip) {
            $this->addSlip($slip);
        }

        return $this;
    }

    /**
     * Adiciona o boleto
     *
     * @param SlipInterface $slip
     * @return Pdf
     */
    public function addSlip(SlipInterface $slip): Pdf
    {
        $this->slipsTotal += 1;
        $this->slip[] = $slip;
        return $this;
    }

    /**
     * Mostra instruções de impressão
     *
     * @param bool $show
     * @return Pdf
     */
    public function showInstructions(bool $show): Pdf
    {
        $this->showInstructions = $show;
        return $this;
    }

    /**
     * Mostra recibo de entrega no boleto
     *
     * @param bool $show
     * @return Pdf
     */
    public function showReceipt(bool $show): Pdf
    {
        $this->showReceipt = $show;
        return $this;
    }

    /**
     * @param bool $show
     * @return $this
     */
    public function showPrint(bool $show): Pdf
    {
        $this->print = $show;
        return $this;
    }

    /**
     * função para gerar o boleto
     *
     * @param string $destination tipo de destino const OUTPUT_STANDARD | OUTPUT_SAVE | OUTPUT_DOWNLOAD | OUTPUT_STRING
     * @param string $savePath
     *
     * @return string
     * @throws Exception
     */
    public function generateSlip(string $destination = self::OUTPUT_STANDARD, string $savePath = null): string
    {
        if ($this->slipsTotal == 0) {
            throw new Exception('Nenhum boleto adicionado');
        }

        for ($i = 0; $i < $this->slipsTotal; $i++) {
            $this->SetDrawColor('0', '0', '0');
            $this->AddPage();
            $this->instructions($i)->receipt($i)->companyLogo($i)->beneficiaryVia($i)->bankVia($i)->barCode($i);
        }
        if ($destination == self::OUTPUT_SAVE) {
            $this->Output($savePath, $destination, $this->print);
            return $savePath;
        }
        return $this->Output(uniqid("boleto_") . '.pdf', $destination, $this->print);
    }

    /**
     * Gera o boleto e retorna no formato stream
     *
     * @return StreamInterface
     * @throws Exception
     */
    public function generateStreamSlip(): StreamInterface
    {
        $pdf = $this->generateSlip(self::OUTPUT_STANDARD, null);
        $stream = stream_for($pdf);

        return $stream;
    }

    /**
     * @param $lista
     * @param integer $pulaLinha
     * @return int
     */
    private function listLines(array $lista, $pulaLinha): int
    {
        foreach ($lista as $d) {
            $pulaLinha -= 2;
            $this->Cell(0, $this->cellDataSize - 0.2, $this->_(preg_replace('/(%)/', '%$1', $d)), 0, 1);
        }

        return $pulaLinha;
    }
}
