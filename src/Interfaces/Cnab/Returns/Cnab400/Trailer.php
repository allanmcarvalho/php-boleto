<?php

namespace PhpBoleto\Interfaces\Cnab\Returns\Cnab400;

interface Trailer
{
    /**
     * @return mixed
     */
    public function getTitlesValue();

    /**
     * @return mixed
     */
    public function getWarnings();

    /**
     * @return mixed
     */
    public function getTitlesAmount();

    /**
     * @return mixed
     */
    public function getLiquidatedAmount();

    /**
     * @return mixed
     */
    public function getDropAmount();

    /**
     * @return mixed
     */
    public function getEnterAmount();

    /**
     * @return mixed
     */
    public function getChangedAmount();

    /**
     * @return mixed
     */
    public function getErrorsAmount();

    /**
     * @return array
     */
    public function toArray();
}
