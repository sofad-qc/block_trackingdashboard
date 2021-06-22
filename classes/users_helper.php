<?php

namespace block_trackingdashboard;

defined('MOODLE_INTERNAL') || die();

class users_helper
{

    /**
     * Returns a list of users without teachers and current user
     *
     * @param array $coursesTeachedByUser
     * @param int $userid
     * @return array
     */
    public static function get_enrolled_users($coursesTeachedByUser, $userid) : array {
        $enrolledUsers = [];
        $enrolledUsersByCourses = [];
        foreach($coursesTeachedByUser as $userCourse) {
            $courseContext = \context_course::instance($userCourse->id);

            if(groups_get_course_groupmode($userCourse) == "1" && !has_capability('moodle/site:accessallgroups', $courseContext, $userid)) {
                $groupids = groups_get_user_groups($userCourse->id);
                foreach($groupids as $groupid) {
                    // $members = groups_get_members($groupid);
                    if(count($enrolledUsers) == 0) {
                        $enrolledUsers = groups_get_members($groupid[0]);
                    } else {
                        foreach(groups_get_members($groupid) as $groupMember) {
                            $enrolledUsers[] = $groupMember;
                        }
                    }
                }

            } else {
                $enrolledUsers = get_enrolled_users($courseContext,  '',  0,  'u.*',  '',  0,  0);
            }
            $enrolledUsersByCourses[$userCourse->id] = \block_trackingdashboard\users_helper::filter_out_current_user_and_teachers($enrolledUsers, $userid, $courseContext);
        }
        return $enrolledUsersByCourses;
    }

    /**
     * Filter out teachers and current user
     *
     * @param array $userList
     * @param int $currentUserid
     * @param context $coursecontext
     * @return array
     */
    function filter_out_current_user_and_teachers($userList, $currentUserid, $coursecontext) {
        $filteredArray = [];
        foreach($userList as $user) {
            if($user->id != $currentUserid && !has_capability('block/trackingdashboard:listuser', $coursecontext, $user)) {
                $filteredArray[] = $user;
            }
        }
        return $filteredArray;
    }

    /**
     * get user last access to course
     *$course, $user
     * @param course $course
     * @param user $user
     * @return boolean or date
     */
    function get_last_access($course, $user) {
        global $DB;
        return $DB->get_field('user_lastaccess', 'timeaccess', array('courseid' => $course->id, 'userid' => $user->id));
    }

}