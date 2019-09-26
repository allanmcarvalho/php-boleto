<?php
namespace PhpBoleto\Contracts;

interface PersonInterface
{
    public function getName();
    public function getNameAndDocument();
    public function getDocument();
    public function getAddressDistrict();
    public function getAddress();
    public function getPostalCodeCityAndStateUf();
    public function getPostalCode();
    public function getCity();
    public function getStateUf();
    public function toArray();
}
