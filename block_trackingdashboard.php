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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_trackingdashboard\courses_helper;
use block_trackingdashboard\display_helper;
use block_trackingdashboard\users_helper;

defined('MOODLE_INTERNAL') || die();

class block_trackingdashboard extends block_base {

    public array $coursesTeachedByUser = [];

    public array $enrolledUsersByCourse = [];

    public array $tableData = [];

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
        $this->page->requires->css(new \moodle_url('https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css'));
        $this->page->requires->css(new \moodle_url('https://cdn.datatables.net/v/bs5/dt-1.10.25/datatables.min.css'));
        $this->page->requires->js(new \moodle_url('https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/js/bootstrap.bundle.min.js'), true);
        $this->page->requires->js(new \moodle_url('https://cdn.datatables.net/v/bs5/dt-1.10.25/datatables.min.js'), true);
        $this->page->requires->css(new \moodle_url(new \moodle_url($CFG->wwwroot . '/blocks/trackingdashboard/styles.css')));
        $this->page->requires->js(new \moodle_url($CFG->wwwroot . '/blocks/trackingdashboard/js/my_datatables.js'));

        // Check if null
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->coursesTeachedByUser = courses_helper::get_user_courses();

        // No courses are teached, return
        if(count($this->coursesTeachedByUser) == 0) {
            $this->content->text = get_string('noCourse', 'block_trackingdashboard');
            return $this->content;
        }

        $this->enrolledUsersByCourse = users_helper::get_enrolled_users($this->coursesTeachedByUser, $userid);

        $this->content = new stdClass();
        $this->tableData = display_helper::build_table_data($this->enrolledUsersByCourse, $this->coursesTeachedByUser, $CFG, $this->page);
        $htmlTable = display_helper::build_table($this->tableData);
        $this->content->text = $htmlTable;


        return $this->content;
    }
}


