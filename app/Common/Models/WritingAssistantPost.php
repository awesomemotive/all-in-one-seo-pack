<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Posts.
 *
 * @since 4.7.4
 */
class WritingAssistantPost extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.7.4
	 *
	 * @var string
	 */
	protected $table = 'aioseo_writing_assistant_posts';

	/**
	 * Fields that should be integer values.
	 *
	 * @since 4.7.4
	 *
	 * @var array
	 */
	protected $integerFields = [ 'id', 'post_id', 'keyword_id' ];

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
	protected $jsonFields = [ 'content_analysis' ];

	/**
	 * Gets a post's content analysis.
	 *
	 * @since 4.7.4
	 *
	 * @param  int   $postId A post ID.
	 * @return array         The post content's analysis.
	 */
	public static function getContentAnalysis( $postId ) {
		$post = self::getPost( $postId );

		return ! empty( $post->content_analysis ) && is_object( $post->content_analysis ) ? (array) $post->content_analysis : [];
	}

	/**
	 * Gets a writing assistant post.
	 *
	 * @since 4.7.4
	 *
	 * @param  int                  $postId A post ID.
	 * @return WritingAssistantPost         The post object.
	 */
	public static function getPost( $postId ) {
		$post = aioseo()->core->db->start( 'aioseo_writing_assistant_posts' )
			->where( 'post_id', $postId )
			->run()
			->model( 'AIOSEO\Plugin\Common\Models\WritingAssistantPost' );

		if ( ! $post->exists() ) {
			$post->post_id = $postId;
		}

		return $post;
	}

	/**
	 * Gets a post's current keyword.
	 *
	 * @since 4.7.4
	 *
	 * @param  int                          $postId A post ID.
	 * @return WritingAssistantKeyword|bool         An attached keyword.
	 */
	public static function getKeyword( $postId ) {
		$post = self::getPost( $postId );
		if ( ! $post->exists() || empty( $post->keyword_id ) ) {
			return false;
		}

		$keyword = aioseo()->core->db->start( 'aioseo_writing_assistant_keywords' )
			->where( 'id', $post->keyword_id )
			->run()
			->model( 'AIOSEO\Plugin\Common\Models\WritingAssistantKeyword' );

		// This is here so this property is reactive in the frontend.
		if ( ! empty( $keyword->keywords ) ) {
			foreach ( $keyword->keywords as &$keyph ) {
				$keyph->contentCount = 0;
			}
		}

		// Help sorting in the frontend.
		if ( ! empty( $keyword->competitors->competitors ) ) {
			foreach ( $keyword->competitors->competitors as &$competitor ) {
				$competitor->wasAnalyzed = true;
				if ( 0 >= $competitor->wordCount ) {
					$competitor->wordCount        = 0;
					$competitor->readabilityScore = 999;
					$competitor->readabilityGrade = '';
					$competitor->gradeScore       = 0;
					$competitor->grade            = '';
					$competitor->wasAnalyzed      = false;
				}

				$competitor->readabilityScore = (float) $competitor->readabilityScore;
			}
		}

		return $keyword;
	}

	/**
	 * Return if a post has a keyword.
	 *
	 * @since 4.7.4
	 *
	 * @param  int     $postId A post ID.
	 * @return boolean         Has a keyword.
	 */
	public static function hasKeyword( $postId ) {
		$post = self::getPost( $postId );

		return (bool) $post->keyword_id;
	}

	/**
	 * Attaches a keyword to a post.
	 *
	 * @since 4.7.4
	 *
	 * @param  int  $keywordId The keyword ID.
	 * @return void
	 */
	public function attachKeyword( $keywordId ) {
		$this->keyword_id = $keywordId;
		$this->save();
	}
}