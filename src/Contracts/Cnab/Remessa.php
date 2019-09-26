<?php
namespace PhpBoleto\Contracts\Cnab;

interface Remessa extends Cnab
{
    public function gerar();
}
