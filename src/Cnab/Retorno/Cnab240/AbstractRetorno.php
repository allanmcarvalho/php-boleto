<?php

namespace PhpBoleto\Cnab\Retorno\Cnab240;

use \PhpBoleto\Cnab\Retorno\AbstractRetorno as AbstractRetornoGeneric;
use PhpBoleto\Contracts\Cnab\Retorno\Cnab240\HeaderLote as HeaderLoteContract;
use PhpBoleto\Contracts\Cnab\Retorno\Cnab240\TrailerLote as TrailerLoteContract;

/**
 * Class AbstractRetorno
 *
 * @method  \PhpBoleto\Cnab\Retorno\Cnab240\Detalhe getDetalhe()
 * @method  \PhpBoleto\Cnab\Retorno\Cnab240\Header getHeader()
 * @method  \PhpBoleto\Cnab\Retorno\Cnab240\Trailer getTrailer()
 * @method  \PhpBoleto\Cnab\Retorno\Cnab240\Detalhe detalheAtual()
 * @package PhpBoleto\Cnab\Retorno\Cnab240
 */
abstract class AbstractRetorno extends AbstractRetornoGeneric
{
    /**
     * @var HeaderLoteContract
     */
    private $headerLote;

    /**
     * @var TrailerLoteContract
     */
    private $trailerLote;

    /**
     * @param String $file
     * @throws \Exception
     */
    public function __construct($file)
    {
        parent::__construct($file);

        $this->header = new Header();
        $this->headerLote = new HeaderLote();
        $this->trailerLote = new TrailerLote();
        $this->trailer = new Trailer();
    }

    /**
     * @return HeaderLote
     */
    public function getHeaderLote()
    {
        return $this->headerLote;
    }

    /**
     * @return TrailerLote
     */
    public function getTrailerLote()
    {
        return $this->trailerLote;
    }

    /**
     * @param array $header
     *
     * @return boolean
     */
    abstract protected function processarHeader(array $header);

    /**
     * @param array $headerLote
     *
     * @return boolean
     */
    abstract protected function processarHeaderLote(array $headerLote);

    /**
     * @param array $detalhe
     *
     * @return boolean
     */
    abstract protected function processarDetalhe(array $detalhe);

    /**
     * @param array $trailer
     *
     * @return boolean
     */
    abstract protected function processarTrailerLote(array $trailer);

    /**
     * @param array $trailer
     *
     * @return boolean
     */
    abstract protected function processarTrailer(array $trailer);

    /**
     * Incrementa o detalhe.
     */
    protected function incrementDetalhe()
    {
        $this->increment++;
        $detalhe = new Detalhe();
        $this->detalhe[$this->increment] = $detalhe;
    }

    /**
     * Processa o arquivo
     *
     * @return $this
     * @throws \Exception
     */
    public function processar()
    {
        if ($this->isProcessado()) {
            return $this;
        }

        if (method_exists($this, 'init')) {
            call_user_func([$this, 'init']);
        }

        foreach ($this->file as $linha) {
            $recordType = $this->rem(8, 8, $linha);

            if ($recordType == '0') {
                $this->processarHeader($linha);
            } elseif ($recordType == '1') {
                $this->processarHeaderLote($linha);
            } elseif ($recordType == '3') {
                if ($this->getSegmentType($linha) == 'T') {
                    $this->incrementDetalhe();
                }

                if ($this->processarDetalhe($linha) === false) {
                    unset($this->detalhe[$this->increment]);
                    $this->increment--;
                }
            } elseif ($recordType == '5') {
                $this->processarTrailerLote($linha);
            } elseif ($recordType == '9') {
                $this->processarTrailer($linha);
            }
        }

        if (method_exists($this, 'finalize')) {
            call_user_func([$this, 'finalize']);
        }

        return $this->setProcessado();
    }

    /**
     * Retorna o array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [
            'header' => $this->header->toArray(),
            'headerLote' => $this->headerLote->toArray(),
            'trailerLote' => $this->trailerLote->toArray(),
            'trailer' => $this->trailer->toArray(),
            'detalhes' => new Collection()
        ];

        foreach ($this->detalhe as $detalhe) {
            $arr = [
                'ocorrenciaTipo' => $detalhe->getOcorrenciaTipo(),
                'ocorrenciaDescricao' => $detalhe->getOcorrenciaDescricao(),
                'segmentoT' => $detalhe->getSegmentoT()->toArray(),
                'segmentoU' => $detalhe->getSegmentoU()->toArray(),
                'segmentoY' => $detalhe->getSegmentoY()->toArray(),
            ];

            if ($detalhe->getOcorrenciaTipo() == 9) {
                $arr['error'] = [
                    'message' => $detalhe->getError(),
                    'code' => $detalhe->getErrorCode(),
                ];
            }

            $array['detalhes']->push($arr);
        }
        return $array;
    }

    protected function getSegmentType($line)
    {
        return strtoupper($this->rem(14, 14, $line));
    }
}
