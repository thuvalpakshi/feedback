<?php
/**
 * Extended class to override the time_created
 * 
 * @property string $status      The published status of the feedback post (published, draft)
 * @property string $comments_on Whether commenting is allowed (Off, On)
 * @property string $excerpt     An excerpt of the feedback post used when displaying the post
 */
class ElggFeedback extends ElggObject {

	/**
	 * Set subtype to feedback.
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();

		$this->attributes['subtype'] = "feedback";
	}

	/**
	 * Can a user comment on this feedback?
	 *
	 * @see ElggObject::canComment()
	 *
	 * @param int $user_guid User guid (default is logged in user)
	 * @return bool
	 * @since 1.8.0
	 */
	public function canComment($user_guid = 0) {
		$result = parent::canComment($user_guid);
		if ($result == false) {
			return $result;
		}

		if ($this->comments_on == 'Off') {
			return false;
		}
		
		return true;
	}

}