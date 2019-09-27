<?php
namespace PhpBoleto\Interfaces\Slip\Render;

interface Pdf
{
    public function generateSlip($destination = self::OUTPUT_STANDARD, $savePath = null);
}
