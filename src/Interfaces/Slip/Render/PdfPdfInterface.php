<?php

namespace PhpBoleto\Interfaces\Slip\Render;

use Psr\Http\Message\StreamInterface;

interface PdfPdfInterface
{
    public function generateSlip(string $destination = self::OUTPUT_STANDARD, string $savePath = null);

    public function generateStreamSlip(): StreamInterface;
}
