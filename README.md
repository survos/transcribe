# transcribe
Scripts to call the Google Speech API for MOV files

Workflow:

Assemble assets, .mov files at test/test1.mov, etc. photos and broll in broll/photo1.jpg

* Import media 

    bin/console app:import-media test --dir=/media/shared/test

* Transcribe
    bin/console app:transcribe test --upload-flac --transcribe --upload-photos
    
    

