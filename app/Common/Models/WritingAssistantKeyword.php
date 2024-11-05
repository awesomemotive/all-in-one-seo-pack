<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Keyword.
 *
 * @since 4.7.4
 */
class WritingAssistantKeyword extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.7.4
	 *
	 * @var string
	 */
	protected $table = 'aioseo_writing_assistant_keywords';

	/**
	 * Fields that should be numeric values.
	 *
	 * @since 4.7.4
	 *
	 * @var array
	 */
	protected $integerFields = [ 'id', 'progress' ];

	/**
	 * Fields that should be boolean values.
	 *
	 * @since 4.7.4
	 *
	 * @var array
	 */
	protected $booleanFields = [];

	/**
	 * Fields that should be encoded/decoded on save/get.
	 *
	 * @since 4.7.4
	 *
	 * @var array
	 */
	protected $jsonFields = [ 'keywords', 'competitors', 'content_analysis' ];

	/**
	 * Gets a keyword.
	 *
	 * @since 4.7.4
	 *
	 * @param  string $keyword  A keyword.
	 * @param  string $country  The country code.
	 * @param  string $language The language code.
	 * @return object           A keyword found.
	 */
	public static function getKeyword( $keyword, $country, $language ) {
		$dbKeyword = aioseo()->core->db->start( 'aioseo_writing_assistant_keywords' )
			->where( 'keyword', $keyword )
			->where( 'country', $country )
			->where( 'language', $language )
			->run()
			->model( 'AIOSEO\Plugin\Common\Models\WritingAssistantKeyword' );

		if ( ! $dbKeyword->exists() ) {
			$dbKeyword->keyword  = $keyword;
			$dbKeyword->country  = $country;
			$dbKeyword->language = $language;
		}

		return $dbKeyword;
	}
}