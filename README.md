# Lambda Solutions Time Tracker Local Plugin

This plugin periodically executes an ajax call to register user presence in course. The plugin has a user presence check alert notification popup window which will pause the ajax calls until the user clicks the button registering them as still here in which the alert window will close and the user can resume learning in the course.

This plugin is part of a set of plugins (Lambda Time Spent Learning, Lambda Course Time Tracker - Block, Lambda Time Spent - Activity Restriction) and is dependent on the Lambda Time Spent Learning (Lambda Dedication) plugin.

Minimum Moodle and Totara version is 2.9 as the JavaScript requirements of the plugin are dependent on the JS libraries in this version up.

### How it works

Visit the documentation at https://lambdasolutions.atlassian.net/wiki/x/Z4CyAQ

### Installation:

1. Unpack the zip file into the local/ directory. A new directory will be created called lambda_time_tracker.
2. Go to Site administration > Notifications to complete the plugin installation.