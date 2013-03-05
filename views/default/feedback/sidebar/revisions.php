<?php
/**
 * Feedback sidebar menu showing revisions
 *
 * @package Feedback
 */

//If editing a post, show the previous revisions and drafts.
$feedback = elgg_extract('entity', $vars, FALSE);

if (elgg_instanceof($feedback, 'object', 'feedback') && $feedback->canEdit()) {
	$owner = $feedback->getOwnerEntity();
	$revisions = array();

	$auto_save_annotations = $feedback->getAnnotations('feedback_auto_save', 1);
	if ($auto_save_annotations) {
		$revisions[] = $auto_save_annotations[0];
	}

	// count(FALSE) == 1!  AHHH!!!
	$saved_revisions = $feedback->getAnnotations('feedback_revision', 10, 0, 'time_created DESC');
	if ($saved_revisions) {
		$revision_count = count($saved_revisions);
	} else {
		$revision_count = 0;
	}

	$revisions = array_merge($revisions, $saved_revisions);

	if ($revisions) {
		$title = elgg_echo('feedback:revisions');

		$n = count($revisions);
		$body = '<ul class="feedback-revisions">';

		$load_base_url = "feedback/edit/{$feedback->getGUID()}";

		// show the "published revision"
		if ($feedback->status == 'published') {
			$load = elgg_view('output/url', array(
				'href' => $load_base_url,
				'text' => elgg_echo('feedback:status:published'),
				'is_trusted' => true,
			));

			$time = "<span class='elgg-subtext'>"
				. elgg_view_friendly_time($feedback->time_created) . "</span>";

			$body .= "<li>$load : $time</li>";
		}

		foreach ($revisions as $revision) {
			$time = "<span class='elgg-subtext'>"
				. elgg_view_friendly_time($revision->time_created) . "</span>";

			if ($revision->name == 'feedback_auto_save') {
				$revision_lang = elgg_echo('feedback:auto_saved_revision');
			} else {
				$revision_lang = elgg_echo('feedback:revision') . " $n";
			}
			$load = elgg_view('output/url', array(
				'href' => "$load_base_url/$revision->id",
				'text' => $revision_lang,
				'is_trusted' => true,
			));

			$text = "$load: $time";
			$class = 'class="auto-saved"';

			$n--;

			$body .= "<li $class>$text</li>";
		}

		$body .= '</ul>';

		echo elgg_view_module('aside', $title, $body);
	}
}