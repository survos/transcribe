# transcribe
Scripts to call the Google Speech API for MOV files (JUFJ)

Used by JUFJ for gala 2018 videos.

## Workflow:

Setup files as follows

@todo Setup files as follows

    /kesh
        /interview
        /interview-audio
        /broll
        /photos
    

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
