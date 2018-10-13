<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
 * @UniqueEntity("filename")
 */
class Media
{
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

    public function __construct()
    {
        $this->flacExists = false;
        $this->transcriptRequested = false;
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

    public function getFileSize()
    {
        return filesize($this->getPath()) / (1024 * 1024) ;
    }

}
