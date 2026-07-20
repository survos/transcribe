<?php

declare(strict_types=1);

namespace App\Enum;

enum TranscriptSource: string
{
    case YouTubeCaption = 'youtubeCaption';
    case YouTubeTranscript = 'youtubeTranscript';
    case SpeechToText = 'speechToText';
}
