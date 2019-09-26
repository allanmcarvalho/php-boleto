<?php
namespace PhpBoleto\Interfaces\Slip\Render;

interface Html
{
    public function getImagemCodigoDeBarras($codigo_barras);

    public function gerarBoleto();
}
