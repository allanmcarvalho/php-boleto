<?php

namespace PhpBoleto\Interfaces\Cnab\Returns\Cnab240;

interface Trailer
{
    /**
     * @return mixed
     */
    public function getRegistryType();

    /**
     * @return mixed
     */
    public function getRemittanceLotNumber();

    /**
     * @return mixed
     */
    public function getFileLotAmount();

    /**
     * @return mixed
     */
    public function getFileRecordAmount();

    /**
     * @return array
     */
    public function toArray();
}
