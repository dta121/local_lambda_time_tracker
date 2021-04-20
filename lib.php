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


global $DB, $PAGE, $USER, $COURSE, $tracker_mod_info;

// workaround to include lib file on every page
function local_lambda_time_tracker_extend_navigation(global_navigation $nav) {}
function local_lambda_time_tracker_extends_navigation(global_navigation $nav) {}

if (CLI_SCRIPT || AJAX_SCRIPT
        || $PAGE->pagelayout === 'login'
        || $PAGE->pagelayout === 'embedded'
        || $PAGE->pagelayout === 'popup'
        || $PAGE->pagelayout === 'base'
        || $PAGE->pagelayout === 'redirect'
        || $PAGE->pagelayout === 'frametop') {
    return;
}

// configuration settings
$config = get_config('local_lambda_time_tracker');

$config->lambda_time_tracker_interval = (isset($config->lambda_time_tracker_interval)) ? $config->lambda_time_tracker_interval : 5;
$config->lambda_time_tracker_interrupt = (isset($config->lambda_time_tracker_interrupt)) ? $config->lambda_time_tracker_interrupt : 15;
$config->lambda_time_tracker_use_interrupt = (isset($config->lambda_time_tracker_use_interrupt)) ? $config->lambda_time_tracker_use_interrupt : 0;
$config->lambda_time_tracker_enabled = (isset($config->lambda_time_tracker_enabled)) ? $config->lambda_time_tracker_enabled : 1;

// session
$sessiontimeout = get_config('core', 'sessiontimeout');

// confirmation message
$confirmationMessage = get_string('lambda_time_tracker_interrupt_confirmation_message', 'local_lambda_time_tracker');

// get total spent time on course
$spent_time = 0;
$result = $DB->get_records('local_ld_course',array('courseid'=>$COURSE->id, 'userid'=>$USER->id));
foreach ($result as $key => $value) {
    $spent_time = $result[$key]->totaldedication; // seconds
}

// get availabilities
$result = $DB->get_records('course_modules', array('course' => $COURSE->id), $sort='', $fields='id, module, instance, availability'); 

foreach ($result as $key => $value) {
    if(isset($value->availability)) {
        get_availability($value->id, json_decode($value->availability), $value->module, $value->instance, $spent_time);
    }
}

// get parameters for jquery init function
$params=[$USER->id, $COURSE->id, $config->lambda_time_tracker_interval, $config->lambda_time_tracker_interrupt, $config->lambda_time_tracker_use_interrupt, $sessiontimeout, $confirmationMessage, json_encode($tracker_mod_info) ];

// require jquery amd module if Time Tracker is enabled
if($config->lambda_time_tracker_enabled) {
	$PAGE->requires->js_call_amd('local_lambda_time_tracker/lambdatimetracker', 'init', $params);
}

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
                    $mod_obj->unlock = true;
                    if( ($option == "&" || $option == "|") && $spent_time < ($val->hours * 3600 + $val->minutes * 60) ) { 
                        $tracker_mod_info[] = $mod_obj;
                    }
                }
            }
        }      
    }
}
