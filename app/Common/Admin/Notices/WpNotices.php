<?php
namespace AIOSEO\Plugin\Common\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WpNotices class.
 *
 * @since 4.2.3
 */
class WpNotices {
	/**
	 * Notices array
	 *
	 * @since 4.2.3
	 *
	 * @var array
	 */
	private $notices = [];

	/**
	 * The cache key.
	 *
	 * @since 4.2.3
	 *
	 * @var string
	 */
	private $cacheKey = 'wp_notices';

	/**
	 * Class Constructor.
	 *
	 * @since 4.2.3
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'registerApiField' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueueScripts' ] );
		add_action( 'admin_notices', [ $this, 'adminNotices' ] );
	}

	/**
	 * Enqueue notices scripts.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function enqueueScripts() {
		aioseo()->core->assets->load( 'src/vue/standalone/wp-notices/main.js' );
	}

	/**
	 * Registers an API field with notices.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function registerApiField() {
		foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
			register_rest_field( $postType, 'aioseo_notices', [
				'get_callback' => [ $this, 'apiGetNotices' ]
			] );
		}
	}

	/**
	 * API field callback.
	 *
	 * @since 4.2.3
	 *
	 * @return array Notices array
	 */
	public function apiGetNotices() {
		$notices = $this->getNoticesInContext();

		// Notices show only one time.
		$this->removeNotices( $notices );

		return $notices;
	}

	/**
	 * Get all notices.
	 *
	 * @since 4.2.3
	 *
	 * @return array Notices array
	 */
	public function getNotices() {
		if ( empty( $this->notices ) ) {
			$this->notices = (array) aioseo()->core->cache->get( $this->cacheKey );
		}

		return ! empty( $this->notices ) ? $this->notices : [];
	}

	/**
	 * Get all notices in the current context.
	 *
	 * @since 4.2.6
	 *
	 * @return array Notices array
	 */
	public function getNoticesInContext() {
		$contextNotices = $this->getNotices();
		foreach ( $contextNotices as $key => $notice ) {
			if ( empty( $notice['allowedContexts'] ) ) {
				continue;
			}

			$allowed = false;
			foreach ( $notice['allowedContexts'] as $allowedContext ) {
				if ( $this->isAllowedContext( $allowedContext ) ) {
					$allowed = true;
					break;
				}
			}

			if ( ! $allowed ) {
				unset( $contextNotices[ $key ] );
			}
		}

		return $contextNotices;
	}

	/**
	 * Test if we are in the current context.
	 *
	 * @since 4.2.6
	 *
	 * @param  string $context The context to test. (posts)
	 * @return bool            Is the required context.
	 */
	private function isAllowedContext( $context ) {
		switch ( $context ) {
			case 'posts':
				return aioseo()->helpers->isScreenPostList() ||
						aioseo()->helpers->isScreenPostEdit() ||
						aioseo()->helpers->isAjaxCronRestRequest();
		}

		return false;
	}

	/**
	 * Finds a notice by message.
	 *
	 * @since 4.2.3
	 *
	 * @param  string     $message The message string.
	 * @param  string     $type    The message type.
	 * @return void|array          The found notice.
	 */
	public function getNotice( $message, $type = '' ) {
		$notices = $this->getNotices();
		foreach ( $notices as $notice ) {
			if ( $notice['options']['id'] === $this->getNoticeId( $message, $type ) ) {
				return $notice;
			}
		}
	}

	/**
	 * Generates a notice id.
	 *
	 * @since 4.2.3
	 *
	 * @param  string $message The message string.
	 * @param  string $type    The message type.
	 * @return string          The notice id.
	 */
	public function getNoticeId( $message, $type = '' ) {
		return md5( $message . $type );
	}

	/**
	 * Clear notices.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function clearNotices() {
		$this->notices = [];
		$this->updateCache();
	}

	/**
	 * Remove certain notices.
	 *
	 * @since 4.2.6
	 *
	 * @param  array $notices A list of notices to remove.
	 * @return void
	 */
	public function removeNotices( $notices ) {
		foreach ( array_keys( $notices ) as $noticeKey ) {
			unset( $this->notices[ $noticeKey ] );
		}
		$this->updateCache();
	}

	/**
	 * Adds a notice.
	 *
	 * @since 4.2.3
	 *
	 * @param  string $message         The message.
	 * @param  string $status          The message status [success, info, warning, error]
	 * @param  array  $options         Options for the message. https://developer.wordpress.org/block-editor/reference-guides/data/data-core-notices/#createnotice
	 * @param  array  $allowedContexts The contexts where this notice will show.
	 * @return void
	 */
	public function addNotice( $message, $status = 'warning', $options = [], $allowedContexts = [] ) {
		$type = ! empty( $options['type'] ) ? $options['type'] : '';
		$foundNotice = $this->getNotice( $message, $type );
		if ( empty( $message ) || ! empty( $foundNotice ) ) {
			return;
		}

		$notice = [
			'message'         => $message,
			'status'          => $status,
			'options'         => wp_parse_args( $options, [
				'id'            => $this->getNoticeId( $message, $type ),
				'isDismissible' => true
			] ),
			'allowedContexts' => $allowedContexts
		];

		$this->notices[] = $notice;
		$this->updateCache();
	}

	/**
	 * Show notices on classic editor.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function adminNotices() {
		// Double check we're actually in the admin before outputting anything.
		if ( ! is_admin() ) {
			return;
		}

		$notices = $this->getNoticesInContext();
		$notices = array_filter( $notices, function( $notice ) {
			return is_array( $notice );
		} );

		foreach ( $notices as $notice ) {
			// Hide snackbar notices on classic editor.
			if ( ! empty( $notice['options']['type'] ) && 'snackbar' === $notice['options']['type'] ) {
				continue;
			}

			$status = ! empty( $notice['status'] ) ? $notice['status'] : 'warning';
			$class  = ! empty( $notice['options']['class'] ) ? $notice['options']['class'] : '';
			?>
			<div
				class="notice notice-<?php echo esc_attr( $status ) ?> <?php echo esc_attr( $class ) ?>">
				<?php echo '<p>' . $notice['message'] . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php
				if ( ! empty( $notice['options']['actions'] ) ) {
					foreach ( $notice['options']['actions'] as $action ) {
						echo '<p>';
						if ( ! empty( $action['url'] ) ) {
							$class  = ! empty( $action['class'] ) ? $action['class'] : '';
							$target = ! empty( $action['target'] ) ? $action['target'] : '';
							echo '<a
								href="' . esc_attr( $action['url'] ) . '"
								class="' . esc_attr( $class ) . '"
								target="' . esc_attr( $target ) . '"
							>';
						}
						echo $action['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						if ( ! empty( $action['url'] ) ) {
							echo '</a>';
						}
						echo '</p>';
					}
					?>
				<?php } ?>
			</div>
			<?php
		}

		// Notices show only one time.
		$this->removeNotices( $notices );
	}

	/**
	 * Helper to update the cache with the current notices array.
	 *
	 * @since 4.2.6
	 *
	 * @return void
	 */
	private function updateCache() {
		aioseo()->core->cache->update( $this->cacheKey, $this->notices );
	}
}