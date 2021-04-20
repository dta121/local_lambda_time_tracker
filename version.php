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

defined('MOODLE_INTERNAL') || die();

$plugin = new stdClass();
$plugin->version   = 2018012200;      // The current module version (Date: YYYYMMDDXX)
$plugin->release = '1.1.29.3';
$plugin->requires  = 2015051100;      // Requires this Moodle version
$plugin->maturity = MATURITY_STABLE;
$plugin->component = 'local_lambda_time_tracker'; // To check on upgrade, that module sits in correct place
$plugin->dependencies = array(
    'local_lambda_dedication' => ANY_VERSION,   // The Lambda Dedication local plugin must be present (any version).
);
