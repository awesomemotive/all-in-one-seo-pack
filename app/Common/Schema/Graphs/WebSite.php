<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WebSite graph class.
 *
 * @since 4.0.0
 */
class WebSite extends Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {
		$homeUrl = trailingslashit( home_url() );
		$data    = [
			'@type'         => 'WebSite',
			'@id'           => $homeUrl . '#website',
			'url'           => $homeUrl,
			'name'          => aioseo()->helpers->getWebsiteName(),
			'alternateName' => aioseo()->tags->replaceTags( aioseo()->options->searchAppearance->global->schema->websiteAlternateName ),
			'description'   => aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ),
			'inLanguage'    => aioseo()->helpers->currentLanguageCodeBCP47(),
			'publisher'     => [ '@id' => $homeUrl . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ]
		];

		return $data;
	}
}