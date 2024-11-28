<?php

namespace AIOSEO\Plugin\Common\Meta\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains BuddyPress specific helper methods.
 *
 * @since 4.7.6
 */
trait BuddyPress {
	/**
	 * Sanitizes the title/description.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $value       The value.
	 * @param  int    $objectId    The object ID.
	 * @param  bool   $replaceTags Whether the smart tags should be replaced.
	 * @return string              The sanitized value.
	 */
	public function bpSanitize( $value, $objectId = 0, $replaceTags = false ) {
		$value = $replaceTags ? $value : aioseo()->standalone->buddyPress->tags->replaceTags( $value, $objectId );

		return $this->sanitize( $value, $objectId, true );
	}
}