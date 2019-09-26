<?php
namespace PhpBoleto\Interfaces\Slip\Render;

interface Pdf
{
    public function gerarBoleto($dest = self::OUTPUT_STANDARD, $save_path = null);
}
