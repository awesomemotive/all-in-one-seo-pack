<?php
/**
 * Schema Graph BreadcrumbList Class
 *
 * Acts as the Breadcrumb List class for Schema BreadcrumbList.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_BreadcrumbList
 *
 * @see Schema BreadcrumbList
 * @link https://schema.org/BreadcrumbList
 */
class AIOSEOP_Graph_BreadcrumbList extends AIOSEOP_Graph_ItemList {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'BreadcrumbList';
	}

	/**
	 * Get Graph Name.
	 *
	 * Intended for frontend use when displaying which schema graphs are available.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	protected function get_name() {
		return 'Breadcrumb List';
	}

	/**
	 * Prepare data.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 */
	protected function prepare( $data = array() ) {
		$context = AIOSEOP_Context::get_instance();
		$rtn_data = array(
			'@type'           => $this->slug,
			'@id'             => $context->get_url() . '#' . strtolower( $this->slug ),
			'itemListElement' => array(),
		);

		$breadcrumb_list = $context->get_breadcrumb();
		foreach ( $breadcrumb_list as $list_item ) {
			$list_item_data = array(
				'position' => $list_item['position'],
				'item'     => array(
					'url'  => $list_item['url'],
					'name' => $list_item['title'],
				),
			);

			$rtn_data['itemListElement'][] = $this->prepare_listitem( $list_item_data );
		}

		return $rtn_data;
	}

	/**
	 * ListItem Defaults.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 */
	protected function listitem_defaults() {
		return array(
			'position' => 1,
			'item'     => array(),
		);
	}

	/**
	 * Item Defaults.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 */
	protected function item_defaults() {
		return array(
			'url'  => '',
			'name' => '',
		);
	}

	/**
	 * Prepare ListItem Schema.
	 *
	 * @since 3.4.0
	 *
	 * @param array      $list_item_data
	 * @return array
	 */
	protected function prepare_listitem( $list_item_data ) {
		$rtn_data = array(
			'@type' => 'ListItem',
		);

		// Only use valid variables from defaults.
		foreach ( array_keys( $this->listitem_defaults() ) as $key ) {
			if ( 'item' === $key ) {
				$list_item_data[ $key ] = $this->prepare_item( $list_item_data[ $key ], 'WebPage', $list_item_data['item']['url'] );
			}
			if ( ! empty( $list_item_data[ $key ] ) ) {
				$rtn_data[ $key ] = $list_item_data[ $key ];
			}
		}

		return $rtn_data;
	}

	/**
	 * Prepare Item Schema.
	 *
	 * @since 3.4.0
	 *
	 * @param array      $item_data
	 * @param string     $schema_type
	 * @param int|string $schema_id
	 * @return array
	 */
	protected function prepare_item( $item_data, $schema_type, $schema_id ) {
		$rtn_data = array(
			'@type' => $schema_type,
			'@id'   => $schema_id,
		);
		foreach ( array_keys( $this->item_defaults() ) as $key ) {
			if ( ! empty( $item_data[ $key ] ) ) {
				$rtn_data[ $key ] = $item_data[ $key ];
			}
		}

		return $rtn_data;
	}
}
