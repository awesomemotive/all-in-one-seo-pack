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
	 * Returns the default context data.
	 *
	 * @since   4.3.0
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function defaults() {
		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl()
		];
	}

	/**
	 * Returns the context data for the blog index shown at the site root.
	 *
	 * @since   4.0.0
	 * @version 5.0.3 No longer returns a static homepage context or the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function home() {
		return [
			'url'         => aioseo()->helpers->getUrl(),
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription()
		];
	}

	/**
	 * Returns the context data for the requested post.
	 *
	 * @since   4.0.0
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function post() {
		$post = aioseo()->helpers->getPost();
		if ( ! $post ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl()
			];
		}

		return [
			'name'        => aioseo()->meta->title->getTitle( $post ),
			'description' => aioseo()->meta->description->getDescription( $post ),
			'url'         => aioseo()->helpers->getUrl(),
			'object'      => $post,
		];
	}

	/**
	 * Returns the context data for the requested term archive.
	 *
	 * @since   4.0.0
	 * @version 4.9.9 Bail when the queried object is not a WP_Term (e.g. WP_Post_Type on some CPT+taxonomy archives).
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function term() {
		$term = aioseo()->helpers->getTerm();
		if ( ! ( $term instanceof \WP_Term ) ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl()
			];
		}

		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl()
		];
	}

	/**
	 * Returns the context data for the requested author archive.
	 *
	 * @since   4.0.0
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function author() {
		$author = get_queried_object();
		if ( ! $author ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl()
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
			'url'         => $url
		];
	}

	/**
	 * Returns the context data for the requested post archive.
	 *
	 * @since   4.0.0
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function postArchive() {
		$postType = get_queried_object();
		if ( ! $postType ) {
			return [
				'name'        => '',
				'description' => '',
				'url'         => aioseo()->helpers->getUrl()
			];
		}

		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl()
		];
	}

	/**
	 * Returns the context data for the requested data archive.
	 *
	 * @since   4.0.0
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function date() {
		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl()
		];
	}

	/**
	 * Returns the context data for the search page.
	 *
	 * @since   4.0.0
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function search() {
		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl()
		];
	}

	/**
	 * Returns the context data for the 404 Not Found page.
	 *
	 * @since   4.0.0
	 * @version 5.0.3 No longer returns the breadcrumb key.
	 *
	 * @return array The context data.
	 */
	public function notFound() {
		return [
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->meta->description->getDescription(),
			'url'         => aioseo()->helpers->getUrl()
		];
	}
}