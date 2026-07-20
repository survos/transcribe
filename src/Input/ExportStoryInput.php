<?php

declare(strict_types=1);

namespace App\Input;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;

final class ExportStoryInput
{
    #[Argument('YouTube video ID')]
    public string $videoId;

    #[Option('Clip start time in seconds')]
    public float $start = 0.0;

    #[Option('Clip end time in seconds; defaults to the last matching segment')]
    public ?float $end = null;

    #[Option('Output directory for story JSON and captions')]
    public string $outputDir = 'var/export/youtube';

    #[Option('Story block kind')]
    public string $kind = 'interviewClip';
}
