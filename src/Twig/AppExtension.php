<?php

namespace App\Twig;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('tc', [$this, 'timecodeFormat']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('tc', [$this, 'timecodeFormat']),
        ];
    }

    public function timecodeFormat($value)
    {
        // return $value;
        return TimeCode::fromSeconds($value);
    }
}
