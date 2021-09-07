<?php

namespace block_trackingdashboard;

use html_writer;
use moodle_url;
global $CFG;
require_once($CFG->dirroot."/user/lib.php");
require_once($CFG->dirroot."/lib/completionlib.php");

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
            foreach($userList[$currentCourse->id] as $user) {
                $boardArray[] = array(
                    '' => display_helper::user_message($user, $currentCourse, $CFG, $page),
                    'userName' => display_helper::userProfile($user, $currentCourse),
                    'courseName' => display_helper::coursePage($currentCourse),
                    'courseLastActivity' => courses_helper::get_most_recent_activity_accessed($currentCourse->id, $user->id),
                    'courseCompletion' => courses_helper::compute_course_completion($currentCourse, $user->id),
                    'waitingGradedWork' => display_helper::buildNonGradedWorkList($currentCourse->id, $user->id),
                    'courseEndDate' => !$currentCourse->enddate ? get_string('noEndDate',
                        'block_trackingdashboard') : date('F jS, Y', $currentCourse->enddate),
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
        $context = $page->context;
        $course = ($page->context->contextlevel == CONTEXT_COURSE) ? $page->course : null;
        $userbutton = null;
        if (user_can_view_profile($user, $course)) {

            // Check to see if we should be displaying a message button.
            if(!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
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
        // start table
        $html = '<div class="table-responsive"><table class="table table-bordered" id="dashboard">';

        // header row
        $html .= '<thead class="thead-light"><tr>';
        foreach($tableData[0] as $key=>$value){
            if($key == '') {
                $html .= '<th scope="col">' . '' . '</th>';
            }
            else {
                $html .= '<th scope="col">' . htmlspecialchars(get_string($key, 'block_trackingdashboard')) . '</th>';
            }

        }
        $html .= '</tr></thead><tbody>';

        // data rows
        foreach($tableData as $key=>$value){
            $html .= '<tr>';
            foreach($value as $key2=>$value2) {
                if($key2 == '' || $key2 == 'userName' || $key2 == 'courseName' || $key2 == 'waitingGradedWork') {
                    $html .= '<td>' . $value2 . '</td>';
                } else {
                    $html .= '<td>' . htmlspecialchars($value2) . '</td>';
                }
            }
            $html .= '</tr>';
        }

        // finish table and return it

        $html .= '</tbody></table></div>';
        return $html;
    }

    /**
     * Returns a HTML string corresponding to the course link and information
     *
     * @param course course
     * @return string
     */
    public static function buildNonGradedWorkList($courseId, $userId) {
        $toReturn = '<ul class="activity_list">';
        $workArray = courses_helper::get_non_graded_work($courseId, $userId);
        if(gettype($workArray) == 'string') {
            return $workArray;
        }
        if(count($workArray) == 1) {
            return $workArray[0];
        }
        foreach ($workArray as $work) {
            $toReturn .= '<li>' . $work . '</li>';
        }
        $toReturn .= '</ul>';
        return $toReturn;
    }

    /**
     * Returns a HTML string corresponding to the user link and information
     *
     * @param user user
     * @param course course
     * @return string
     */
    public static function userProfile($user, $course) {
        global $CFG;
        return '<a href=' . $CFG->wwwroot . '/user/view.php?id=' . $user -> id. '&course=' . $course -> id. '>' . $user->firstname . ' ' . $user->lastname . '</a>';
    }

    /**
     * Returns a HTML string corresponding to the course link and information
     *
     * @param course course
     * @return string
     */
    public static function coursePage($course) {
        global $CFG;
        return '<a href=' . $CFG->wwwroot . '/course/view.php?id=' . $course -> id. '>' . $course->fullname . '</a>';
    }

    /**
     * Returns a string corresponding to the user's language
     *
     * @return string
     */
    public static function getLanguage() {
        return current_language();
    }
}