
require('jquery-ui');

const $ = require('jquery');

$( ".sortable" ).sortable({
    update: function( event, ui ) {
        var data = $(this).sortable('serialize');
        console.log('data', data);
        $('#marker_order').text('order: ' + data);
        $.ajax({
            data: data,
            type: 'GET',
            // url: '{{ path('marker_reorder', project.rp) }}'
        });
    }
});

$( ".sortable" ).disableSelection();

$(function() {


    $('.clip').click( function (e) {
        e.preventDefault();
        clickedMarker = $(this).data('id');

        if (!audio.paused) {
            audio.pause();
            // if it's playing, then stop
            if (currentMarker === clickedMarker) {
                return;
            }
        }

        let a = $('#audio');

        audio.src = $(this).data('url');
        stopTime = $(this).data('stop');
        startTime = $(this).data('start');

        a.bind('timeupdate', function () {
            if (this.currentTime > stopTime) this.pause();
        });

        currentMarker = clickedMarker;
        audio.currentTime = startTime;
        audio.play();

        return true;


        word_index = $(this).data('word-index');

        if (e.shiftKey) {
            // $(this).css("color", "red");
            stopTime = $(this).data('end');
            $('#marker_form_lastWordIndex').val(word_index);
            // $('#marker_form_title').val(startWord.data('word') + '..' + $(this).data('word'));

            // get the phrase and add to the form
            let title = '';
            let note = '';
            let wordHandles = 3;
            for (let i = startWordIndex; i <= word_index; i++) {

                if ( (i <= startWordIndex + wordHandles) || (i >= word_index - wordHandles)) {
                    title = title + $('#w_' + i).data('word') + ' ';
                    if (i === (startWordIndex + wordHandles)) {
                        title = title + '..';
                    }
                }
                $('#w_' + i).addClass('newMarker');
                // $('#w_' + i).css("text-decoration", "underline overline");
                note = note + $('#w_' + i).data('word') + ' ';
            }
            // $('#marker_form_title').val(title);
            $('#marker_form_note').val(note);



            a.bind('timeupdate', function () {
                if (this.currentTime > stopTime) this.pause();
            });
            audio.play();

        } else {
            $('.newMarker').removeClass('newMarker');

            $(this).addClass('newMarker');
            // $(this).css("text-decoration", "underline overline");
            startWord = $(this);

            time = $(this).data('start');
            startWordIndex = word_index;
            $('#marker_form_firstWordIndex').val(word_index);
            $('#marker_form_note').val(startWord.data('word'));
            audio.currentTime = time;
            audio.pause();
        }
    });

});
