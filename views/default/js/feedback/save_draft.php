<?php
/**
 * Save draft through ajax
 *
 * @package Feedback
 */
?>
elgg.provide('elgg.feedback');

/*
 * Attempt to save and update the input with the guid.
 */
elgg.feedback.saveDraftCallback = function(data, textStatus, XHR) {
	if (textStatus == 'success' && data.success == true) {
		var form = $('form[name=feedback_post]');

		// update the guid input element for new posts that now have a guid
		form.find('input[name=guid]').val(data.guid);

		oldDescription = form.find('textarea[name=description]').val();

		var d = new Date();
		var mins = d.getMinutes() + '';
		if (mins.length == 1) {
			mins = '0' + mins;
		}
		$(".feedback-save-status-time").html(d.toLocaleDateString() + " @ " + d.getHours() + ":" + mins);
	} else {
		$(".feedback-save-status-time").html(elgg.echo('error'));
	}
};

elgg.feedback.saveDraft = function() {
	if (typeof(tinyMCE) != 'undefined') {
		tinyMCE.triggerSave();
	}

	// only save on changed content
	var form = $('form[name=feedback_post]');
	var description = form.find('textarea[name=description]').val();
	var title = form.find('input[name=title]').val();

	if (!(description && title) || (description == oldDescription)) {
		return false;
	}

	var draftURL = elgg.config.wwwroot + "action/feedback/auto_save_revision";
	var postData = form.serializeArray();

	// force draft status
	$(postData).each(function(i, e) {
		if (e.name == 'status') {
			e.value = 'draft';
		}
	});

	$.post(draftURL, postData, elgg.feedback.saveDraftCallback, 'json');
};

elgg.feedback.init = function() {
	// get a copy of the body to compare for auto save
	oldDescription = $('form[name=feedback_post]').find('textarea[name=description]').val();
	
	setInterval(elgg.feedback.saveDraft, 60000);
};

elgg.register_hook_handler('init', 'system', elgg.feedback.init);