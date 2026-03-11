<?php

defined('MOODLE_INTERNAL') || die();

$functions = [
    'aiplacement_airesourceguide_get_references' => [
        'classname'    => 'aiplacement_airesourceguide\external\get_references',
        'methodname'   => 'execute',
        'description'  => 'Get AI-generated learning references for a Page activity',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'aiplacement/airesourceguide:viewreferences',
    ],
];
