<?php

namespace block_trackingdashboard;

use completion_info;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

class display_helper
{

    /**
     * Returns an array corresponding to the table data
     *
     * @param array userList
     * @param array courseList
     * @param CFG CFG
     * @return array
     */
    public static function build_table_data($userList, $courseList, $CFG, $page) : array {
        $boardArray = [];
        foreach($courseList as $currentCourse) {
            $courseCompletionInfo = new completion_info($currentCourse);
            foreach($userList[$currentCourse->id] as $user) {
                $userLastAccess = users_helper::get_last_access($currentCourse, $user);
                $boardArray[] = array(
                    '' => display_helper::user_message($user, $currentCourse, $CFG, $page),
                    get_string('userId', 'block_trackingdashboard') => $user->id,
                    get_string('userName', 'block_trackingdashboard') => $user->firstname . ' ' . $user->lastname,
                    get_string('courseName', 'block_trackingdashboard') => $currentCourse->fullname,
                    get_string('courseLastAccess', 'block_trackingdashboard') => $userLastAccess == false ? get_string('noActivityYet', 'block_trackingdashboard') : date('F jS, Y', $userLastAccess),
                    get_string('courseCompletion', 'block_trackingdashboard') => courses_helper::compute_course_completion($courseCompletionInfo, $user)
                );
            }
        }
        return $boardArray;
    }

    /**
     * create button link to send message for each user
     *
     * @param user user
     * @param CFG CFG
     * @return array
     */
    public static function user_message($user, $currentCourse, $CFG, $page) {
        global $DB;
        $context = $page->context;
        $course = ($page->context->contextlevel == CONTEXT_COURSE) ? $page->course : null;
        $userbutton = null;
        if (user_can_view_profile($user, $course)) {
            // Use the user's full name if the heading isn't set.
            if (empty($heading)) {
                $heading = fullname($user);
            }

            // Check to see if we should be displaying a message button.
            if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                $userbutton = array(
                    'buttontype' => 'message',
                    'title' => get_string('message', 'message'),
                    'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                    'image' => 'message',
                    'linkattributes' => messaging_helper::messageuser_link_params($user->id, $currentCourse->id),
                    'page' => $page
                );
                $page->requires->string_for_js('changesmadereallygoaway', 'moodle');
            }
        }

        $html = '';
        // Buttons.
        if ($userbutton != null) {
            $html .= html_writer::start_div('btn-group header-button-group');
            if (!isset($userbutton->page)) {
                // Include js for messaging.
                if ($userbutton['buttontype'] === 'message') {
                    messaging_helper::messageuser_requirejs($user->id, $currentCourse->id);
                }
                $image = '<i class="icon fa fa-comment fa-fw iconsmall" title="Message" aria-label="Message"></i>';
            }
            $some = html_writer::link($userbutton['url'], html_writer::tag('span', $image), $userbutton['linkattributes']);
            $html .= $some;
            $html .= html_writer::end_div();
        }
        return $html;
    }

    /**
     * Returns an array corresponding to the table
     *
     * @param array tableData
     * @return array
     */
    public static function build_table($tableData){
        global $CFG;

        // start table
        $html = '<table class="table table-bordered" id="dashboard">';

        // header row
        $html .= '<thead class="thead-light"><tr>';
        foreach($tableData[0] as $key=>$value){
            if($key != get_string('userId', 'block_trackingdashboard')) {
                if($key == '') {
                    $html .= '<th>' . '' . '</th>';
                }
                else {
                    $html .= '<th>' . htmlspecialchars($key) . '</th>';
                }

            }
        }
        $html .= '</tr></thead><tbody>';

        // data rows
        foreach($tableData as $key=>$value){
            $html .= '<tr>';
            foreach($value as $key2=>$value2){
                if($key2 != get_string('userId', 'block_trackingdashboard')) {
                    if($key2 == get_string('userName', 'block_trackingdashboard')) {
                        $html .= '<td><a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $value[get_string('userId', 'block_trackingdashboard')] . '">' . htmlspecialchars($value2) . '</a></td>';
                    } else if($key2 == '') {
                        $html .= '<td>' . $value2 . '</td>';
                    } else {
                        $html .= '<td>' . htmlspecialchars($value2) . '</td>';
                    }
                }
            }
            $html .= '</tr>';
        }

        // finish table and return it

        $html .= '</tbody></table>';
        return $html;
    }

}