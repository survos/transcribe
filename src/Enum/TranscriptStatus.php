<?php

declare(strict_types=1);

namespace App\Enum;

enum TranscriptStatus: string
{
    case Pending = 'pending';
    case Imported = 'imported';
    case NeedsTranscription = 'needsTranscription';
}
