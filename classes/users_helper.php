<?php

namespace block_trackingdashboard;

defined('MOODLE_INTERNAL') || die();

class users_helper
{

    /**
     * Returns a list of users without teachers and current user
     *
     * @param array $coursesTaughtByUser
     * @param int $userid
     * @return array
     */
    public static function get_enrolled_users($coursesTaughtByUser, $userid) : array {
        $enrolledUsers = [];
        $enrolledUsersByCourses = [];
        foreach($coursesTaughtByUser as $userCourse) {
            $courseContext = \context_course::instance($userCourse->id);

            if(groups_get_course_groupmode($userCourse) == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $courseContext, $userid)) {
                $groupIds = groups_get_user_groups($userCourse->id);
                foreach($groupIds[0] as $groupId) {
                    if(empty($enrolledUsers)) {
                        $enrolledUsers = groups_get_members($groupId);
                    } else {
                        foreach(groups_get_members($groupId) as $groupMember) {
                            $enrolledUsers[] = $groupMember;
                        }
                    }
                }

            } else {
                $enrolledUsers = get_enrolled_users($courseContext,  '',  0,  'u.*',  '',  0,  0);
            }
            $enrolledUsersByCourses[$userCourse->id] = users_helper::filter_out_current_user_and_teachers($enrolledUsers, $userid, $courseContext);
            $enrolledUsers = [];
        }
        return $enrolledUsersByCourses;
    }

    /**
     * Filter out teachers and current user
     *
     * @param array $userList
     * @param int $currentUserid
     * @param object $coursecontext
     * @return array
     */
    public static function filter_out_current_user_and_teachers($userList, $currentUserid, $coursecontext) {
        $filteredArray = [];
        foreach($userList as $user) {
            if($user->id != $currentUserid && !has_capability('block/trackingdashboard:listuser', $coursecontext, $user)) {
                $filteredArray[] = $user;
            }
        }
        return $filteredArray;
    }
}