<?php

return [

    'class' => null,

    'assets' => [
        'auto_inject' => true,
    ],

    'guest_links' => [
        'enabled' => true,
    ],

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#C8922A',
    ],

    'inject_assets' => true,

    'payload' => [
        'max_size' => 1048576,
        'max_nesting_depth' => 10,
        'max_calls' => 50,
        'max_components' => 200,
    ],

    'preload_html' => true,

];
