<?php
/**
 * Feedbacks
 *
 * @package Feedback
 *
 * @todo
 * - Either drop support for "publish date" or duplicate more entity getter
 * functions to work with a non-standard time_created.
 * - Pingbacks
 * - Notifications
 * - River entry for posts saved as drafts and later published
 */

elgg_register_event_handler('init', 'system', 'feedback_init');

/**
 * Init feedback plugin.
 */
function feedback_init() {

	elgg_register_library('elgg:feedback', elgg_get_plugins_path() . 'feedback/lib/feedback.php');

	// add a site navigation item
	$item = new ElggMenuItem('feedback', elgg_echo('feedback:feedbacks'), 'feedback/all');
	elgg_register_menu_item('site', $item);

	elgg_register_event_handler('upgrade', 'upgrade', 'feedback_run_upgrades');

	// add to the main css
	elgg_extend_view('css/elgg', 'feedback/css');

	// register the feedback's JavaScript
	$feedback_js = elgg_get_simplecache_url('js', 'feedback/save_draft');
	elgg_register_simplecache_view('js/feedback/save_draft');
	elgg_register_js('elgg.feedback', $feedback_js);

	// routing of urls
	elgg_register_page_handler('feedback', 'feedback_page_handler');

	// override the default url to view a feedback object
	elgg_register_entity_url_handler('object', 'feedback', 'feedback_url_handler');

	// notifications
	register_notification_object('object', 'feedback', elgg_echo('feedback:newpost'));
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'feedback_notify_message');

	// add feedback link to
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'feedback_owner_block_menu');

	// pingbacks
	//elgg_register_event_handler('create', 'object', 'feedback_incoming_ping');
	//elgg_register_plugin_hook_handler('pingback:object:subtypes', 'object', 'feedback_pingback_subtypes');

	// Register for search.
	elgg_register_entity_type('object', 'feedback');

	// Add group option
	add_group_tool_option('feedback', elgg_echo('feedback:enablefeedback'), true);
	elgg_extend_view('groups/tool_latest', 'feedback/group_module');

	// add a feedback widget
	elgg_register_widget_type('feedback', elgg_echo('feedback'), elgg_echo('feedback:widget:description'), 'profile');

	// register actions
	$action_path = elgg_get_plugins_path() . 'feedback/actions/feedback';
	elgg_register_action('feedback/save', "$action_path/save.php");
	elgg_register_action('feedback/auto_save_revision', "$action_path/auto_save_revision.php");
	elgg_register_action('feedback/delete', "$action_path/delete.php");

	// entity menu
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'feedback_entity_menu_setup');

	// ecml
	elgg_register_plugin_hook_handler('get_views', 'ecml', 'feedback_ecml_views_hook');
}

/**
 * Dispatches feedback pages.
 * URLs take the form of
 *  All feedbacks:       feedback/all
 *  User's feedbacks:    feedback/owner/<username>
 *  Friends' feedback:   feedback/friends/<username>
 *  User's archives: feedback/archives/<username>/<time_start>/<time_stop>
 *  Feedback post:       feedback/view/<guid>/<title>
 *  New post:        feedback/add/<guid>
 *  Edit post:       feedback/edit/<guid>/<revision>
 *  Preview post:    feedback/preview/<guid>
 *  Group feedback:      feedback/group/<guid>/all
 *
 * Title is ignored
 *
 * @todo no archives for all feedbacks or friends
 *
 * @param array $page
 * @return bool
 */
function feedback_page_handler($page) {

	elgg_load_library('elgg:feedback');

	// @todo remove the forwarder in 1.9
	// forward to correct URL for feedback pages pre-1.7.5
	feedback_url_forwarder($page);

	// push all feedbacks breadcrumb
	elgg_push_breadcrumb(elgg_echo('feedback:feedbacks'), "feedback/all");

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	$page_type = $page[0];
	switch ($page_type) {
		case 'owner':
			$user = get_user_by_username($page[1]);
			$params = feedback_get_page_content_list($user->guid);
			break;
		case 'friends':
			$user = get_user_by_username($page[1]);
			$params = feedback_get_page_content_friends($user->guid);
			break;
		case 'archive':
			$user = get_user_by_username($page[1]);
			$params = feedback_get_page_content_archive($user->guid, $page[2], $page[3]);
			break;
		case 'view':
		case 'read': // Elgg 1.7 compatibility
			$params = feedback_get_page_content_read($page[1]);
			break;
		case 'add':
			gatekeeper();
			$params = feedback_get_page_content_edit($page_type, $page[1]);
			break;
		case 'edit':
			gatekeeper();
			$params = feedback_get_page_content_edit($page_type, $page[1], $page[2]);
			break;
		case 'group':
			if ($page[2] == 'all') {
				$params = feedback_get_page_content_list($page[1]);
			} else {
				$params = feedback_get_page_content_archive($page[1], $page[3], $page[4]);
			}
			break;
		case 'all':
			$params = feedback_get_page_content_list();
			break;
		default:
			return false;
	}

	if (isset($params['sidebar'])) {
		$params['sidebar'] .= elgg_view('feedback/sidebar', array('page' => $page_type));
	} else {
		$params['sidebar'] = elgg_view('feedback/sidebar', array('page' => $page_type));
	}

	$body = elgg_view_layout('content', $params);

	echo elgg_view_page($params['title'], $body);
	return true;
}

/**
 * Format and return the URL for feedbacks.
 *
 * @param ElggObject $entity Feedback object
 * @return string URL of feedback.
 */
function feedback_url_handler($entity) {
	if (!$entity->getOwnerEntity()) {
		// default to a standard view if no owner.
		return FALSE;
	}

	$friendly_title = elgg_get_friendly_title($entity->title);

	return "feedback/view/{$entity->guid}/$friendly_title";
}

/**
 * Add a menu item to an ownerblock
 */
function feedback_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "feedback/owner/{$params['entity']->username}";
		$item = new ElggMenuItem('feedback', elgg_echo('feedback'), $url);
		$return[] = $item;
	} else {
		if ($params['entity']->feedback_enable != "no") {
			$url = "feedback/group/{$params['entity']->guid}/all";
			$item = new ElggMenuItem('feedback', elgg_echo('feedback:group'), $url);
			$return[] = $item;
		}
	}

	return $return;
}

/**
 * Add particular feedback links/info to entity menu
 */
function feedback_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'feedback') {
		return $return;
	}

	if ($entity->canEdit() && $entity->status != 'published') {
		$status_text = elgg_echo("feedback:status:{$entity->status}");
		$options = array(
			'name' => 'published_status',
			'text' => "<span>$status_text</span>",
			'href' => false,
			'priority' => 150,
		);
		$return[] = ElggMenuItem::factory($options);
	}

	return $return;
}

/**
 * Register feedbacks with ECML.
 */
function feedback_ecml_views_hook($hook, $entity_type, $return_value, $params) {
	$return_value['object/feedback'] = elgg_echo('feedback:feedbacks');

	return $return_value;
}

/**
 * Upgrade from 1.7 to 1.8.
 */
function feedback_run_upgrades($event, $type, $details) {
	$feedback_upgrade_version = elgg_get_plugin_setting('upgrade_version', 'feedbacks');

	if (!$feedback_upgrade_version) {
		 // When upgrading, check if the ElggFeedback class has been registered as this
		 // was added in Elgg 1.8
		if (!update_subtype('object', 'feedback', 'ElggFeedback')) {
			add_subtype('object', 'feedback', 'ElggFeedback');
		}

		elgg_set_plugin_setting('upgrade_version', 1, 'feedbacks');
	}
}
