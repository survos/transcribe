document.querySelectorAll(".sortable").forEach((list) => {
    list.querySelectorAll("li[id^=\"marker_\"]").forEach((item) => {
        item.draggable = true;

        item.addEventListener("dragstart", (event) => {
            event.dataTransfer.effectAllowed = "move";
            event.dataTransfer.setData("text/plain", item.id);
            item.classList.add("dragging");
        });

        item.addEventListener("dragend", () => {
            item.classList.remove("dragging");
            updateMarkerOrder(list);
        });
    });

    list.addEventListener("dragover", (event) => {
        event.preventDefault();
        const draggingItem = list.querySelector(".dragging");
        const nextItem = getDragAfterElement(list, event.clientY);

        if (!draggingItem) {
            return;
        }

        if (nextItem) {
            list.insertBefore(draggingItem, nextItem);
        } else {
            list.appendChild(draggingItem);
        }
    });
});

document.querySelectorAll(".clip").forEach((clip) => {
    clip.addEventListener("click", (event) => {
        event.preventDefault();
        clickedMarker = clip.dataset.id;

        if (!audio.paused) {
            audio.pause();
            if (currentMarker === clickedMarker) {
                return;
            }
        }

        audio.src = clip.dataset.url;
        stopTime = parseFloat(clip.dataset.stop);
        startTime = parseFloat(clip.dataset.start);

        audio.addEventListener("timeupdate", function () {
            if (this.currentTime > stopTime) this.pause();
        });

        currentMarker = clickedMarker;
        audio.currentTime = startTime;
        audio.play();
    });
});

function getDragAfterElement(list, y) {
    return Array.from(list.querySelectorAll("li[id^=\"marker_\"]:not(.dragging)"))
        .reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset, element: child };
            }

            return closest;
        }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
}

function updateMarkerOrder(list) {
    const data = Array.from(list.querySelectorAll("li[id^=\"marker_\"]"))
        .map((item) => "marker[]=" + encodeURIComponent(item.id.replace("marker_", "")))
        .join("&");

    console.log("data", data);

    const markerOrder = document.getElementById("marker_order");
    if (markerOrder) {
        markerOrder.textContent = "order: " + data;
    }
}