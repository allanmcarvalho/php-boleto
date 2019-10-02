<?php

namespace PhpBoleto\Cnab\Remittances\Cnab240;

use Exception;
use PhpBoleto\Cnab\Remittances\GenericRemittanceAbstract;

/**
 * Class GenericRemittanceAbstract
 * @package PhpBoleto\CnabInterface\Remessa\Cnab240
 */
abstract class RemittanceAbstract extends GenericRemittanceAbstract
{
    /**
     * @var int
     */
    protected $lineSize = 240;

    /**
     * Array contendo o cnab.
     *
     * @var array
     */
    protected $registryArray = [
        self::HEADER => [],
        self::HEADER_LOTE => [],
        self::DETALHE => [],
        self::TRAILER_LOTE => [],
        self::TRAILER => [],
    ];

    /**
     * Função para gerar o cabeçalho do arquivo.
     *
     * @return mixed
     */
    abstract protected function headerLote();


    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return mixed
     */
    abstract protected function trailerLote();

    /**
     * Retorna o header do lote.
     *
     * @return mixed
     */
    protected function getHeaderLote()
    {
        return $this->registryArray[self::HEADER_LOTE];
    }

    /**
     * Retorna o trailer do lote.
     *
     * @return mixed
     */
    protected function getTrailerLote()
    {
        return $this->registryArray[self::TRAILER_LOTE];
    }

    /**
     * Inicia a edição do header
     */
    protected function initiateHeader()
    {
        $this->registryArray[self::HEADER] = array_fill(0, 240, ' ');
        $this->linePointer = &$this->registryArray[self::HEADER];
    }

    /**
     * Inicia a edição do header
     */
    protected function initiateHeaderLot()
    {
        $this->registryArray[self::HEADER_LOTE] = array_fill(0, 240, ' ');
        $this->linePointer = &$this->registryArray[self::HEADER_LOTE];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function initiateTrailerLot()
    {
        $this->registryArray[self::TRAILER_LOTE] = array_fill(0, 240, ' ');
        $this->linePointer = &$this->registryArray[self::TRAILER_LOTE];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function initiateTrailer()
    {
        $this->registryArray[self::TRAILER] = array_fill(0, 240, ' ');
        $this->linePointer = &$this->registryArray[self::TRAILER];
    }

    /**
     * Inicia uma nova linha de detalhe e marca com a atual de edição
     */
    protected function initiateDetail()
    {
        $this->registryCount++;
        $this->registryArray[self::DETALHE][$this->registryCount] = array_fill(0, 240, ' ');
        $this->linePointer = &$this->registryArray[self::DETALHE][$this->registryCount];
    }

    /**
     * Gera o arquivo, retorna a string.
     *
     * @return string
     * @throws Exception
     */
    public function generate()
    {
        if (!$this->isValid()) {
            throw new Exception('Campos requeridos pelo banco, aparentam estar ausentes');
        }

        $stringRemessa = '';
        if ($this->registryCount < 1) {
            throw new Exception('Nenhuma linha detalhe foi adicionada');
        }

        $this->header();
        $stringRemessa .= $this->validate($this->getHeader()) . $this->eolChar;

        $this->headerLote();
        $stringRemessa .= $this->validate($this->getHeaderLote()) . $this->eolChar;

        foreach ($this->getDetails() as $i => $detalhe) {
            $stringRemessa .= $this->validate($detalhe) . $this->eolChar;
        }

        $this->trailerLote();
        $stringRemessa .= $this->validate($this->getTrailerLote()) . $this->eolChar;

        $this->trailer();
        $stringRemessa .= $this->validate($this->getTrailer()) . $this->endOfFileChar;

        return $stringRemessa;
    }
}
