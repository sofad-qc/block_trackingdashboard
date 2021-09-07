<?php

namespace block_trackingdashboard;

use core_completion\progress;

global $CFG;
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/lib/gradelib.php');

defined('MOODLE_INTERNAL') || die();

class courses_helper
{
    /**
     * Returns a list of courses taught by current user
     *
     * @return array
     */
    public static function get_user_courses() : array {

        global $DB;

        // Get all courses where the user is enrolled
        $userCourses = enrol_get_my_courses(null, null, 0, [], true, 0, []);

        // Filter courses where he doesn't have the permission to list the users enrolled.
        $filteredArray = [];
        foreach($userCourses as $userCourse) {
            $courseEndDate = $DB->get_record('course', array('id' => $userCourse->id), "enddate");
            $coursecontext = \context_course::instance($userCourse->id);
            if(has_capability('block/trackingdashboard:listuser', $coursecontext) && substr($userCourse->fullname, 0, 4) !== 'meta') {
                $userCourse->enddate = $courseEndDate->enddate;
                $filteredArray[] = $userCourse;
            }
        }
        return $filteredArray;
    }

    /**
     * Returns the course completion for a user
     *
     * @param course course
     * @param user user
     * @return string
     */
    public static function compute_course_completion($course, $userId) : string {

        global $DB;
        $courseInformation = $DB->get_record('course', array('id' => $course->id), "enablecompletion");

        if($courseInformation->enablecompletion == "1") {
            $progress = progress::get_course_progress_percentage($course, $userId);

            if($progress == null) {
                $progress = 0;
            }

            return number_format($progress, 2, '.', '') . '%';
        } else {
            return get_string('noActivityToComplete', 'block_trackingdashboard');
        }
    }

    /**
     * Returns date of last interaction with activity in course
     *
     * @param course course
     * @param user user
     * @return string
     */
    public static function get_most_recent_activity_accessed($courseId, $userId) : string {

        global $DB;
        $accessedItems = $DB->get_record_sql(
            "Select cmid, MAX(timeaccess) as lastTimeAccess From {block_recentlyaccesseditems} Where courseid='$courseId' and userid='$userId'");

        return $accessedItems->cmid == null ? get_string('noActivityYet',
            'block_trackingdashboard') : date('F jS, Y', $accessedItems->lasttimeaccess);

    }

    /**
     * Returns list of non graded activities in course, or string indicated there is no non graded activities yet
     *
     * @param courseId courseId
     * @param userId userId
     * @return string
     * @return array
     */
    public static function get_non_graded_work($courseId, $userId) {

        global $DB;
        global $CFG;
        $non_grated_activities = [];
        $activitiesInCourse = get_array_of_activities($courseId);

        foreach ($activitiesInCourse as $activity) {
            $grade_infos = grade_get_grades($courseId, 'mod', $activity->mod, $activity->id, $userId);

            foreach($grade_infos->items as $grade_info) {
                foreach ($grade_info->grades as $grade) {
                    $submitionState = null;
                    if($grade_info->itemmodule == "assign") {
                        $submitionState = $DB->get_record('assign_submission', array('assignment' => $grade_info->iteminstance, 'userid' => $userId),'status');
                        if($grade->grade == null && $submitionState && ($submitionState->status == 'submitted')) {
                            $non_grated_activities[] = '<a href=' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $activity -> cm. '&action=grading>' . $activity->name . '</a>';
                        }
                    }
                }
            }
        }
        return empty($non_grated_activities) ? get_string('noWaitingGradedWorkActivity',
            'block_trackingdashboard') : $non_grated_activities;
    }
}