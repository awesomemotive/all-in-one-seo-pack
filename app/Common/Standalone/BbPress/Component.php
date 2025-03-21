<?php
namespace AIOSEO\Plugin\Common\Standalone\BbPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Component class.
 *
 * @since 4.8.1
 */
class Component {
	/**
	 * The current component template type.
	 *
	 * @since 4.8.1
	 *
	 * @var string|null
	 */
	public $templateType = null;

	/**
	 * The topic single page data.
	 *
	 * @since 4.8.1
	 *
	 * @var array
	 */
	public $topic = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.8.1
	 */
	public function __construct() {
		if ( is_admin() ) {
			return;
		}

		$this->setTemplateType();
		$this->setTopic();
	}

	/**
	 * Sets the template type.
	 *
	 * @since 4.8.1
	 *
	 * @return void
	 */
	private function setTemplateType() {
		if ( function_exists( 'bbp_is_single_topic' ) && bbp_is_single_topic() ) {
			$this->templateType = 'bbp-topic_single';
		}
	}

	/**
	 * Sets the topic data.
	 *
	 * @since 4.8.1
	 *
	 * @return void
	 */
	private function setTopic() {
		if ( 'bbp-topic_single' !== $this->templateType ) {
			return;
		}

		if (
			! function_exists( 'bbpress' ) ||
			! function_exists( 'bbp_has_replies' ) ||
			! bbp_has_replies()
		) {
			return;
		}

		$replyQuery = bbpress()->reply_query ?? null;
		$replies    = $replyQuery->posts ?? [];
		$mainTopic  = is_array( $replies ) && ! empty( $replies ) ? array_shift( $replies ) : null;

		if ( $mainTopic instanceof \WP_Post ) {
			$this->topic = [
				'title'   => $mainTopic->post_title,
				'content' => $mainTopic->post_content,
				'date'    => $mainTopic->post_date,
				'author'  => get_the_author_meta( 'display_name', $mainTopic->post_author ),
			];

			$comments = [];
			if ( ! empty( $replies ) ) {
				foreach ( $replies as $reply ) {
					if ( ! $reply instanceof \WP_Post ) {
						continue;
					}

					$comments[ $reply->ID ] = [
						'content'       => $reply->post_content,
						'date_recorded' => $reply->post_date,
						'user_fullname' => get_the_author_meta( 'display_name', $reply->post_author ),
					];

					if ( ! empty( $reply->reply_to ) ) {
						$comments[ $reply->reply_to ]['children'][] = $comments[ $reply->ID ];

						unset( $comments[ $reply->ID ] );
					}
				}

				$this->topic['comment'] = array_values( $comments );
			}

			return;
		}

		$this->resetComponent();
	}

	/**
	 * Resets some of the component properties.
	 *
	 * @since 4.8.1
	 *
	 * @return void
	 */
	private function resetComponent() {
		$this->templateType = null;
	}

	/**
	 * Determines the schema type for the current component.
	 *
	 * @since 4.8.1
	 *
	 * @return void
	 */
	public function determineSchemaGraphsAndContext() {
	}
}