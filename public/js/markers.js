$(function() {

    var element = $("#transcript");

    element.attr('unselectable', 'on').css('user-select', 'none').on('selectstart dragstart', false);


    $('.word').click( function (e) {
        word_index = $(this).data('word-index');
        console.log(e, $(this))

        if (e.shiftKey) {
            $(this).css("color", "red");
            stopTime = $(this).data('end');
            $('#marker_form_lastWordIndex').val(word_index);
            $('#marker_form_title').val(startWord.data('word') + '..' + $(this).data('word'));

            // get the phrase and add to the form
            let title = '';
            for (let i = startWordIndex; i <= word_index; i++) {

                title = title + $('#w_' + i).data('word') + ' ';
            }
            $('#marker_form_title').val(title);



            $('#audio').bind('timeupdate', function () {
                console.log(stopTime);
                if (this.currentTime > stopTime+1.0) this.pause();
            });
            audio.play();

        } else {
            $(this).css("color", "green");
            startWord = $(this);
            time = $(this).data('start');
            startWordIndex = word_index;
            $('#marker_form_firstWordIndex').val(word_index);
            $('#marker_form_title').val(startWord.data('word'));
            console.log(time)
            audio.currentTime = time;
            audio.pause();
        }
    });

});
