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

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

	$ADMIN->add('root', new admin_category('lambda_time_tracker', get_string('pluginname', 'local_lambda_time_tracker')));
	$settings = new admin_settingpage('local_lambda_time_tracker', get_string('pluginname', 'local_lambda_time_tracker'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox('local_lambda_time_tracker/lambda_time_tracker_enabled', get_string('lambda_time_tracker_enabled', 'local_lambda_time_tracker'),
                       get_string('lambda_time_tracker_enabled_description', 'local_lambda_time_tracker'), 1, 1));

    $settings->add(new admin_setting_configtext('local_lambda_time_tracker/lambda_time_tracker_interval', get_string('lambda_time_tracker_interval', 'local_lambda_time_tracker'),
                       get_string('lambda_time_tracker_interval_description', 'local_lambda_time_tracker'), 5, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_lambda_time_tracker/lambda_time_tracker_interrupt', get_string('lambda_time_tracker_interrupt', 'local_lambda_time_tracker'),
                       get_string('lambda_time_tracker_interrupt_description', 'local_lambda_time_tracker'), 15, PARAM_INT));

    $choices = array(
	    0 => get_string('no', 'core'),
	    1 => get_string('yes', 'core')	    
	);

    $settings->add(new admin_setting_configselect('local_lambda_time_tracker/lambda_time_tracker_use_interrupt', get_string('lambda_time_tracker_use_interrupt', 'local_lambda_time_tracker'),
                       get_string('lambda_time_tracker_use_interrupt_description', 'local_lambda_time_tracker'), 0, $choices));

}