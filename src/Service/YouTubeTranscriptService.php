<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TranscriptSegment;
use App\Entity\VideoSource;
use App\Enum\TranscriptSource;
use App\Enum\TranscriptStatus;
use App\Input\ExportStoryInput;
use App\Input\FetchYouTubeTranscriptInput;
use App\Repository\VideoSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\MapInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use MrMySQL\YoutubeTranscript\TranscriptListFetcher;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\u;

final class YouTubeTranscriptService
{
    public function __construct(
        private readonly VideoSourceRepository $videos,
        private readonly EntityManagerInterface $em,
        private readonly Filesystem $filesystem,
        private readonly HttpClientInterface $httpClient,
    ) {}

    #[AsCommand('youtube:transcript:fetch', 'fetch YouTube captions for a known video')]
    public function fetch(SymfonyStyle $io, #[MapInput] FetchYouTubeTranscriptInput $input): int
    {
        $videoId = $this->videoId($input->video);
        $video = $this->videos->find($videoId) ?? new VideoSource($videoId);
        $video->sourceUrl = sprintf('https://www.youtube.com/watch?v=%s', $videoId);
        $video->title = $input->title ?: $video->title;
        $this->enrichMetadata($video, $input->apiKeyFile);

        $this->em->persist($video);

        try {
            $segments = $this->fetchCaptionSegments($videoId, $input->language);
        } catch (\Throwable $e) {
            if (!$input->allowMissing) {
                throw $e;
            }
            $video->transcriptStatus = TranscriptStatus::NeedsTranscription;
            $this->em->flush();
            $io->warning(sprintf('No captions imported for %s; marked needsTranscription.', $videoId));

            return Command::SUCCESS;
        }

        if ($segments === []) {
            $video->transcriptStatus = TranscriptStatus::NeedsTranscription;
            $this->em->flush();
            $io->warning(sprintf('No captions found for %s; marked needsTranscription.', $videoId));

            return Command::SUCCESS;
        }

        foreach ($video->segments as $segment) {
            $this->em->remove($segment);
        }
        $video->segments->clear();

        foreach ($segments as $segmentData) {
            $segment = new TranscriptSegment($video, $segmentData['startMs']);
            $segment->durationMs = $segmentData['durationMs'];
            $segment->text = $segmentData['text'];
            $segment->language = $segmentData['language'];
            $segment->source = TranscriptSource::YouTubeCaption;
            $video->segments->add($segment);
            $this->em->persist($segment);
        }

        $video->transcriptStatus = TranscriptStatus::Imported;
        $this->em->flush();

        $io->success(sprintf('Imported %d transcript segments for %s.', count($segments), $videoId));

        return Command::SUCCESS;
    }

    #[AsCommand('youtube:story:export', 'export selected transcript excerpts as story JSON and captions')]
    public function exportStory(SymfonyStyle $io, #[MapInput] ExportStoryInput $input): int
    {
        $video = $this->videos->find($input->videoId);
        if (!$video) {
            $io->error(sprintf('Video not found: %s', $input->videoId));

            return Command::FAILURE;
        }

        $segments = $this->segmentsInRange($video, $input->start, $input->end);
        if ($segments === []) {
            $io->error('No transcript segments matched the requested time range.');

            return Command::FAILURE;
        }

        $this->filesystem->mkdir($input->outputDir);

        $base = rtrim($input->outputDir, '/') . '/' . $video->id . '-' . $this->milliseconds((float) $input->start);
        $storyPath = $base . '.story.json';
        $vttPath = $base . '.vtt';
        $srtPath = $base . '.srt';

        $this->filesystem->dumpFile($storyPath, json_encode($this->storyBlock($video, $segments, $input), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
        $this->filesystem->dumpFile($vttPath, $this->vtt($segments));
        $this->filesystem->dumpFile($srtPath, $this->srt($segments));

        $io->success(sprintf('Exported %s, %s, and %s.', $storyPath, $vttPath, $srtPath));

        return Command::SUCCESS;
    }

    /** @return array<int, array{startMs:int, durationMs:int, text:string, language:?string}> */
    private function fetchCaptionSegments(string $videoId, ?string $language): array
    {
        $psr18Client = new Psr18Client($this->httpClient);
        $fetcher = new TranscriptListFetcher($psr18Client, $psr18Client, $psr18Client);
        $list = $fetcher->fetch($videoId);
        $languages = $language ? array_map("trim", explode(",", $language)) : $list->getAvailableLanguageCodes();
        $transcript = $list->findTranscript($languages);
        $segments = [];

        foreach ($transcript->fetch() as $item) {
            $text = (string) u((string) ($item["text"] ?? ""))->collapseWhitespace();
            if ($text === "") {
                continue;
            }

            $startSeconds = (float) ($item["start"] ?? 0);
            $durationSeconds = (float) ($item["duration"] ?? 0);
            $segments[] = [
                "startMs" => $this->milliseconds($startSeconds),
                "durationMs" => $this->milliseconds($durationSeconds),
                "text" => $text,
                "language" => $item["language"] ?? $languages[0] ?? null,
            ];
        }

        return $segments;
    }

    private function enrichMetadata(VideoSource $video, ?string $apiKeyFile): void
    {
        $apiKey = $this->youtubeApiKey($apiKeyFile);
        if (!$apiKey) {
            return;
        }

        $response = $this->httpClient->request("GET", "https://www.googleapis.com/youtube/v3/videos", [
            "query" => [
                "part" => "snippet,contentDetails",
                "id" => $video->id,
                "key" => $apiKey,
            ],
        ]);

        $data = $response->toArray(false);
        $item = $data["items"][0] ?? null;
        if (!$item) {
            return;
        }

        $snippet = $item["snippet"] ?? [];
        $video->title = $snippet["title"] ?? $video->title;
        $video->description = $snippet["description"] ?? $video->description;
        $video->publishedAt = isset($snippet["publishedAt"]) ? new \DateTimeImmutable($snippet["publishedAt"]) : $video->publishedAt;
        $video->thumbnailUrl = $snippet["thumbnails"]["high"]["url"] ?? $snippet["thumbnails"]["default"]["url"] ?? $video->thumbnailUrl;
        $video->channelId = $snippet["channelId"] ?? $video->channelId;
        $video->duration = $this->durationToSeconds($item["contentDetails"]["duration"] ?? null) ?? $video->duration;
        $video->rawMetadata = $item;
    }

    private function youtubeApiKey(?string $apiKeyFile): ?string
    {
        $envKey = $_ENV["YOUTUBE_API_KEY"] ?? $_SERVER["YOUTUBE_API_KEY"] ?? getenv("YOUTUBE_API_KEY") ?: null;
        if ($envKey) {
            return $envKey;
        }

        if (!$apiKeyFile || !is_file($apiKeyFile)) {
            return null;
        }

        $values = (new Dotenv())->parse(file_get_contents($apiKeyFile) ?: "", $apiKeyFile);

        return $values["YOUTUBE_API_KEY"] ?? null;
    }


    /** @return array<int, TranscriptSegment> */
    private function segmentsInRange(VideoSource $video, float $startSeconds, ?float $endSeconds): array
    {
        $startMs = $this->milliseconds($startSeconds);
        $endMs = $endSeconds === null ? null : $this->milliseconds($endSeconds);
        $segments = [];

        foreach ($video->segments as $segment) {
            $segmentEnd = $segment->startMs + $segment->durationMs;
            if ($segmentEnd < $startMs) {
                continue;
            }
            if ($endMs !== null && $segment->startMs > $endMs) {
                continue;
            }
            $segments[] = $segment;
        }

        return $segments;
    }

    /** @param array<int, TranscriptSegment> $segments */
    private function storyBlock(VideoSource $video, array $segments, ExportStoryInput $input): array
    {
        $first = $segments[0];
        $last = $segments[array_key_last($segments)];
        $startMs = max($this->milliseconds($input->start), $first->startMs);
        $endMs = $input->end === null ? $last->startMs + $last->durationMs : $this->milliseconds($input->end);

        return [
            'kind' => $input->kind,
            'provider' => 'youtube',
            'videoId' => $video->id,
            'sourceUrl' => $video->sourceUrl,
            'title' => $video->title,
            'startMs' => $startMs,
            'durationMs' => max(0, $endMs - $startMs),
            'text' => trim(implode(' ', array_map(static fn(TranscriptSegment $segment): string => $segment->text, $segments))),
            'captionTrack' => array_map(static fn(TranscriptSegment $segment): array => [
                'startMs' => $segment->startMs,
                'durationMs' => $segment->durationMs,
                'text' => $segment->text,
                'language' => $segment->language,
                'source' => $segment->source->value,
            ], $segments),
            'blocks' => [
                [
                    'type' => 'contextLink',
                    'url' => $video->sourceUrl,
                    'label' => 'YouTube',
                ],
            ],
        ];
    }

    /** @return array<int, array{startMs:int, durationMs:int, text:string, language:?string}> */
    private function parseVtt(string $vtt, ?string $language): array
    {
        $segments = [];
        $lines = preg_split('/\R/', $vtt) ?: [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = trim($lines[$i]);
            if (!preg_match('/^([0-9:.]+)\s+-->\s+([0-9:.]+)/', $line, $matches)) {
                continue;
            }

            $textLines = [];
            while (++$i < $count && trim($lines[$i]) !== '') {
                $text = trim(strip_tags($lines[$i]));
                if ($text !== '' && !str_contains($text, '-->')) {
                    $textLines[] = $text;
                }
            }

            $text = (string) u(html_entity_decode(implode(' ', $textLines), ENT_QUOTES | ENT_HTML5))->collapseWhitespace();
            if ($text === '') {
                continue;
            }

            $startMs = $this->timestampToMs($matches[1]);
            $endMs = $this->timestampToMs($matches[2]);
            $segments[] = [
                'startMs' => $startMs,
                'durationMs' => max(0, $endMs - $startMs),
                'text' => $text,
                'language' => $language,
            ];
        }

        return $segments;
    }

    /** @param array<int, TranscriptSegment> $segments */
    private function vtt(array $segments): string
    {
        $body = ["WEBVTT", ""];
        foreach ($segments as $segment) {
            $body[] = sprintf('%s --> %s', $this->vttTime($segment->startMs), $this->vttTime($segment->startMs + $segment->durationMs));
            $body[] = $segment->text;
            $body[] = '';
        }

        return implode("\n", $body);
    }

    /** @param array<int, TranscriptSegment> $segments */
    private function srt(array $segments): string
    {
        $body = [];
        foreach (array_values($segments) as $index => $segment) {
            $body[] = (string) ($index + 1);
            $body[] = sprintf('%s --> %s', $this->srtTime($segment->startMs), $this->srtTime($segment->startMs + $segment->durationMs));
            $body[] = $segment->text;
            $body[] = '';
        }

        return implode("\n", $body);
    }

    private function videoId(string $video): string
    {
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $video)) {
            return $video;
        }
        if (preg_match('#(?:v=|youtu\.be/|shorts/)([a-zA-Z0-9_-]{11})#', $video, $matches)) {
            return $matches[1];
        }

        throw new \InvalidArgumentException(sprintf('Unable to read a YouTube video ID from "%s".', $video));
    }

    private function timestampToMs(string $timestamp): int
    {
        $parts = explode(':', str_replace(',', '.', $timestamp));
        $seconds = (float) array_pop($parts);
        $minutes = (int) array_pop($parts);
        $hours = $parts ? (int) array_pop($parts) : 0;

        return (int) round((($hours * 3600) + ($minutes * 60) + $seconds) * 1000);
    }

    private function durationToSeconds(?string $duration): ?int
    {
        if (!$duration) {
            return null;
        }

        $interval = new \DateInterval($duration);

        return ($interval->d * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }

    private function milliseconds(float $seconds): int
    {
        return (int) round($seconds * 1000);
    }

    private function vttTime(int $milliseconds): string
    {
        return $this->time($milliseconds, '.');
    }

    private function srtTime(int $milliseconds): string
    {
        return $this->time($milliseconds, ',');
    }

    private function time(int $milliseconds, string $separator): string
    {
        $hours = intdiv($milliseconds, 3600000);
        $milliseconds -= $hours * 3600000;
        $minutes = intdiv($milliseconds, 60000);
        $milliseconds -= $minutes * 60000;
        $seconds = intdiv($milliseconds, 1000);
        $milliseconds -= $seconds * 1000;

        return sprintf('%02d:%02d:%02d%s%03d', $hours, $minutes, $seconds, $separator, $milliseconds);
    }

    private function languageFromFilename(string $filename): ?string
    {
        if (preg_match('/\.([a-z]{2}(?:-[A-Z]{2})?)\.vtt$/', $filename, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
