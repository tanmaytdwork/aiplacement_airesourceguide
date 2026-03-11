<?php

namespace aiplacement_airesourceguide;

class hook_listener {


    public static function before_footer_html_generation(
        \core\hook\output\before_footer_html_generation $hook,
    ): void {
        global $PAGE;
        if ($PAGE->context->contextlevel !== CONTEXT_MODULE) {
            return;
        }
        try {
            $cm = get_coursemodule_from_id('page', $PAGE->context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        if (!has_capability('aiplacement/airesourceguide:viewreferences', $PAGE->context)) {
            return;
        }

        $manager = \core\di::get(\core_ai\manager::class);
        if (!$manager->is_action_available(\core_ai\aiactions\generate_text::class)) {
            return;
        }

        $PAGE->requires->js_call_amd(
            'aiplacement_airesourceguide/guide',
            'init',
            ['cmid' => (int)$cm->id]
        );
    }
}
