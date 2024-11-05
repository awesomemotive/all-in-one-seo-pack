<?php
namespace AIOSEO\Plugin\Common\WritingAssistant\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Helper functions.
 *
 * @since 4.7.4
 */
class Helpers {
	/**
	 * Gets the data for vue.
	 *
	 * @since 4.7.4
	 *
	 * @return array An array of data.
	 */
	public function getStandaloneVueData() {
		$keyword = Models\WritingAssistantPost::getKeyword( get_the_ID() );

		return [
			'postId'          => get_the_ID(),
			'report'          => $keyword,
			'keywordText'     => ! empty( $keyword->keyword ) ? $keyword->keyword : '',
			'contentAnalysis' => Models\WritingAssistantPost::getContentAnalysis( get_the_ID() ),
			'seoBoost'        => [
				'isLoggedIn'       => aioseo()->writingAssistant->seoBoost->isLoggedIn(),
				'loginUrl'         => aioseo()->writingAssistant->seoBoost->getLoginUrl(),
				'createAccountUrl' => aioseo()->writingAssistant->seoBoost->getCreateAccountUrl(),
				'userOptions'      => aioseo()->writingAssistant->seoBoost->getUserOptions()
			]
		];
	}

	/**
	 * Gets the data for vue.
	 *
	 * @since 4.7.4
	 *
	 * @return array An array of data.
	 */
	public function getSettingsVueData() {
		return [
			'seoBoost' => [
				'isLoggedIn'       => aioseo()->writingAssistant->seoBoost->isLoggedIn(),
				'loginUrl'         => aioseo()->writingAssistant->seoBoost->getLoginUrl(),
				'createAccountUrl' => aioseo()->writingAssistant->seoBoost->getCreateAccountUrl(),
				'userOptions'      => aioseo()->writingAssistant->seoBoost->getUserOptions()
			]
		];
	}
}