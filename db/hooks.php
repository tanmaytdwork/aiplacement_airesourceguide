<?php

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\output\before_footer_html_generation::class,
        'callback' => \aiplacement_airesourceguide\hook_listener::class . '::before_footer_html_generation',
    ],
];
