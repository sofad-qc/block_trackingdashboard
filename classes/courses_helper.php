<?php

namespace block_trackingdashboard;

use core_completion\progress;

defined('MOODLE_INTERNAL') || die();

class courses_helper
{
    /**
     * Returns a list of courses taught by current user
     *
     * @return array
     */
    public static function get_user_courses() : array {

        // Get all courses where the user is enrolled
        $userCourses = enrol_get_my_courses(null, null, 0, [], true, 0, []);

        // Filter courses where he doesn't have the permission to list the users enrolled.
        $filteredArray = [];
        foreach($userCourses as $userCourse) {
            $coursecontext = \context_course::instance($userCourse->id);
            if(has_capability('block/trackingdashboard:listuser', $coursecontext)) {
                $filteredArray[] = $userCourse;
            }
        }
        return $filteredArray;
    }

    /**
     * Returns the course completion for a user
     *
     * @param completionInfo completionInfo
     * @param user user
     * @return string
     */
    public static function compute_course_completion($course, $userId) : string {

        $progress = progress::get_course_progress_percentage($course, $userId);

        if($progress == null) {
            return get_string('noActivityToComplete', 'block_trackingdashboard');
        }

        return number_format($progress, 2, '.', '') . '%';

    }
}