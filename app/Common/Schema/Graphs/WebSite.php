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
			'name'          => aioseo()->options->searchAppearance->global->schema->websiteName
				? aioseo()->options->searchAppearance->global->schema->websiteName
				: aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) ),
			'alternateName' => aioseo()->options->searchAppearance->global->schema->websiteAlternateName,
			'description'   => aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ),
			'inLanguage'    => aioseo()->helpers->currentLanguageCodeBCP47(),
			'publisher'     => [ '@id' => $homeUrl . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ]
		];

		if ( is_front_page() && aioseo()->options->searchAppearance->advanced->sitelinks ) {
			$data['potentialAction'] = [
				'@type'       => 'SearchAction',
				'target'      => [
					'@type'       => 'EntryPoint',
					'urlTemplate' => $homeUrl . '?s={search_term_string}'
				],
				'query-input' => 'required name=search_term_string',
			];

			// Yandex requires a different, older format.
			if ( aioseo()->helpers->isYandexUserAgent() ) {
				$data['potentialAction'] = [
					'@type'  => 'SearchAction',
					'target' => $homeUrl . '?s={search_term_string}',
					'query'  => 'required'
				];
			}
		}

		return $data;
	}
}