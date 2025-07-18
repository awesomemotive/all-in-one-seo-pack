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
		$breadcrumbs = aioseo()->breadcrumbs->frontend->getBreadcrumbs() ?? '';
		if ( ! $breadcrumbs ) {
			return [];
		}

		// Set the position for each breadcrumb.
		$position = 1;
		foreach ( $breadcrumbs as $k => $breadcrumb ) {
			if ( ! isset( $breadcrumb['position'] ) ) {
				$breadcrumbs[ $k ]['position'] = $position;
			}
			$position++;
		}

		$trailLength = count( $breadcrumbs );
		if ( ! $trailLength ) {
			return [];
		}

		$listItems = [];
		foreach ( $breadcrumbs as $breadcrumb ) {
			if ( empty( $breadcrumb['link'] ) || ! is_scalar( $breadcrumb['link'] ) ) {
				continue;
			}

			$listItem = [
				'@type'    => 'ListItem',
				'@id'      => $breadcrumb['link'] . '#listItem',
				'position' => $breadcrumb['position'],
				'name'     => $breadcrumb['label'] ?? ''
			];

			// Don't add "item" prop for last crumb.
			if ( $trailLength !== $breadcrumb['position'] ) {
				$listItem['item'] = $breadcrumb['link'];
			}

			if ( 1 === $trailLength ) {
				$listItems[] = $listItem;
				continue;
			}

			if ( $trailLength > $breadcrumb['position'] && ! empty( $breadcrumbs[ $breadcrumb['position'] ]['label'] ) ) {
				$listItem['nextItem'] = [
					'@type' => 'ListItem',
					'@id'   => $breadcrumbs[ $breadcrumb['position'] ]['link'] . '#listItem',
					'name'  => $breadcrumbs[ $breadcrumb['position'] ]['label'],
				];
			}

			if ( 1 < $breadcrumb['position'] && ! empty( $breadcrumbs[ $breadcrumb['position'] - 2 ]['label'] ) ) {
				$listItem['previousItem'] = [
					'@type' => 'ListItem',
					'@id'   => $breadcrumbs[ $breadcrumb['position'] - 2 ]['link'] . '#listItem',
					'name'  => $breadcrumbs[ $breadcrumb['position'] - 2 ]['label'],
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