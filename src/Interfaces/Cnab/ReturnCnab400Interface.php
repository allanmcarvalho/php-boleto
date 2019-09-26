<?php

namespace PhpBoleto\Interfaces\Cnab;

interface ReturnCnab400Interface extends CnabInterface
{
    /**
     * @return mixed
     */
    public function getCodigoBanco();

    /**
     * @return mixed
     */
    public function getBancoNome();

    /**
     * @return Collection
     */
    public function getDetalhes();

    /**
     * @return Retorno\Cnab400\Detalhe
     */
    public function getDetalhe($i);

    /**
     * @return Retorno\Cnab400\Header
     */
    public function getHeader();

    /**
     * @return Retorno\Cnab400\Trailer
     */
    public function getTrailer();

    /**
     * @return string
     */
    public function processar();

    /**
     * @return array
     */
    public function toArray();
}
