/**
 * Account settings component
 */
"use strict";
/* global app, trans, launchToast */

$(function () {
    // AI auto-reply toggle
    $('#ai_auto_reply_toggle').on('change', function () {
        // Checkbox checked = creator wants AI active = paused is false
        var paused = !$(this).prop('checked');
        AccountSettings.saveAiAutoReplyState(paused);
    });
});

// eslint-disable-next-line no-unused-vars
var AccountSettings = {

    saveAiAutoReplyState: function (paused) {
        $.ajax({
            type: 'POST',
            data: {
                'key': 'ai_auto_reply_paused',
                'value': paused ? 'true' : 'false'
            },
            dataType: 'json',
            url: app.baseUrl + '/my/settings/save',
            success: function (result) {
                if (result.success) {
                    launchToast('success', trans('Success'), trans('Setting saved'));
                } else {
                    launchToast('danger', trans('Error'), trans('Setting save failed'));
                }
            },
            error: function () {
                launchToast('danger', trans('Error'), trans('Setting save failed'));
            }
        });
    }

};
