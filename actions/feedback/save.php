<?php
/**
 * Save feedback entity
 *
 * @package Feedback
 */

// start a new sticky form session in case of failure
elgg_make_sticky_form('feedback');

// save or preview
$save = (bool)get_input('save');

// store errors to pass along
$error = FALSE;
$error_forward_url = REFERER;
$user = elgg_get_logged_in_user_entity();

// edit or create a new entity
$guid = get_input('guid');

if ($guid) {
	$entity = get_entity($guid);
	if (elgg_instanceof($entity, 'object', 'feedback') && $entity->canEdit()) {
		$feedback = $entity;
	} else {
		register_error(elgg_echo('feedback:error:post_not_found'));
		forward(get_input('forward', REFERER));
	}

	// save some data for revisions once we save the new edit
	$revision_text = $feedback->description;
	$new_post = $feedback->new_post;
} else {
	$feedback = new ElggFeedback();
	$feedback->subtype = 'feedback';
	$new_post = TRUE;
}

// set the previous status for the hooks to update the time_created and river entries
$old_status = $feedback->status;

// set defaults and required values.
$values = array(
	'title' => '',
	'description' => '',
	'status' => 'draft',
	'access_id' => ACCESS_DEFAULT,
	'comments_on' => 'On',
	'excerpt' => '',
	'tags' => '',
	'container_guid' => (int)get_input('container_guid'),
);

// fail if a required entity isn't set
$required = array('title', 'description');

// load from POST and do sanity and access checking
foreach ($values as $name => $default) {
	$value = get_input($name, $default);

	if (in_array($name, $required) && empty($value)) {
		$error = elgg_echo("feedback:error:missing:$name");
	}

	if ($error) {
		break;
	}

	switch ($name) {
		case 'tags':
			if ($value) {
				$values[$name] = string_to_tag_array($value);
			} else {
				unset ($values[$name]);
			}
			break;

		case 'excerpt':
			if ($value) {
				$values[$name] = elgg_get_excerpt($value);
			}
			break;

		case 'container_guid':
			// this can't be empty or saving the base entity fails
			if (!empty($value)) {
				if (can_write_to_container($user->getGUID(), $value)) {
					$values[$name] = $value;
				} else {
					$error = elgg_echo("feedback:error:cannot_write_to_container");
				}
			} else {
				unset($values[$name]);
			}
			break;

		// don't try to set the guid
		case 'guid':
			unset($values['guid']);
			break;

		default:
			$values[$name] = $value;
			break;
	}
}

// if preview, force status to be draft
if ($save == false) {
	$values['status'] = 'draft';
}

// assign values to the entity, stopping on error.
if (!$error) {
	foreach ($values as $name => $value) {
		if (FALSE === ($feedback->$name = $value)) {
			$error = elgg_echo('feedback:error:cannot_save' . "$name=$value");
			break;
		}
	}
}

// only try to save base entity if no errors
if (!$error) {
	if ($feedback->save()) {
		// remove sticky form entries
		elgg_clear_sticky_form('feedback');

		// remove autosave draft if exists
		$feedback->deleteAnnotations('feedback_auto_save');

		// no longer a brand new post.
		$feedback->deleteMetadata('new_post');

		// if this was an edit, create a revision annotation
		if (!$new_post && $revision_text) {
			$feedback->annotate('feedback_revision', $revision_text);
		}

		system_message(elgg_echo('feedback:message:saved'));

		$status = $feedback->status;

		// add to river if changing status or published, regardless of new post
		// because we remove it for drafts.
		if (($new_post || $old_status == 'draft') && $status == 'published') {
			add_to_river('river/object/feedback/create', 'create', $feedback->owner_guid, $feedback->getGUID());

			if ($guid) {
				$feedback->time_created = time();
				$feedback->save();
			}
		} elseif ($old_status == 'published' && $status == 'draft') {
			elgg_delete_river(array(
				'object_guid' => $feedback->guid,
				'action_type' => 'create',
			));
		}

		if ($feedback->status == 'published' || $save == false) {
			forward($feedback->getURL());
		} else {
			forward("feedback/edit/$feedback->guid");
		}
	} else {
		register_error(elgg_echo('feedback:error:cannot_save'));
		forward($error_forward_url);
	}
} else {
	register_error($error);
	forward($error_forward_url);
}