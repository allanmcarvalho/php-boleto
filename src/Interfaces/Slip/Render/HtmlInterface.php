<?php
namespace PhpBoleto\Interfaces\Slip\Render;

interface HtmlInterface
{
    public function getBarCodeImage($barCode);

    public function generateSlip();
}
