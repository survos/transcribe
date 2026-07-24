console.log("Setting up listeners for transcript");

document.querySelectorAll(".transcript").forEach((element) => {
    element.setAttribute("unselectable", "on");
    element.style.userSelect = "none";
    element.addEventListener("selectstart", (event) => event.preventDefault());
    element.addEventListener("dragstart", (event) => event.preventDefault());
});

document.querySelectorAll(".word").forEach((wordElement) => {
    wordElement.addEventListener("click", (event) => {
        const wordIndex = parseInt(wordElement.dataset.wordIndex, 10);

        console.log(event, wordElement);

        if (event.shiftKey) {
            stopTime = parseFloat(wordElement.dataset.end);
            document.getElementById("marker_form_lastWordIndex").value = wordIndex;

            let note = "";
            const wordHandles = 3;
            for (let i = startWordIndex; i <= wordIndex; i++) {
                const currentWord = document.getElementById("w_" + i);
                if (!currentWord) {
                    continue;
                }

                currentWord.classList.add("newMarker");
                note = note + currentWord.dataset.word + " ";
            }

            document.getElementById("marker_form_note").value = note;

            audio.addEventListener("timeupdate", function () {
                console.log(stopTime);
                if (this.currentTime > stopTime) this.pause();
            });
            audio.play();
        } else {
            document.querySelectorAll(".newMarker").forEach((element) => element.classList.remove("newMarker"));

            wordElement.classList.add("newMarker");
            startWord = wordElement;

            time = parseFloat(wordElement.dataset.start);
            startWordIndex = wordIndex;
            document.getElementById("marker_form_firstWordIndex").value = wordIndex;
            document.getElementById("marker_form_note").value = startWord.dataset.word;
            console.log(time);
            audio.currentTime = time;
            audio.pause();
        }
    });
});
