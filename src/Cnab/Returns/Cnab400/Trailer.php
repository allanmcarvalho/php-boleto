<?php

namespace PhpBoleto\Cnab\Returns\Cnab400;

use PhpBoleto\Interfaces\Cnab\Returns\Cnab400\Trailer as TrailerContract;
use PhpBoleto\Traits\MagicTrait;

class Trailer implements TrailerContract
{
    use MagicTrait;
    /**
     * @var float
     */
    protected $valorTitulos;
    /**
     * @var int
     */
    protected $avisos = 0;
    /**
     * @var int
     */
    protected $quantidadeTitulos;
    /**
     * @var int
     */
    protected $quantidadeLiquidados = 0;
    /**
     * @var int
     */
    protected $quantidadeBaixados = 0;
    /**
     * @var int
     */
    protected $quantidadeEntradas = 0;
    /**
     * @var int
     */
    protected $quantidadeAlterados = 0;
    /**
     * @var int
     */
    protected $quantidadeErros = 0;

    /**
     * @return float
     */
    public function getTitlesValue()
    {
        return $this->valorTitulos;
    }

    /**
     * @param float $valorTitulos
     *
     * @return Trailer
     */
    public function setValorTitulos($valorTitulos)
    {
        $this->valorTitulos = $valorTitulos;

        return $this;
    }

    /**
     * @return int
     */
    public function getWarnings()
    {
        return $this->avisos;
    }

    /**
     * @param int $avisos
     *
     * @return Trailer
     */
    public function setAvisos($avisos)
    {
        $this->avisos = $avisos;

        return $this;
    }

    /**
     * @return int
     */
    public function getTitlesAmount()
    {
        return $this->quantidadeTitulos;
    }

    /**
     * @param int $quantidadeTitulos
     *
     * @return Trailer
     */
    public function setQuantidadeTitulos($quantidadeTitulos)
    {
        $this->quantidadeTitulos = $quantidadeTitulos;

        return $this;
    }

    /**
     * @return int
     */
    public function getLiquidatedAmount()
    {
        return $this->quantidadeLiquidados;
    }

    /**
     * @param int $quantidadeLiquidados
     *
     * @return Trailer
     */
    public function setQuantidadeLiquidados($quantidadeLiquidados)
    {
        $this->quantidadeLiquidados = $quantidadeLiquidados;

        return $this;
    }

    /**
     * @return int
     */
    public function getDropAmount()
    {
        return $this->quantidadeBaixados;
    }

    /**
     * @param int $quantidadeBaixados
     *
     * @return Trailer
     */
    public function setQuantidadeBaixados($quantidadeBaixados)
    {
        $this->quantidadeBaixados = $quantidadeBaixados;

        return $this;
    }

    /**
     * @return int
     */
    public function getEnterAmount()
    {
        return $this->quantidadeEntradas;
    }

    /**
     * @param int $quantidadeEntradas
     *
     * @return Trailer
     */
    public function setQuantidadeEntradas($quantidadeEntradas)
    {
        $this->quantidadeEntradas = $quantidadeEntradas;

        return $this;
    }

    /**
     * @return int
     */
    public function getChangedAmount()
    {
        return $this->quantidadeAlterados;
    }

    /**
     * @param int $quantidadeAlterados
     *
     * @return Trailer
     */
    public function setQuantidadeAlterados($quantidadeAlterados)
    {
        $this->quantidadeAlterados = $quantidadeAlterados;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorsAmount()
    {
        return $this->quantidadeErros;
    }

    /**
     * @param int $quantidadeErros
     *
     * @return Trailer
     */
    public function setQuantidadeErros($quantidadeErros)
    {
        $this->quantidadeErros = $quantidadeErros;

        return $this;
    }
}
