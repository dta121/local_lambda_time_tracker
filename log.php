<?php
/*
 * This file is part local lambda time tracker package.
 *
 * Copyright (c) 2016 Lambda Solutions
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @license MIT
 *
 */

define('AJAX_SCRIPT', true);

global $tracker_mod_info;


require_once('../../config.php');
require_once($CFG->dirroot . '/local/lambda_dedication/classes/observer.php');


$PAGE->set_context(context_system::instance());
require_login();


$data = $_POST['logData'];
$courseid = null;
$cmid = null;
$userid = null;

if(isset($data[0]['cmid'])) {
	$cmid = $data[0]['cmid'];
}
if(isset($data[0]['courseid'])) {
	$courseid = $data[0]['courseid'];
}
if(isset($data[0]['userid'])) {
	$userid = $data[0]['userid'];
}

// create object that will be passed as param to observe_all method of observer class
$obs = new stdClass();
$obs->timecreated = time();
$obs->userid = $userid;
$obs->courseid = $courseid;
$obs->contextlevel = null;
$obs->contextinstanceid = null;

// set context level if course module if not null
if($cmid != null) {
	$obs->contextlevel = CONTEXT_MODULE;
	$obs->contextinstanceid = $cmid;
}

// update log
// call observe_all method and send $obs object that contains required parameters
$log = local_lambda_dedication\observer::observe_all($obs);

// retrive latest values for time spent
$spent_time = 0;
$result = $DB->get_records('local_ld_course',array('courseid'=>$courseid, 'userid'=>$userid));
foreach ($result as $key => $value) {
    $spent_time = $result[$key]->totaldedication; // seconds
}

$result = $DB->get_records('course_modules', array('course' => $courseid), $sort='', $fields='id, module, instance, availability'); 
foreach ($result as $key => $value) {
    if(isset($value->availability)) {
        get_availability($value->id, json_decode($value->availability), $value->module, $value->instance, $spent_time);
    }
}

$showpopup = false;
// course time tracker configuration settings
$coursecontext = context_course::instance($courseid);
$blockrecords = $DB->get_records('block_instances', array('blockname' => 'course_time_tracker', 'parentcontextid' => $coursecontext->id));
foreach ($blockrecords as $b){
    $blockinstance = block_instance('course_time_tracker', $b);
    if($blockinstance->config->showpopuppage && $blockinstance->config->showpopuppage == 1) {
        $showpopup = true;
    }
}

// spent time calculation
// hours
$spent_hours = time_to_hours($spent_time);
//minutes
$spent_minutes = time_to_minutes($spent_time);


$response = array('data'=>$spent_hours . ':' . $spent_minutes, 'data_mod'=>$tracker_mod_info, 'showpopup'=>$showpopup);
echo json_encode($response);


// helpers
function get_availability($id, $av, $module, $instance, $spent_time) {
    global $tracker_mod_info;
    if($av && is_array($av->c) && !empty($av->c) ) {
        $availability =  $av->c;
        
        foreach ($availability as $val) {
            $option = $av->op;
            if(isset($val->c)) {
                get_availability($id, $val, $module, $instance, $spent_time);
            } else {
                if($val->type == "lambda_timespent") {
                    $mod_obj = new stdClass; 
                    $mod_obj->id = $id . '_' . ($val->hours * 3600 + $val->minutes * 60);
                    if( ($option == "&" || $option == "|") && $spent_time > ($val->hours * 3600 + $val->minutes * 60) ) { 
                        $tracker_mod_info[] = $mod_obj;
                    }
                }
            }
        }      
    }
}

function time_to_minutes ($seconds) {
    if(floor($seconds / 60 % 60)<10 ){
        return '0'.floor($seconds / 60 % 60);
    } else {
        return floor($seconds / 60 % 60);
    }
}

function time_to_hours ($seconds) {
    return floor($seconds / 3600);
}

