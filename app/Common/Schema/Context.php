<?php
namespace AIOSEO\Plugin\Common\Schema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines the context.
 *
 * @since 4.0.0
 */
class Context {
	/**
	 * Breadcrumb class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Breadcrumb
	 */
	public $breadcrumb = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->breadcrumb = new Breadcrumb();
	}

	/**
	 * Returns the default context data.
	 *
	 * @since 4.3.0
	 *
	 * @return array The context data.
	 */
	public function defaults() {
		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl(),
			'breadcrumb'  => []
		];
	}

	/**
	 * Returns the context data for the homepage.
	 *
	 * @since 4.0.0
	 *
	 * @return array $context The context data.
	 */
	public function home() {
		$context = [
			'url'         => aioseo()->helpers->getUrl(),
			'breadcrumb'  => $this->breadcrumb->home(),
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription()
		];

		// Homepage set to show latest posts.
		if ( 'posts' === get_option( 'show_on_front' ) && is_home() ) {
			return $context;
		}

		// Homepage set to static page.
		$post = aioseo()->helpers->getPost();
		if ( ! $post ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl(),
				'breadcrumb'  => [],
			];
		}

		$context['object'] = $post;

		return $context;
	}

	/**
	 * Returns the context data for the requested post.
	 *
	 * @since 4.0.0
	 *
	 * @return array The context data.
	 */
	public function post() {
		$post = aioseo()->helpers->getPost();
		if ( ! $post ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl(),
				'breadcrumb'  => [],
			];
		}

		return [
			'name'        => aioseo()->meta->title->getTitle( $post ),
			'description' => aioseo()->meta->description->getDescription( $post ),
			'url'         => aioseo()->helpers->getUrl(),
			'breadcrumb'  => $this->breadcrumb->post( $post ),
			'object'      => $post,
		];
	}

	/**
	 * Returns the context data for the requested term archive.
	 *
	 * @since 4.0.0
	 *
	 * @return array The context data.
	 */
	public function term() {
		$term = aioseo()->helpers->getTerm();
		if ( ! $term ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl(),
				'breadcrumb'  => [],
			];
		}

		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl(),
			'breadcrumb'  => $this->breadcrumb->term( $term )
		];
	}

	/**
	 * Returns the context data for the requested author archive.
	 *
	 * @since 4.0.0
	 *
	 * @return array The context data.
	 */
	public function author() {
		$author = get_queried_object();
		if ( ! $author ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl(),
				'breadcrumb'  => [],
			];
		}

		$title       = aioseo()->meta->title->getTitle();
		$description = aioseo()->meta->description->getDescription();
		$url         = aioseo()->helpers->getUrl();

		if ( ! $description ) {
			$description = get_the_author_meta( 'description', $author->ID );
		}

		return [
			'name'        => $title,
			'description' => $description,
			'url'         => $url,
			'breadcrumb'  => $this->breadcrumb->setPositions( [
				'name'        => get_the_author_meta( 'display_name', $author->ID ),
				'description' => $description,
				'url'         => $url,
				'type'        => 'CollectionPage'
			] )
		];
	}

	/**
	 * Returns the context data for the requested post archive.
	 *
	 * @since 4.0.0
	 *
	 * @return array The context data.
	 */
	public function postArchive() {
		$postType = get_queried_object();
		if ( ! $postType ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl(),
				'breadcrumb'  => [],
			];
		}

		$title       = aioseo()->meta->title->getTitle();
		$description = aioseo()->meta->description->getDescription();
		$url         = aioseo()->helpers->getUrl();

		return [
			'name'        => $title,
			'description' => $description,
			'url'         => $url,
			'breadcrumb'  => $this->breadcrumb->setPositions( [
				'name'        => $postType->label,
				'description' => $description,
				'url'         => $url,
				'type'        => 'CollectionPage'
			] )
		];
	}

	/**
	 * Returns the context data for the requested data archive.
	 *
	 * @since 4.0.0
	 *
	 * @return array $context The context data.
	 */
	public function date() {
		$context = [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl()
		];

		$context['breadcrumb'] = $this->breadcrumb->date();

		return $context;
	}

	/**
	 * Returns the context data for the search page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The context data.
	 */
	public function search() {
		global $s;
		$title       = aioseo()->meta->title->getTitle();
		$description = aioseo()->meta->description->getDescription();
		$url         = aioseo()->helpers->getUrl();

		return [
			'name'        => $title,
			'description' => $description,
			'url'         => $url,
			'breadcrumb'  => $this->breadcrumb->setPositions( [
				'name'        => $s ? $s : $title,
				'description' => $description,
				'url'         => $url,
				'type'        => 'SearchResultsPage'
			] )
		];
	}

	/**
	 * Returns the context data for the 404 Not Found page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The context data.
	 */
	public function notFound() {
		$title       = aioseo()->meta->title->getTitle();
		$description = aioseo()->meta->description->getDescription();
		$url         = aioseo()->helpers->getUrl();

		return [
			'name'        => $title,
			'description' => $description,
			'url'         => $url,
			'breadcrumb'  => $this->breadcrumb->setPositions( [
				'name'        => __( 'Not Found', 'all-in-one-seo-pack' ),
				'description' => $description,
				'url'         => $url
			] )
		];
	}
}