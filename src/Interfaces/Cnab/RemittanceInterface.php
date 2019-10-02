<?php
namespace PhpBoleto\Interfaces\Cnab;

interface RemittanceInterface extends CnabInterface
{
    public function generate();
}
