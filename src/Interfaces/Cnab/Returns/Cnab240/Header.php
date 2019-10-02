<?php

namespace PhpBoleto\Interfaces\Cnab\Returns\Cnab240;

interface Header
{
    /**
     * @return string
     */
    public function getServiceLot();

    /**
     * @return string
     */
    public function getRegistryType();

    /**
     * @return string
     */
    public function getSubscriptionType();

    /**
     * @return string
     */
    public function getAgency();

    /**
     * @return string
     */
    public function getAgencyCheckDigit();

    /**
     * @return string
     */
    public function getCompanySocialName();

    /**
     * @return string
     */
    public function getGenerationHour();

    /**
     * @return string
     */
    public function getSequentialFileNumber();

    /**
     * @return string
     */
    public function getLayoutFileVersion();

    /**
     * @return string
     */
    public function getSubscriptionNumber();

    /**
     * @return string
     */
    public function getAccount();

    /**
     * @return string
     */
    public function getAccountCheckDigit();

    /**
     * @return string
     */
    public function getAssignorCode();

    /**
     * @param string $format
     *
     * @return string
     */
    public function getDate($format = 'd/m/Y');

    /**
     * @return string
     */
    public function getCovenant();

    /**
     * @return int
     */
    public function getBankCode();

    /**
     * @return int
     */
    public function getRemittanceReturnCode();

    /**
     * @return string
     */
    public function getBankName();

    /**
     * @return array
     */
    public function toArray();
}
