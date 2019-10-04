<?php

namespace PhpBoleto\Cnab\Returns;

use Countable;
use Exception;
use OutOfBoundsException;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\DetailInterface as Detalhe240Contract;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\Header as Header240Contract;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\Trailer as Trailer240Contract;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab400\DetailInterface as Detalhe400Contract;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab400\Header as Header400Contract;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab400\Trailer as Trailer400Contract;
use PhpBoleto\Support\Collection;
use PhpBoleto\Util;
use ReflectionClass;
use SeekableIterator;

abstract class AbstractRetorno implements Countable, SeekableIterator
{
    /**
     * Se CnabInterface ja foi processado
     *
     * @var bool
     */
    protected $processado = false;

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco;

    /**
     * Incremeto de detalhes
     *
     * @var int
     */
    protected $increment = 0;

    /**
     * Archive transformado em array por linha.
     *
     * @var array
     */
    protected $file;

    /**
     * @var Header240Contract|Header400Contract
     */
    protected $header;

    /**
     * @var Trailer240Contract|Trailer400Contract
     */
    protected $trailer;

    /**
     * @var Detalhe240Contract[]|Detalhe400Contract[]
     */
    protected $detalhe = [];

    /**
     * Helper de totais.
     *
     * @var array
     */
    protected $totais = [];

    /**
     * @var int
     */
    private $_position = 1;

    /**
     *
     * @param String $file
     * @throws Exception
     */
    public function __construct($file)
    {
        $this->_position = 1;

        if (!$this->file = Util::file2array($file)) {
            throw new Exception("Archive: não existe");
        }

        $r = new ReflectionClass('\PhpBoleto\Contracts\Boleto\SlipInterface');
        $constantNames = $r->getConstants();
        $bancosDisponiveis = [];
        foreach ($constantNames as $constantName => $codigoBanco) {
            if (preg_match('/^COD_BANCO.*/', $constantName)) {
                $bancosDisponiveis[] = $codigoBanco;
            }
        }

        if (!Util::isHeaderRetorno($this->file[0])) {
            throw new Exception(sprintf("Archive de retorno inválido"));
        }

        $banco = Util::isCnab400($this->file[0]) ? substr($this->file[0], 76, 3) : substr($this->file[0], 0, 3);
        if (!in_array($banco, $bancosDisponiveis)) {
            throw new Exception(sprintf("Banks: %s, inválido", $banco));
        }
    }

    /**
     * Retorna o código do banco
     *
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * @return mixed
     */
    public function getBancoNome()
    {
        return Util::$bancos[$this->codigoBanco];
    }

    /**
     * @return Collection
     */
    public function getDetalhes()
    {
        return new Collection($this->detalhe);
    }

    /**
     * @param $i
     *
     * @return Detalhe240Contract[]|Detalhe400Contract[]
     */
    public function getDetalhe($i)
    {
        return array_key_exists($i, $this->detalhe) ? $this->detalhe[$i] : null;
    }

    /**
     * @return Header240Contract|Header400Contract
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return Trailer240Contract|Trailer400Contract
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    /**
     * Retorna o detalhe atual.
     *
     * @return Detalhe240Contract|Detalhe400Contract
     */
    protected function detalheAtual()
    {
        return $this->detalhe[$this->increment];
    }

    /**
     * Se esta processado
     *
     * @return bool
     */
    protected function isProcessado()
    {
        return $this->processado;
    }

    /**
     * Seta cnab como processado
     *
     * @return $this
     */
    protected function setProcessado()
    {
        $this->processado = true;
        return $this;
    }

    /**
     * Incrementa o detalhe.
     */
    abstract protected function incrementDetalhe();

    /**
     * Processa o arquivo
     *
     * @return $this
     */
    abstract protected function processar();

    /**
     * Retorna o array.
     *
     * @return array
     */
    abstract protected function toArray();

    /**
     * Remove trecho do array.
     *
     * @param $i
     * @param $f
     * @param $array
     *
     * @return string
     * @throws Exception
     */
    protected function rem($i, $f, &$array)
    {
        return Util::remove($i, $f, $array);
    }


    public function current()
    {
        return $this->detalhe[$this->_position];
    }

    public function next()
    {
        ++$this->_position;
    }

    public function key()
    {
        return $this->_position;
    }

    public function valid()
    {
        return isset($this->detalhe[$this->_position]);
    }

    public function rewind()
    {
        $this->_position = 1;
    }

    public function count()
    {
        return count($this->detalhe);
    }

    public function seek($position)
    {
        $this->_position = $position;
        if (!$this->valid()) {
            throw new OutOfBoundsException('"Posição inválida "$position"');
        }
    }
}
