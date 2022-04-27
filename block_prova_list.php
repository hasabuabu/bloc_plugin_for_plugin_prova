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
 * Course list block.
 *
 * @package    block_prova_list
 * @copyright  2022, Hasan Abuzoor <a21hasabuabu@inspedralbes.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//include_once($CFG->dirroot . '/course/lib.php');
//require_once("../../config.php");
require_once($CFG->dirroot . '/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/report/prova/renderables.php');

class block_prova_list extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_prova_list');
    }

    function has_config() {
        return true;
    }

    function get_content() {
 
        if($this->content !== NULL) {
            return $this->content;
        }
        global $CFG, $DB;
        $pageparams = new mod_attendance_view_page_params();

$id                     = required_param('id', PARAM_INT);
$edit                   = optional_param('edit', -1, PARAM_BOOL);
$pageparams->studentid  = optional_param('studentid', null, PARAM_INT);
$pageparams->mode       = optional_param('mode', 1, PARAM_INT);
$pageparams->view       = optional_param('view', null, PARAM_INT);
$pageparams->curdate    = optional_param('curdate', null, PARAM_INT);
$pageparams->groupby    = optional_param('groupby', 'course', PARAM_ALPHA);
$pageparams->sesscourses = optional_param('sesscourses', 'current', PARAM_ALPHA);

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$attendance    = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

//require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$pageparams->init($cm);

$att = new mod_attendance_structure($attendance, $cm, $course, $context, $pageparams);
$printUsersTable = new stdclass();
$usersCourse = new prova_print_users_attendance($printUsersTable, $att, $context);
        $this->content = new stdClass;
        $this->content->items = array();
        //$this->content->abajo = array();
        $this->content->items[] = "<a href=\"$CFG->wwwroot/report/prova/view.php?id={$id}\">".get_string('user_list', 'block_prova_list').'</a>';
        $datos = $usersCourse->returnDatos();
        $this->content->items[] = '<br><h6>Users with a presence less than 80%</h6>';
        $this->title = 'Prova';
        foreach($datos as $user => $value){
            if(intval(explode(",",$datos[$user]['total'])[0]) < 80 )
                $this->content->items[] = "<p>- &emsp;".fullname(core_user::get_user($user)).'</p>';
        }
        return $this->content;
    }
   
}


