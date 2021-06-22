<?php

namespace block_trackingdashboard;

use core_message\api;

defined('MOODLE_INTERNAL') || die();

class messaging_helper
{

    /**
     * Returns the attributes to place on the message user button.
     *
     * @param int $useridto
     * @return array
     */
    public static function messageuser_link_params($useridto, $courseId) : array {
        global $USER;

        return [
            'id' => 'message-user-' . $useridto . '-course-'. $courseId . '-button',
            'role' => 'button',
            'data-conversationid' => api::get_conversation_between_users([$USER->id, $useridto]),
            'data-userid' => $useridto,
        ];
    }


    /**
     * Requires the JS libraries for the message user button.
     *
     * @return void
     */
    public static function messageuser_requirejs($userId, $courseId)
    {
        global $PAGE;

        $PAGE->requires->js_call_amd('block_trackingdashboard/message_user_button_custom', 'send', array('#message-user-' . $userId . '-course-'. $courseId .'-button'));
    }
}