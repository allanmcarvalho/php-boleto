<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400;

use Exception;
use PhpBoleto\Cnab\Remittances\GenericRemittanceAbstract;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class GenericRemittanceAbstract
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400
 */
abstract class RemittanceAbstract extends GenericRemittanceAbstract
{
    protected $lineSize = 400;

    /**
     * Inicia a edição do header
     */
    protected function initiateHeader()
    {
        $this->registryArray[self::HEADER] = array_fill(0, 400, ' ');
        $this->linePointer = &$this->registryArray[self::HEADER];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function InitiateTrailer()
    {
        $this->registryArray[self::TRAILER] = array_fill(0, 400, ' ');
        $this->linePointer = &$this->registryArray[self::TRAILER];
    }

    /**
     * Inicia uma nova linha de detalhe e marca com a atual de edição
     */
    protected function initiateDetail()
    {
        $this->registryCount++;
        $this->registryArray[self::DETALHE][$this->registryCount] = array_fill(0, 400, ' ');
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

        foreach ($this->getDetails() as $i => $detalhe) {
            $stringRemessa .= $this->validate($detalhe) . $this->eolChar;
        }

        $this->trailer();
        $stringRemessa .= $this->validate($this->getTrailer()) . $this->endOfFileChar;

        return $stringRemessa;
    }

    public function generateStream(): StreamInterface
    {
        $stream = stream_for($this->generate());
        return $stream;
    }
}
