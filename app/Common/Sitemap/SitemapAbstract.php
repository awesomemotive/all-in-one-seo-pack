<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class holding the class properties of our main AIOSEO class.
 *
 * @since 4.4.3
 */
abstract class SitemapAbstract {
	/**
	 * Content class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Content
	 */
	public $content = null;

	/**
	 * Root class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Root
	 */
	public $root = null;

	/**
	 * Query class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Query
	 */
	public $query = null;

	/**
	 * File class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var File
	 */
	public $file = null;

	/**
	 * Image class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Image\Image
	 */
	public $image = null;

	/**
	 * Priority class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Priority
	 */
	public $priority = null;

	/**
	 * Output class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Output
	 */
	public $output = null;

	/**
	 * Helpers class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Helpers
	 */
	public $helpers = null;

	/**
	 * RequestParser class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var RequestParser
	 */
	public $requestParser = null;

	/**
	 * Xsl class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Xsl
	 */
	public $xsl = null;

	/**
	 * The sitemap type (e.g. "general", "news", "video", "rss", etc.).
	 *
	 * @since 4.2.7
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * Index name.
	 *
	 * @since 4.4.3
	 *
	 * @var string
	 */
	public $indexName = '';

	/**
	 * Page number.
	 *
	 * @since 4.4.3
	 *
	 * @var int
	 */
	public $pageNumber = 0;

	/**
	 * Page number.
	 *
	 * @since 4.4.3
	 *
	 * @var int
	 */
	public $offset = 0;

	/**
	 * Indexes active.
	 *
	 * @since 4.4.3
	 *
	 * @var bool
	 */
	public $indexes = false;

	/**
	 * Links per index.
	 *
	 * @since 4.4.3
	 *
	 * @var int
	 */
	public $linksPerIndex = PHP_INT_MAX;

	/**
	 * Is static.
	 *
	 * @since 4.4.3
	 *
	 * @var bool
	 */
	public $isStatic = false;

	/**
	 * Filename.
	 *
	 * @since 4.4.3
	 *
	 * @var string
	 */
	public $filename = '';
}