<?php
/**
 * Feedback English language file.
 *
 */

$english = array(
	'feedback' => 'Feedbacks',
	'feedback:feedbacks' => 'Feedbacks',
	'feedback:revisions' => 'Revisions',
	'feedback:archives' => 'Archives',
	'feedback:feedback' => 'Feedback',
	'item:object:feedback' => 'Feedbacks',

	'feedback:title:user_feedbacks' => '%s\'s feedbacks',
	'feedback:title:all_feedbacks' => 'All site feedbacks',
	'feedback:title:friends' => 'Friends\' feedbacks',

	'feedback:group' => 'Group feedback',
	'feedback:enablefeedback' => 'Enable group feedback',
	'feedback:write' => 'Write a feedback post',

	// Editing
	'feedback:add' => 'Add a feedback or suggestion',
	'feedback:edit' => 'Edit feedback post',
	'feedback:excerpt' => 'Suggestion Type (eg. Improvment, New, etc)',
	'feedback:body' => 'Your Feedback',
	'feedback:save_status' => 'Last saved: ',
	'feedback:never' => 'Never',

	// Statuses
	'feedback:status' => 'Status',
	'feedback:status:draft' => 'Draft',
	'feedback:status:published' => 'Published',
	'feedback:status:unsaved_draft' => 'Unsaved Draft',

	'feedback:revision' => 'Revision',
	'feedback:auto_saved_revision' => 'Auto Saved Revision',

	// messages
	'feedback:message:saved' => 'Feedback post saved.',
	'feedback:error:cannot_save' => 'Cannot save feedback post.',
	'feedback:error:cannot_write_to_container' => 'Insufficient access to save feedback to group.',
	'feedback:error:post_not_found' => 'This post has been removed, is invalid, or you do not have permission to view it.',
	'feedback:messages:warning:draft' => 'There is an unsaved draft of this post!',
	'feedback:edit_revision_notice' => '(Old version)',
	'feedback:message:deleted_post' => 'Feedback post deleted.',
	'feedback:error:cannot_delete_post' => 'Cannot delete feedback post.',
	'feedback:none' => 'No feedback posts',
	'feedback:error:missing:title' => 'Please enter a feedback title!',
	'feedback:error:missing:description' => 'Please enter the body of your feedback!',
	'feedback:error:cannot_edit_post' => 'This post may not exist or you may not have permissions to edit it.',
	'feedback:error:revision_not_found' => 'Cannot find this revision.',

	// river
	'river:create:object:feedback' => '%s published a feedback post %s',
	'river:comment:object:feedback' => '%s commented on the feedback %s',

	// notifications
	'feedback:newpost' => 'A new feedback post',

	// widget
	'feedback:widget:description' => 'Display your latest feedback posts',
	'feedback:morefeedbacks' => 'More feedback posts',
	'feedback:numbertodisplay' => 'Number of feedback posts to display',
	'feedback:nofeedbacks' => 'No feedback posts'
);

add_translation('en', $english);
