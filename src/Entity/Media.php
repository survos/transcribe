<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Survos\WorkflowBundle\Traits\MarkingTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
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

    public function __construct()
    {
        $this->flacExists = false;
        $this->transcriptRequested = false;
        $this->markers = new ArrayCollection();
        $this->words = new ArrayCollection();
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

    public function setPath(string $path): self
    {
        $this->path = $path;

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
        return $this->getPath() . '.flac';
        // return $this->getFilename() . '.wav';
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
        return sprintf("https://storage.googleapis.com/%s/%s.%s",
            $this->getBucketName(), $this->getBaseName(), $_format);

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

}
