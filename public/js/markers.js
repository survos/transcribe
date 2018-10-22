$(function() {

    var element = $(".transcript");

    element.attr('unselectable', 'on').css('user-select', 'none').on('selectstart dragstart', false);


    $('.word').click( function (e) {
        word_index = $(this).data('word-index');
        console.log(e, $(this))

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



            $('#audio').bind('timeupdate', function () {
                console.log(stopTime);
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
            console.log(time)
            audio.currentTime = time;
            audio.pause();
        }
    });

});
