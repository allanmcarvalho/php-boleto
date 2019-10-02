<?php

namespace PhpBoleto\Interfaces\Cnab\Returns\Cnab240;

interface HeaderLote
{
    /**
     * @return mixed
     */
    public function getRegistryType();

    /**
     * @return mixed
     */
    public function getOperationType();

    /**
     * @return mixed
     */
    public function getServiceType();

    /**
     * @return mixed
     */
    public function getLayoutFileVersion();

    /**
     * @return mixed
     */
    public function getSubscriptionType();

    /**
     * @return mixed
     */
    public function getSubscriptionNumber();

    /**
     * @return mixed
     */
    public function getAssignorCode();

    /**
     * @return mixed
     */
    public function getCovenant();

    /**
     * @return mixed
     */
    public function getCompanySocialName();

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
     * @return string
     */
    public function getReturnNumber();

    /**
     * @return mixed
     */
    public function getAccountCheckDigit();

    /**
     * @param string $format
     *
     * @return string
     */
    public function getWritingDate($format = 'd/m/Y');

    /**
     * @param string $format
     *
     * @return string
     */
    public function getCreditDate($format = 'd/m/Y');

    /**
     * @return array
     */
    public function toArray();
}
