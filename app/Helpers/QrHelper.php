<?php

namespace App\Helpers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrHelper
{
    public static function generate(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
            'svgUseCssProperties' => false,
            'scale' => 1,
            'outputBase64' => false,
        ]);

        return (new QRCode($options))->render($data);
    }
}
