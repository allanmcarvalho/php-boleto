<?php

namespace PhpBoleto\Interfaces\Person;

interface PersonInterface
{
    public function getName(): string;

    public function getNameAndDocument(): string;

    public function getDocument(): string;

    public function getAddressDistrict(): string;

    public function getAddress(): string;

    public function getPostalCodeCityAndStateUf(): string;

    public function getPostalCode(): string;

    public function getCity(): string;

    public function getStateUf(): string;

    public function toArray(): array;
}
