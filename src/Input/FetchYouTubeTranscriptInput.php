<?php

declare(strict_types=1);

namespace App\Input;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;

final class FetchYouTubeTranscriptInput
{
    #[Argument('YouTube video ID or watch URL')]
    public string $video;

    #[Option('Preferred subtitle language, e.g. es, en, or es,en')]
    public ?string $language = null;

    #[Option('Video title to store when acquisition metadata is not available')]
    public ?string $title = null;

    #[Option('Optional .env file containing YOUTUBE_API_KEY for metadata enrichment')]
    public ?string $apiKeyFile = '/home/tac/sites/kpa/.env.local';

    #[Option('Mark missing captions as needsTranscription instead of failing')]
    public bool $allowMissing = true;
}
