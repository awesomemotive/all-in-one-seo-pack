<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines the priority/frequency.
 *
 * @since 4.0.0
 */
class Priority {
	/**
	 * Whether the advanced settings are enabled for the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	private static $advanced;

	/**
	 * The global priority for the page type.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	private static $globalPriority = [];

	/**
	 * The global frequency for the page type.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	private static $globalFrequency = [];

	/**
	 * Whether or not we have grouped our settings.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private static $grouped = [];

	/**
	 * The current object type priority.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private static $objectTypePriority = [];

	/**
	 * The current object type frequency.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private static $objectTypeFrequency = [];

	/**
	 * Returns the sitemap priority for a given page.
	 *
	 * @since 4.0.0
	 *
	 * @param  string         $pageType   The type of page (e.g. homepage, blog, post, taxonomies, etc.).
	 * @param  \stdClass|bool $object     The post/term object (optional).
	 * @param  string         $objectType The post/term object type (optional).
	 * @return float                      The priority.
	 */
	public function priority( $pageType, $object = false, $objectType = '' ) {
		// Store setting values in static properties so that we can cache them.
		// Otherwise this has a significant impact on the load time of the sitemap.
		if ( ! self::$advanced ) {
			self::$advanced = aioseo()->options->sitemap->general->advancedSettings->enable;
		}

		if ( ! isset( self::$globalPriority[ $pageType . $objectType ] ) ) {
			$options = aioseo()->options->noConflict();

			$pageTypeConditional = 'date' === $pageType ? 'archive' : $pageType;
			self::$globalPriority[ $pageType . $objectType ] = self::$advanced && $options->sitemap->general->advancedSettings->priority->has( $pageTypeConditional )
				? json_decode( $options->sitemap->general->advancedSettings->priority->$pageTypeConditional->priority )
				: false;
		}

		if ( ! isset( self::$grouped[ $pageType . $objectType ] ) ) {
			$options = aioseo()->options->noConflict();
			self::$grouped[ $pageType . $objectType ] = self::$advanced &&
				$options->sitemap->general->advancedSettings->priority->has( $pageType ) &&
				$options->sitemap->general->advancedSettings->priority->$pageType->has( 'grouped' )
					? $options->sitemap->general->advancedSettings->priority->$pageType->grouped
					: true;
		}

		if ( empty( self::$grouped[ $pageType . $objectType ] ) && self::$advanced ) {
			if ( ! isset( self::$objectTypePriority[ $pageType . $objectType ] ) ) {
				$dynamicOptions = aioseo()->dynamicOptions->noConflict();
				self::$objectTypePriority[ $pageType . $objectType ] = $dynamicOptions->sitemap->priority->has( $pageType ) && $dynamicOptions->sitemap->priority->$pageType->has( $objectType )
					? json_decode( $dynamicOptions->sitemap->priority->$pageType->$objectType->priority )
					: false;
			}
		}

		$priority = $this->defaultPriority( $pageType );
		if ( self::$globalPriority[ $pageType . $objectType ] ) {
			$defaultValue = ! self::$grouped[ $pageType . $objectType ] &&
				self::$advanced &&
				! empty( self::$objectTypePriority[ $pageType . $objectType ] )
					? self::$objectTypePriority[ $pageType . $objectType ]
					: self::$globalPriority[ $pageType . $objectType ];
			$priority     = 'default' === $defaultValue->value ? $priority : $defaultValue->value;
		}

		return $priority;
	}

	/**
	 * Returns the sitemap frequency for a given page.
	 *
	 * @since 4.0.0
	 *
	 * @param  string         $pageType   The type of page (e.g. homepage, blog, post, taxonomies, etc.).
	 * @param  \stdClass|bool $object     The post/term object (optional).
	 * @param  string         $objectType The post/term object type (optional).
	 * @return float                      The frequency.
	 */
	public function frequency( $pageType, $object = false, $objectType = '' ) {
		// Store setting values in static properties so that we can cache them.
		// Otherwise this has a significant impact on the load time of the sitemap.
		if ( ! self::$advanced ) {
			self::$advanced = aioseo()->options->sitemap->general->advancedSettings->enable;
		}
		if ( ! isset( self::$globalFrequency[ $pageType . $objectType ] ) ) {
			$options = aioseo()->options->noConflict();
			$pageTypeConditional = 'date' === $pageType ? 'archive' : $pageType;
			self::$globalFrequency[ $pageType . $objectType ] = self::$advanced && $options->sitemap->general->advancedSettings->priority->has( $pageTypeConditional )
				? json_decode( $options->sitemap->general->advancedSettings->priority->$pageTypeConditional->frequency )
				: false;
		}

		if ( ! isset( self::$grouped[ $pageType . $objectType ] ) ) {
			$options = aioseo()->options->noConflict();
			self::$grouped[ $pageType . $objectType ] = self::$advanced &&
				$options->sitemap->general->advancedSettings->priority->has( $pageType ) &&
				$options->sitemap->general->advancedSettings->priority->$pageType->has( 'grouped' )
					? $options->sitemap->general->advancedSettings->priority->$pageType->grouped
					: true;
		}

		if ( empty( self::$grouped[ $pageType . $objectType ] ) && self::$advanced ) {
			if ( ! isset( self::$objectTypeFrequency[ $pageType . $objectType ] ) ) {
				$dynamicOptions = aioseo()->dynamicOptions->noConflict();

				self::$objectTypeFrequency[ $pageType . $objectType ] = $dynamicOptions->sitemap->priority->has( $pageType ) && $dynamicOptions->sitemap->priority->$pageType->has( $objectType )
					? json_decode( $dynamicOptions->sitemap->priority->$pageType->$objectType->frequency )
					: false;
			}
		}

		$frequency = $this->defaultFrequency( $pageType );
		if ( self::$globalFrequency[ $pageType . $objectType ] ) {
			$defaultValue = ! self::$grouped[ $pageType . $objectType ] &&
				self::$advanced &&
				! empty( self::$objectTypeFrequency[ $pageType . $objectType ] )
					? self::$objectTypeFrequency[ $pageType . $objectType ]
					: self::$globalFrequency[ $pageType . $objectType ];
			$frequency    = 'default' === $defaultValue->value ? $frequency : $defaultValue->value;
		}

		return $frequency;
	}

	/**
	 * Returns the default priority for the page.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $pageType The type of page.
	 * @return float            The default priority.
	 */
	private function defaultPriority( $pageType ) {
		$defaults = [
			'homePage'   => 1.0,
			'blog'       => 0.9,
			'sitemap'    => 0.8,
			'postTypes'  => 0.7,
			'archive'    => 0.5,
			'author'     => 0.3,
			'taxonomies' => 0.3,
			'other'      => 0.5,
		];

		if ( array_key_exists( $pageType, $defaults ) ) {
			return $defaults[ $pageType ];
		}

		return $defaults['other'];
	}

	/**
	 * Returns the default frequency for the page.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $pageType The type of page.
	 * @return float            The default frequency.
	 */
	private function defaultFrequency( $pageType ) {
		$defaults = [
			'homePage'   => 'always',
			'sitemap'    => 'hourly',
			'blog'       => 'daily',
			'postTypes'  => 'weekly',
			'author'     => 'weekly',
			'archive'    => 'monthly',
			'taxonomies' => 'monthly',
			'other'      => 'weekly'
		];

		if ( array_key_exists( $pageType, $defaults ) ) {
			return $defaults[ $pageType ];
		}

		return $defaults['other'];
	}
}