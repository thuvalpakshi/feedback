<?php
/**
 * Delete feedback entity
 *
 * @package Feedback
 */

$feedback_guid = get_input('guid');
$feedback = get_entity($feedback_guid);

if (elgg_instanceof($feedback, 'object', 'feedback') && $feedback->canEdit()) {
	$container = get_entity($feedback->container_guid);
	if ($feedback->delete()) {
		system_message(elgg_echo('feedback:message:deleted_post'));
		if (elgg_instanceof($container, 'group')) {
			forward("feedback/group/$container->guid/all");
		} else {
			forward("feedback/owner/$container->username");
		}
	} else {
		register_error(elgg_echo('feedback:error:cannot_delete_post'));
	}
} else {
	register_error(elgg_echo('feedback:error:post_not_found'));
}

forward(REFERER);