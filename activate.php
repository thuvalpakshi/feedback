<?php
/**
 * Register the ElggFeedback class for the object/feedback subtype
 */

if (get_subtype_id('object', 'feedback')) {
	update_subtype('object', 'feedback', 'ElggFeedback');
} else {
	add_subtype('object', 'feedback', 'ElggFeedback');
}
