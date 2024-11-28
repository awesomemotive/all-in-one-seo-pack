<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Traits as CommonTraits;

/**
 * The base graph class.
 *
 * @since 4.0.0
 */
abstract class Graph {
	use Traits\Image;
	use CommonTraits\SocialProfiles;

	/**
	 * The graph data to overwrite.
	 *
	 * @since 4.7.6
	 *
	 * @var array
	 */
	protected static $overwriteGraphData = [];

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 */
	abstract public function get();

	/**
	 * Iterates over a list of functions and sets the results as graph data.
	 *
	 * @since 4.0.13
	 *
	 * @param  array $data          The graph data to add to.
	 * @param  array $dataFunctions List of functions to loop over, associated with a graph property.
	 * @return array $data          The graph data with the results added.
	 */
	protected function getData( $data, $dataFunctions ) {
		foreach ( $dataFunctions as $k => $f ) {
			if ( ! method_exists( $this, $f ) ) {
				continue;
			}

			$value = $this->$f();
			if ( $value || in_array( $k, aioseo()->schema->nullableFields, true ) ) {
				$data[ $k ] = $value;
			}
		}

		return $data;
	}

	/**
	 * Decodes a multiselect field and returns the values.
	 *
	 * @since 4.6.4
	 *
	 * @param  string $json The JSON encoded multiselect field.
	 * @return array        The decoded values.
	 */
	protected function extractMultiselectTags( $json ) {
		$tags = is_string( $json ) ? json_decode( $json ) : [];
		if ( ! $tags ) {
			return [];
		}

		return wp_list_pluck( $tags, 'value' );
	}

	/**
	 * Merges in data from our addon plugins.
	 *
	 * @since   4.5.6
	 * @version 4.6.4 Moved to main graph class.
	 *
	 * @param  array $data The graph data.
	 * @return array       The graph data.
	 */
	protected function getAddonData( $data, $className, $methodName = 'getAdditionalGraphData' ) {
		$addonData = array_filter( aioseo()->addons->doAddonFunction( $className, $methodName, [
			'postId' => get_the_ID(),
			'data'   => $data
		] ) );

		foreach ( $addonData as $addonGraphData ) {
			$data = array_merge( $data, $addonGraphData );
		}

		return $data;
	}

	/**
	 * A way to overwrite the graph data.
	 *
	 * @since 4.7.6
	 *
	 * @param  array $data The data to overwrite.
	 * @return void
	 */
	public static function setOverwriteGraphData( $data ) {
		self::$overwriteGraphData[ static::class ] = $data;
	}
}