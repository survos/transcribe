# transcribe

The symfony6 branch has SOME work, but needs php7 to convert easyadmin.

Scripts to call the Google Speech API for MOV files (JUFJ)

Used by JUFJ for gala 2018 videos.

## Workflow:

Setup files as follows

@todo Setup files as follows

    /kesh
        /interview
        /interview-audio
        /broll
        /photos`
    

Assemble assets, .mov files at test/test1.mov, etc. photos and broll in broll/photo1.jpg

* Import media 

     bin/console app:import-media test --dir=/media/shared/test
     bin/console app:import-media claire --dir=../data/JUFJ/Videos/Claire


* Transcribe

    bin/console app:transcribe test --upload-flac --transcribe --upload-photos
    
This uploads the flac files to Google Storage (@todo: move to Amazon Transcribe) and uploads the photos.

* Select Excerpts

The user then can select excerpts from the transcripts and order them in a timeline.  That timeline can be exported to Final Cut (fcpxml), or eventually to kdenlive (mlt xml, https://www.mltframework.org/)

Related links:    

https://sabre.io/xml/reading/ (for parsing mlt and other xml)

## HANDOFF: Symfony 8 YouTube Transcript Work

Status as of 2026-07-20:

This checkout is mid-upgrade from the old PHP 7/Symfony 4-5 app toward Symfony 8.1/PHP 8.4+ conventions. The current focus is **transcription**, not YouTube channel acquisition. Channel-as-dataset/provider work is being handled separately in `survos-sites/musdig` issue #23.

Current direction:

- No legacy compatibility layer.
- No hand-written migrations right now.
- Use local SQLite in `.env.local`:

  ```dotenv
  DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
  ```

- Use `php bin/console doctrine:schema:update --force` during local development.
- Generate real migrations later with Symfony/Doctrine tools when publishing and switching to PostgreSQL.
- Use the YouTube API key from `/home/tac/sites/kpa/.env.local` at runtime; do not copy the key into this repo.

What landed:

- Composer was moved toward PHP `^8.4` and Symfony `^8.1`.
- `composer update --no-scripts --no-interaction -W` completed and installed `vendor/`.
- Added `mrmysql/youtube-transcript`, matching the approach in `/home/tac/symfony/ai/demo`.
- Added modern PHP 8.4-style transcript model pieces:
  - `src/Entity/VideoSource.php`
  - `src/Entity/TranscriptSegment.php`
  - `src/Enum/VideoProvider.php`
  - `src/Enum/TranscriptSource.php`
  - `src/Enum/TranscriptStatus.php`
  - `src/Input/FetchYouTubeTranscriptInput.php`
  - `src/Input/ExportStoryInput.php`
  - `src/Repository/VideoSourceRepository.php`
  - `src/Repository/TranscriptSegmentRepository.php`
  - `src/Service/YouTubeTranscriptService.php`
- Added method-level commands:
  - `youtube:transcript:fetch`
  - `youtube:story:export`
- The transcript fetch command accepts a known YouTube video ID or URL, enriches video metadata via the YouTube Data API key if available, fetches transcript segments via `mrmysql/youtube-transcript`, and marks missing captions as `needsTranscription`.
- The story export command writes story JSON plus VTT/SRT for a selected transcript time range.

Important decisions:

- Do not implement channel acquisition here.
- Do not add `yt-dlp` provider/channel listing here.
- Do not preserve old annotation/legacy bundles just to keep the old UI booting.
- Prefer Symfony 8 method-level `#[AsCommand]`, DTO inputs, public-property Doctrine entities, enums, and typed code.

Current blocker:

`php bin/console doctrine:schema:update --force` currently fails because Doctrine is still scanning the old `App\Entity` namespace. It loads legacy `src/Entity/Media.php`, which uses `Survos\WorkflowBundle\Traits\MarkingTrait`; that bundle was removed during the Symfony 8 cleanup.

The next step is to keep the new transcript entities out of the legacy entity namespace, or finish converting/removing the legacy entities. The intended quick path is:

1. Move the new transcript entities into a separate modern namespace, for example:

   ```text
   src/Transcription/Entity/VideoSource.php
   src/Transcription/Entity/TranscriptSegment.php
   ```

2. Update their namespaces and repository imports.
3. Change Doctrine mapping to scan only that modern namespace for now:

   ```yaml
   doctrine:
     orm:
       mappings:
         Transcription:
           is_bundle: false
           type: attribute
           dir: '%kernel.project_dir%/src/Transcription/Entity'
           prefix: 'App\Transcription\Entity'
           alias: Transcription
   ```

4. Rerun:

   ```bash
   php bin/console doctrine:schema:update --force
   ```

5. Then test a known video:

   ```bash
   php bin/console youtube:transcript:fetch 'https://www.youtube.com/watch?v=VIDEO_ID' --language=es
   php bin/console youtube:story:export VIDEO_ID --start=10 --end=40
   ```

Notes for the next session:

- `composer.lock`, `symfony.lock`, and several Symfony recipe files changed during Composer update.
- Legacy configs/bundles were removed for Sensio FrameworkExtra, Survos Workflow/Landing, Oneup Flysystem, VichUploader, Knp OAuth, and Webpack Encore.
- A generated migration file was intentionally removed per the “no migrations right now” decision.
- Re-check `git status --short` before continuing; there are many changes from Composer recipes and cleanup.
