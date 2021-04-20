/**
 * Javascript
 *
 * @package    local
 * @subpackage lambda_time_tracker
 * @copyright  Lambda Solutions
 */

define(['jquery', 'core/log'], function($, log) {

    return {
        init: function(userid, courseid, timeTackingInterval, timeTrackingInterrupt, timeTrackingUseInterrupt, timeSessionTimeout,
            confirmationMessage, $tracker_mod_info) {

        var tracker_mod_info = JSON.parse($tracker_mod_info);

        // debug
        log.debug('Lambda Time Tracker plugin AMD module initialised');
        $(document).ready(function() {

            // declaration
            var cms;
            var cmsa;
            var cmsaid;

            // log data object
            var logData=[];
            // log interval time
            var logIntervalTime = timeTackingInterval * 60 * 1000;
            // presence interval time
            var presenceIntervalTime = timeTrackingInterrupt * 60 * 1000;
            // session timeout in seconds
            var sessionRestrictTimeoutTime = timeSessionTimeout * 1000;
            // ajax url
            var pluginurl = M.cfg.wwwroot + '/local/lambda_time_tracker';
            var url = pluginurl + '/log.php';

            // get cmid from body class if exist
            var bodyClasses = document.body.className;
            var courseModulesPossition = bodyClasses.indexOf(' cmid-');

            // intervals
            var logInterval;
            var presenceInterval;

            // debug message
            var infoText;

            // unlock item id
            var unlockItemId = 0;

            function openDialog(selector) {
                $('body').addClass('modal-open');
                $('body').append('<div class="modal-backdrop fade in"></div>');
                $(selector).addClass('in');
                $(selector).show();
            }

            function closeDialog(selector) {
                $(selector).removeClass('in');
                $(selector).hide();
                $('body').removeClass('modal-open');
                $('div.modal-backdrop.in').remove();
            }

            // log interval
            function setLogInterval() {

                $.ajax({
                    url: url,
                    contentType: 'application/x-www-form-urlencoded',
                    data: {'logData':logData},
                    method: 'POST',

                    success: function(result){
                        // in case result data is not a null
                        if(result.data !== null) {
                            $('#block-course-time-tracker-total-spent-time').html(result.data);
                            if(result.showpopup) {
                                if(tracker_mod_info !== null && tracker_mod_info.length !== 0 ) {
                                    $.each( result.data_mod, function( index, value ) {
                                        $.each( tracker_mod_info, function( index, value_local ) {
                                            if( value.id == value_local.id && value_local.unlock) {
                                                unlockItemId = value.id;
                                                // open course time requirement complete modal
                                                // $('#block-course-time-tracker-modal').modal();
                                                openDialog('#block-course-time-tracker-modal');
                                            }
                                        });
                                    });
                                }
                            }
                        }
                        if(result.error !== null && typeof result.error !== 'undefined') {
                            // clear intervals
                            clearInterval(logInterval);
                            clearInterval(presenceInterval);

                            // debud
                            log.debug('Lambda Time Tracker ajax log error message: ' + result.error);
                        }
                    },

                    error: function (xhr, ajaxOptions, thrownError) {
                        // debug
                        log.debug('Lambda Time Tracker ajax log error code: ' + xhr.status);
                        log.debug('Lambda Time Tracker ajax log error message: ' + thrownError);
                    }
                });
            }

            // presence interval
            function setPresenceInterval() {
                // display modal
                // $('#time-tracker-interrupt-modal').modal();
                openDialog('#time-tracker-interrupt-modal');

                // clear intervals
                clearInterval(logInterval);
                clearInterval(presenceInterval);
            }

            // session restrict interval
            function setSessionRestrictTimeout() {
                // clear intervals
                clearInterval(logInterval);
                log.debug('Lambda Time Tracker session timeout');
            }

            // MODAL
            // create div to load modal into it
            $('<div id="time-tracker-interrupt"></div>').appendTo( $( 'body' ) );
            // load modal html
            $('#time-tracker-interrupt').load(pluginurl + '/interrupt_modal.html', function(responseTxt, statusTxt, xhr){
                if(statusTxt == 'success') {
                    // debug
                    log.debug('Lambda Time Tracker interrupt modal loaded');

                    $('#time-tracker-interrupt-modal-confirmation-message').html(confirmationMessage);
                    $('#time-tracker-interrupt-modal button').click(function () {
                        closeDialog('#time-tracker-interrupt-modal');

                        // debug
                        infoText = 'User confirmed his/her presence!';
                        log.debug( infoText );

                        // restart log interval
                        logInterval = setInterval(setLogInterval, logIntervalTime);
                        // restart presence interval
                        presenceInterval = setInterval( setPresenceInterval, presenceIntervalTime);
                    });

                    // course time tracker unlock button handler
                    $('#block-course-time-tracker-modal-unlock-reload').click(function(){
                        location.reload(true);
                    });
                    $('#block-course-time-tracker-modal-unlock-skip').click(function(){
                        closeDialog('#block-course-time-tracker-modal');
                        $.each( tracker_mod_info, function( index, value ) {
                            if(value.id == unlockItemId) {
                                // set unlock value to false
                                value.unlock = false;
                            }
                        });
                    });
                    $('#block-course-time-tracker-modal button.close').click(function() {
                        closeDialog('#block-course-time-tracker-modal');
                    });
                }
                if(statusTxt == 'error'){
                    // debug
                    log.debug('Error: ' + xhr.status + ': ' + xhr.statusText);
                }
            });

            // COURSE
            // if course is defined and bigger than 1 (front page)
            if(courseid !== null && courseid > 1) {
                if(courseModulesPossition !== -1) {
                    cms = bodyClasses.substring(courseModulesPossition + 1);
                    cmsa = cms.split(' ');
                    cmsaid = cmsa[0].split('-');
                    logData.push( {'courseid':courseid, 'cmid':cmsaid[1], 'userid':userid} );
                } else {
                    logData.push( {'courseid':courseid, 'userid':userid} );
                }

                // initialize log interval
                logInterval = setInterval(setLogInterval, logIntervalTime);

                if(timeTrackingUseInterrupt == 1) {
                    // initialize presence interval if timeTrackingUseInterrupt is set to true (1)
                    presenceInterval = setInterval(setPresenceInterval, presenceIntervalTime);
                } else {
                    // initialize session restriction timeout
                    setTimeout(setSessionRestrictTimeout, sessionRestrictTimeoutTime);
                }
            }
        });
    }};
});
