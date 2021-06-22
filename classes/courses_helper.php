<?php

namespace block_trackingdashboard;

defined('MOODLE_INTERNAL') || die();

class courses_helper
{
    /**
     * Returns a list of courses teached by current user
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
    public static function compute_course_completion($completionInfo, $user) : string {
        $userModulesProgress = $completionInfo -> get_completions($user->id, null);

        $moduleCountToCompleteForCompletion = count($completionInfo->get_criteria());
        $moduleCompleted = 0;
        foreach($userModulesProgress as $userModuleProgress) {
            foreach($completionInfo->get_criteria() as $criteria) {
                if($userModuleProgress->get_criteria()->criteriatype == $criteria->criteriatype
                    && $userModuleProgress->get_criteria()->moduleinstance == $criteria->moduleinstance
                    && $userModuleProgress->timecompleted != null) {
                    $moduleCompleted = $moduleCompleted + 1;
                }
            }
        }
        return number_format((float)($moduleCompleted / $moduleCountToCompleteForCompletion) * 100, 2, '.', '') . "%";
    }
}