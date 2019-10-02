<?php

namespace PhpBoleto\Cnab\Returns\Cnab240;

use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\Trailer as TrailerContract;
use PhpBoleto\Traits\MagicTrait;

class Trailer implements TrailerContract
{
    use MagicTrait;
    /**
     * @var integer
     */
    protected $numeroLote;

    /**
     * @var integer
     */
    protected $tipoRegistro;

    /**
     * @var integer
     */
    protected $qtdLotesArquivo;

    /**
     * @var integer
     */
    protected $qtdRegistroArquivo;

    /**
     * @return mixed
     */
    public function getRegistryType()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param mixed $numeroLote
     *
     * @return $this
     */
    public function setNumeroLote($numeroLote)
    {
        $this->numeroLote = $numeroLote;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemittanceLotNumber()
    {
        return $this->numeroLote;
    }

    /**
     * @param mixed $qtdLotesArquivo
     *
     * @return $this
     */
    public function setQtdLotesArquivo($qtdLotesArquivo)
    {
        $this->qtdLotesArquivo = $qtdLotesArquivo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileLotAmount()
    {
        return $this->qtdLotesArquivo;
    }

    /**
     * @param mixed $qtdRegistroArquivo
     *
     * @return $this
     */
    public function setQtdRegistroArquivo($qtdRegistroArquivo)
    {
        $this->qtdRegistroArquivo = $qtdRegistroArquivo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileRecordAmount()
    {
        return $this->qtdRegistroArquivo;
    }

    /**
     * @param mixed $tipoRegistro
     *
     * @return $this
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }
}
