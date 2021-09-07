<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing HTML block instances.
 *
 * @package   block_trackingdashboard
 * @copyright 2021 SOFAD
 */

use block_trackingdashboard\courses_helper;
use block_trackingdashboard\display_helper;
use block_trackingdashboard\users_helper;

defined('MOODLE_INTERNAL') || die();

class block_trackingdashboard extends block_base {

    public $coursesTaughtByUser = [];

    public $enrolledUsersByCourse = [];

    public $tableData = [];

    function init() {
        $this->title = get_string('pluginname', 'block_trackingdashboard');
    }

    function has_config()
    {
        return true;
    }

    function get_content() {
        global $USER, $CFG;
        $userid = $USER->id;

        $this->page->requires->jquery();
        $this->page->requires->css(new \moodle_url('https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.css'));
        $this->page->requires->css(new \moodle_url('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css'));
        $this->page->requires->css(new \moodle_url($CFG->wwwroot . '/blocks/trackingdashboard/styles.css'));
        $this->page->requires->js(new \moodle_url('https://cdn.jsdelivr.net/momentjs/latest/moment.min.js'), true);
        $this->page->requires->js(new \moodle_url('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js'), true);
        $this->page->requires->js(new \moodle_url('https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.js'), true);
        $this->page->requires->js(new \moodle_url($CFG->wwwroot . '/blocks/trackingdashboard/js/script.js'));

        // Check if null
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->coursesTaughtByUser = courses_helper::get_user_courses();

        // No courses are taught, return empty
        if(count($this->coursesTaughtByUser) == 0) {
            return '';
        }

        $this->enrolledUsersByCourse = users_helper::get_enrolled_users($this->coursesTaughtByUser, $userid);

        $this->content = new stdClass();

        $lang = display_helper::getLanguage();
        $this->content->text = '<p id="data-lang" style="display: none;">' . $lang . '</p>';

        $hasUsers = false;
        foreach($this->enrolledUsersByCourse as $enrolledUsersPerCourse) {
            if(count($enrolledUsersPerCourse) > 0) {
                $hasUsers = true;
            }
        }

        if($hasUsers) {
            $this->tableData = display_helper::build_table_data($this->enrolledUsersByCourse, $this->coursesTaughtByUser, $CFG, $this->page);
            $htmlTable = display_helper::build_table($this->tableData);
            $this->content->text .= $htmlTable;
        } else {
            // return specific string when no students are enrolled
            $this->content->text .= get_string('noUser', 'block_trackingdashboard');
        }


        return $this->content;
    }

}





