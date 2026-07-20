<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TranscriptSource;
use App\Repository\TranscriptSegmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranscriptSegmentRepository::class)]
#[ORM\Table(name: 'transcript_segment')]
#[ORM\Index(columns: ['video_source_id', 'start_ms'], name: 'transcript_segment_video_start_idx')]
final class TranscriptSegment
{
    #[ORM\Id]
    #[ORM\Column(length: 96)]
    public readonly string $id;

    #[ORM\ManyToOne(inversedBy: 'segments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public VideoSource $videoSource;

    #[ORM\Column(length: 64)]
    public string $videoId;

    #[ORM\Column]
    public int $startMs;

    #[ORM\Column]
    public int $durationMs;

    #[ORM\Column(type: Types::TEXT)]
    public string $text;

    #[ORM\Column(length: 16, nullable: true)]
    public ?string $language = null;

    #[ORM\Column(enumType: TranscriptSource::class)]
    public TranscriptSource $source = TranscriptSource::YouTubeTranscript;

    public function __construct(VideoSource $videoSource, int $startMs)
    {
        $this->videoSource = $videoSource;
        $this->videoId = $videoSource->id;
        $this->startMs = $startMs;
        $this->durationMs = 0;
        $this->text = '';
        $this->id = sprintf('%s:%d', $videoSource->id, $startMs);
    }
}
