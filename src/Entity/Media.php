<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Survos\WorkflowBundle\Traits\MarkingTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
 * @ORM\Table(name="media", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="project_code", columns={"project_id", "code"})
 * }, indexes={
 *      @ORM\Index(name="code", columns={"code"})
 * })
 * @UniqueEntity("filename")
 */
class Media
{

    use MarkingTrait;

    const
        PLACE_START = 'start',
        PLACE_AUDIO_LOCAL = 'local',
        PLACE_AUDIO_UPLOADED = 'uploaded',
        PLACE_TRANSCRIBED = 'transcribed',
        PLACE_MP3_UPLOADED = 'transcribed';

    const
        TRANSITION_EXRACT_RAW_AUDIO = 'extract_raw_audio',
        TRANSITION_UPLOAD_RAW = 'upload_raw',
        TRANSITION_TRANSCRIBE = 'transcribe'
    ;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="boolean")
     */
    private $flacExists;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transcriptJson;

    /**
     * @ORM\Column(type="boolean")
     */
    private $transcriptRequested;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $word_count;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $file_size;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="media")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Marker", mappedBy="media", orphanRemoval=true)
     */
    private $markers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Word", mappedBy="media", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"idx" = "asc"})
     */
    private $words;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $speaker;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $display;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $streams_json;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stream_count;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BRoll", mappedBy="media", orphanRemoval=true)
     */
    private $bRolls;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $video_stream;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $audio_streams = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $frame_rate;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $frame_duration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimelineAsset", mappedBy="media")
     */
    private $timelineAssets;

    public function __construct()
    {
        $this->flacExists = false;
        $this->transcriptRequested = false;
        $this->markers = new ArrayCollection();
        $this->words = new ArrayCollection();
        $this->bRolls = new ArrayCollection();
        $this->timelineAssets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    // this is really relativePath
    public function setPath(string $path): self
    {
        $this->path = $path;
        if (empty($this->code)) {
            $code =  pathinfo($path, PATHINFO_FILENAME);
            $this->setCode($code);
        }

        return $this;
    }

    public function getFlacExists(): ?bool
    {
        return $this->flacExists;
    }

    public function setFlacExists(bool $flacExists): self
    {
        $this->flacExists = $flacExists;

        return $this;
    }

    public function getTranscriptJson(): ?string
    {
        return $this->transcriptJson;
    }

    public function setTranscriptJson($transcriptJson): self
    {
        $this->transcriptJson = $transcriptJson;

        return $this;
    }

    public function getTranscriptRequested(): ?bool
    {
        return $this->transcriptRequested;
    }

    public function setTranscriptRequested(bool $transcriptRequested): self
    {
        $this->transcriptRequested = $transcriptRequested;

        return $this;
    }

    public function getTranscript()
    {
        return $this->getTranscriptJson() ? json_decode($this->getTranscriptJson()) : null;
    }

    public function getWordCount(): ?int
    {
        return $this->word_count;
    }

    public function setWordCount(?int $word_count): self
    {
        $this->word_count = $word_count;

        return $this;
    }

    public function getSentenceCount()
    {
        $transcript = $this->getTranscript();
        return $transcript ? count($transcript): null;
    }

    public function calcFileSize(): ?float
    {
        if (file_exists($this->getPath())) {
            return filesize($this->getPath()) ;
        }
        return null;
    }

    public function getFileSize(): ?int {
        return $this->file_size;
    }

    public function getAudioFilePath()
    {
        return $this->getRealPath('\\') . '.flac';
        // return $this->getFilename() . '.wav';
    }

    public function getThumbFilePath()
    {
        return $this->getPath() . '.jpg';
    }

    public function getAudioFileName()
    {
        return $this->getFilename() . '.flac';
    }

    public function rp($addl=[])
    {
        return array_merge($addl, ['id' => $this->getId()]);
    }

    public function setFileSize(?int $file_size): self
    {
        $this->file_size = $file_size;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getPublicUrl($_format='flac')
    {
        return sprintf("https://storage.googleapis.com/%s/%s%s",
            $this->getBucketName(), $this->getFilename(), $_format ? ".$_format" : '');

    }

    public function getBucketName()
    {
        return $this->getProject()->getBucketName();
    }

    public function getBaseName()
    {
        return basename($this->getFilename());
    }

    public function getTranscribeSize()
    {
        return $this->getTranscriptJson() ? strlen($this->getTranscriptJson()) : -1;
    }

    public function getProjectCode()
    {
        return $this->getProject()->getCode();
    }

    public function __toString()
    {
        return sprintf("%s/%s", $this->getProjectCode(), $this->getBaseName());
    }

    public function getRealPath($delim="//")
    {
        $path = sprintf("%s$delim%s", $this->getProject()->getBasePath(), $this->getPath());
        $path = str_replace('\\', $delim, $path);
        $path = str_replace('//', $delim, $path);
        return $path;
    }

    /**
     * @return Collection|Marker[]
     */
    public function getMarkers(): Collection
    {
        return $this->markers;
    }

    public function addMarker(Marker $marker): self
    {
        if (!$this->markers->contains($marker)) {
            $this->markers[] = $marker;
            $marker->setMedia($this);
        }

        return $this;
    }

    public function removeMarker(Marker $marker): self
    {
        if ($this->markers->contains($marker)) {
            $this->markers->removeElement($marker);
            // set the owning side to null (unless already changed)
            if ($marker->getMedia() === $this) {
                $marker->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Word[]
     */
    public function getWords(): Collection
    {
        return $this->words;
    }

    public function addWord(Word $word): self
    {
        if (!$this->words->contains($word)) {
            $this->words[] = $word;
            $word->setMedia($this);
        }

        return $this;
    }

    public function removeWord(Word $word): self
    {
        if ($this->words->contains($word)) {
            $this->words->removeElement($word);
            // set the owning side to null (unless already changed)
            if ($word->getMedia() === $this) {
                $word->setMedia(null);
            }
        }

        return $this;
    }

    public function getSpeaker(): ?string
    {
        return $this->speaker;
    }

    public function setSpeaker(?string $speaker): self
    {
        $this->speaker = $speaker;

        return $this;
    }

    public function getDisplay(): ?string
    {
        return $this->display;
    }

    public function setDisplay(?string $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function getMarkersByWordIndex()
    {
        // cache it if it doesn't already exist?  Or use a collection with a proper index?
        $markers = [];
        foreach ($this->getMarkers() as $marker) {
            $markers[$marker->getFirstWordIndex()] = $marker;
        }
        return $markers;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $code = str_replace('(', '_', $code);
        $code = str_replace(')', '', $code);
        $this->code = $code;

        return $this;
    }

    public function getStreamsJson(): ?string
    {
        return $this->streams_json;
    }

    public function getStreams()
    {
        return $this->getStreamsJson() ? json_decode($this->getStreamsJson()) : [];
    }

    public function setStreamsJson(?string $streams_json): self
    {
        $this->streams_json = $streams_json;

        return $this;
    }

    public function getStreamCount(): ?int
    {
        return $this->stream_count;
    }

    public function setStreamCount(?int $stream_count): self
    {
        $this->stream_count = $stream_count;

        return $this;
    }

    public function getStreamsInfo()
    {
        $x = '';
        foreach ($this->getStreams() as $stream) {
            $x .= json_encode($stream);
        }
        return $x;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|BRoll[]
     */
    public function getBRolls(): Collection
    {
        return $this->bRolls;
    }

    public function addBRoll(BRoll $bRoll): self
    {
        if (!$this->bRolls->contains($bRoll)) {
            $this->bRolls[] = $bRoll;
            $bRoll->setMedia($this);
        }

        return $this;
    }

    public function removeBRoll(BRoll $bRoll): self
    {
        if ($this->bRolls->contains($bRoll)) {
            $this->bRolls->removeElement($bRoll);
            // set the owning side to null (unless already changed)
            if ($bRoll->getMedia() === $this) {
                $bRoll->setMedia(null);
            }
        }

        return $this;
    }

    public function isPhoto(): bool
    {
        return $this->getType() == 'photo';
    }

    public function createTimelineFormat(): TimelineFormat
    {
        $tf = (new TimelineFormat())
            ->setWidth($this->getWidth())
            ->setHeight($this->getHeight())
            ->setFrameDurationString($this->getFrameDuration())
            ;
        $id = sprintf("%dx%d@%s", $this->getHeight(), $this->getWidth(), $this->getFrameDuration());
        $tf->setCode($id);

        return $tf;

        foreach ($this->getStreams() as $stream) {
            dump($stream);
        }
        die();
    }

    public function getVideoStream(): ?array
    {
        return $this->video_stream ? json_decode($this->video_stream, true) : null;
    }

    public function setVideoStream(?array $video_stream): self
    {
        $this->video_stream = json_encode($video_stream);

        return $this;
    }

    public function getAudioStreams(): ?array
    {
        return $this->audio_streams;
    }

    public function setAudioStreams(?array $audio_streams): self
    {
        $this->audio_streams = $audio_streams;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getFrameRate(): ?string
    {
        return $this->frame_rate;
    }

    public function setFrameRate(?string $frame_rate): self
    {
        $this->frame_rate = $frame_rate;

        return $this;
    }

    public function getFrameDuration(): ?string
    {
        return $this->frame_duration;
    }

    public function setFrameDuration(?string $frame_duration): self
    {
        $this->frame_duration = $frame_duration;

        return $this;
    }

    /**
     * @return Collection|TimelineAsset[]
     */
    public function getTimelineAssets(): Collection
    {
        return $this->timelineAssets;
    }

    public function addTimelineAsset(TimelineAsset $timelineAsset): self
    {
        if (!$this->timelineAssets->contains($timelineAsset)) {
            $this->timelineAssets[] = $timelineAsset;
            $timelineAsset->setMedia($this);
        }

        return $this;
    }

    public function removeTimelineAsset(TimelineAsset $timelineAsset): self
    {
        if ($this->timelineAssets->contains($timelineAsset)) {
            $this->timelineAssets->removeElement($timelineAsset);
            // set the owning side to null (unless already changed)
            if ($timelineAsset->getMedia() === $this) {
                $timelineAsset->setMedia(null);
            }
        }

        return $this;
    }

}
