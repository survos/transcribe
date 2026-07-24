<?php

/**
 * Returns the importmap for this application.
 */
return [
    app => [
        path => ./assets/app.js,
        entrypoint => true,
    ],
    markers => [
        path => ./assets/js/markers.js,
        entrypoint => true,
    ],
    project => [
        path => ./assets/js/project.js,
        entrypoint => true,
    ],
];