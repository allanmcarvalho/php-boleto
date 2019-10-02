<?php

namespace PhpBoleto\Interfaces\Cnab\Returns\Cnab400;

use DateTimeInterface;

interface Header
{
    /**
     * @return mixed
     */
    public function getOperationCode();

    /**
     * @return mixed
     */
    public function getOperation();

    /**
     * @return mixed
     */
    public function getServiceCode();

    /**
     * @return mixed
     */
    public function getService();

    /**
     * @return mixed
     */
    public function getAgency();

    /**
     * @return mixed
     */
    public function getAgencyCheckDigit();

    /**
     * @return mixed
     */
    public function getAccount();

    /**
     * @return mixed
     */
    public function getAccountCheckDigit();

    /**
     * @param string $format
     *
     * @return DateTimeInterface
     */
    public function getDate($format = 'd/m/Y');

    /**
     * @return mixed
     */
    public function getCovenant();

    /**
     * @return mixed
     */
    public function getClientCode();

    /**
     * @return array
     */
    public function toArray();
}
