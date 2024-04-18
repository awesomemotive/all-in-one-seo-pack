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
			'alternateName' => aioseo()->options->searchAppearance->global->schema->websiteAlternateName,
			'description'   => aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ),
			'inLanguage'    => aioseo()->helpers->currentLanguageCodeBCP47(),
			'publisher'     => [ '@id' => $homeUrl . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ]
		];

		if ( is_front_page() && aioseo()->options->searchAppearance->advanced->sitelinks ) {
			$defaultSearchAction = [
				'@type'       => 'SearchAction',
				'target'      => [
					'@type'       => 'EntryPoint',
					'urlTemplate' => $homeUrl . '?s={search_term_string}'
				],
				'query-input' => 'required name=search_term_string',
			];

			$data['potentialAction'] = $defaultSearchAction;

			if ( aioseo()->helpers->isYandexUserAgent() ) {
				// Yandex requires a different, older format. We'll output both so Google doesn't throw errors
				// in case this version of the page gets cached.
				$data['potentialAction'] = [
					$defaultSearchAction,
					[
						'@type'  => 'SearchAction',
						'target' => $homeUrl . '?s={search_term_string}',
						'query'  => 'required'
					]
				];
			}
		}

		return $data;
	}
}