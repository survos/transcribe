<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TranscriptStatus;
use App\Enum\VideoProvider;
use App\Repository\VideoSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoSourceRepository::class)]
#[ORM\Table(name: 'video_source')]
#[ORM\Index(columns: ['provider', 'channel_id'], name: 'video_source_channel_idx')]
#[ORM\Index(columns: ['transcript_status'], name: 'video_source_transcript_status_idx')]
final class VideoSource implements \Stringable
{
    #[ORM\Id]
    #[ORM\Column(length: 64)]
    public readonly string $id;

    #[ORM\Column(enumType: VideoProvider::class)]
    public VideoProvider $provider = VideoProvider::YouTube;

    #[ORM\Column(length: 512)]
    public string $sourceUrl;

    #[ORM\Column(length: 128, nullable: true)]
    public ?string $channelHandle = null;

    #[ORM\Column(length: 128, nullable: true)]
    public ?string $channelId = null;

    #[ORM\Column(length: 512)]
    public string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(length: 512, nullable: true)]
    public ?string $thumbnailUrl = null;

    #[ORM\Column(nullable: true)]
    public ?int $duration = null;

    #[ORM\Column(nullable: true)]
    public ?array $rawMetadata = null;

    #[ORM\Column(enumType: TranscriptStatus::class)]
    public TranscriptStatus $transcriptStatus = TranscriptStatus::Pending;

    /** @var Collection<int, TranscriptSegment> */
    #[ORM\OneToMany(mappedBy: 'videoSource', targetEntity: TranscriptSegment::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['startMs' => 'ASC'])]
    public Collection $segments;

    public function __construct(string $videoId)
    {
        $this->id = $videoId;
        $this->sourceUrl = sprintf('https://www.youtube.com/watch?v=%s', $videoId);
        $this->title = $videoId;
        $this->segments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
