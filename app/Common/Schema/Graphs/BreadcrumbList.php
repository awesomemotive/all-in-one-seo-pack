<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BreadcrumbList graph class.
 *
 * @since 4.0.0
 */
class BreadcrumbList extends Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array The graph data.
	 */
	public function get() {
		$breadcrumbs = aioseo()->schema->context['breadcrumb'] ?? '';
		if ( ! $breadcrumbs ) {
			return [];
		}

		$trailLength = count( $breadcrumbs );
		if ( ! $trailLength ) {
			return [];
		}

		$listItems = [];
		foreach ( $breadcrumbs as $breadcrumb ) {
			$listItem = [
				'@type'    => 'ListItem',
				'@id'      => $breadcrumb['url'] . '#listItem',
				'position' => $breadcrumb['position'],
				'name'     => $breadcrumb['name'] ?? ''
			];

			// Don't add "item" prop for last crumb.
			if ( $trailLength !== $breadcrumb['position'] ) {
				$listItem['item'] = $breadcrumb['url'];
			}

			if ( 1 === $trailLength ) {
				$listItems[] = $listItem;
				continue;
			}

			if ( $trailLength > $breadcrumb['position'] ) {
				$listItem['nextItem'] = [
					'@type' => 'ListItem',
					'@id'   => $breadcrumbs[ $breadcrumb['position'] ]['url'] . '#listItem',
					'name'  => $breadcrumbs[ $breadcrumb['position'] ]['name'],
				];
			}

			if ( 1 < $breadcrumb['position'] ) {
				$listItem['previousItem'] = [
					'@type' => 'ListItem',
					'@id'   => $breadcrumbs[ $breadcrumb['position'] - 2 ]['url'] . '#listItem',
					'name'  => $breadcrumbs[ $breadcrumb['position'] - 2 ]['name'],
				];
			}

			$listItems[] = $listItem;
		}

		$data = [
			'@type'           => 'BreadcrumbList',
			'@id'             => aioseo()->schema->context['url'] . '#breadcrumblist',
			'itemListElement' => $listItems
		];

		return $data;
	}
}