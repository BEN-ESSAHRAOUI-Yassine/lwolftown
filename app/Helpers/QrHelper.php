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
            'scale' => 8,
            'outputBase64' => false,
            'addQuietzone' => true,
        ]);

        $svg = (new QRCode($options))->render($data);
        $svg = str_replace('<svg ', '<svg width="250" height="250" ', $svg);
        return $svg;
    }
}
