<?php

namespace PhpBoleto\Interfaces\Cnab\Returns\Cnab240;

interface TrailerLote
{
    /**
     * @return mixed
     */
    public function getServiceLot();

    /**
     * @return mixed
     */
    public function getEntryWarningNumber();

    /**
     * @return mixed
     */
    public function getRegistryLotAmount();

    /**
     * @return mixed
     */
    public function getSecuredChargeAmount();

    /**
     * @return mixed
     */
    public function getDiscountedChargeAmount();

    /**
     * @return mixed
     */
    public function getSimpleChargeAmount();

    /**
     * @return mixed
     */
    public function getAttachedChargeAmount();

    /**
     * @return mixed
     */
    public function getRegistryType();

    /**
     * @return mixed
     */
    public function getSimpleChargeTotalAmount();

    /**
     * @return mixed
     */
    public function getSecuredChargeTotalAmount();

    /**
     * @return mixed
     */
    public function getDiscountedChargeTotalAmount();

    /**
     * @return mixed
     */
    public function getAttachedChargeTotalAmount();

    /**
     * @return array
     */
    public function toArray();
}
