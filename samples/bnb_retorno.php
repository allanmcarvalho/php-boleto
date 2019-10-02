<?php

use PhpBoleto\Cnab\Returns\Factory;

require 'autoload.php';
$retorno = Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'bnb.ret');
$retorno->processar();

echo $retorno->getBancoNome();
dd($retorno->getDetalhes());
