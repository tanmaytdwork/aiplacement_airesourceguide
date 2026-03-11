<?php


namespace aiplacement_airesourceguide;

defined('MOODLE_INTERNAL') || die();

class placement extends \core_ai\placement {

    public function get_action_list(): array {
        return [
            \core_ai\aiactions\generate_text::class,
        ];
    }
}
