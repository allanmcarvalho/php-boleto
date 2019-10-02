<?php

namespace PhpBoleto\Interfaces\Cnab;

use PhpBoleto\Interfaces\Cnab\Returns\Cnab400\DetailInterface;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab400\Header;
use PhpBoleto\Interfaces\Cnab\Returns\Cnab400\Trailer;
use PhpBoleto\Support\Collection;

interface ReturnCnab400Interface extends CnabInterface
{
    /**
     * @return mixed
     */
    public function getBankCode();

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
