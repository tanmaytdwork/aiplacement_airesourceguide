<?php
defined('MOODLE_INTERNAL') || die();

$definitions = [
    'references' => [
        'mode'       => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'ttl'        => 60 * 60 * 24, // 24 hours.
    ],
];
