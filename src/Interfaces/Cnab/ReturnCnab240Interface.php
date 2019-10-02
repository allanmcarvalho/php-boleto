<?php

namespace PhpBoleto\Interfaces\Cnab;

use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\DetailInterface;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\Header;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\HeaderLote;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\Trailer;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab240\TrailerLote;
use PhpBoleto\Support\Collection;

interface ReturnCnab240Interface extends CnabInterface
{
    /**
     * @return mixed
     */
    public function getBarCode();

    /**
     * @return mixed
     */
    public function getBankName();

    /**
     * @return Collection
     */
    public function getDetails();

    /**
     * @param $i
     * @return DetailInterface
     */
    public function getDetail($i);

    /**
     * @return Header
     */
    public function getHeader();

    /**
     * @return HeaderLote
     */
    public function getHeaderLot();

    /**
     * @return TrailerLote
     */
    public function getTrailerLot();

    /**
     * @return Trailer
     */
    public function getTrailer();

    /**
     * @return string
     */
    public function process();

    /**
     * @return array
     */
    public function toArray();
}
