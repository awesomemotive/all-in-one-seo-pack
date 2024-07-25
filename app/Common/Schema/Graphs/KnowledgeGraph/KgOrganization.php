<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\KnowledgeGraph;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \AIOSEO\Plugin\Common\Schema\Graphs;

/**
 * Knowledge Graph Organization graph class.
 *
 * @since 4.0.0
 */
class KgOrganization extends Graphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {
		$homeUrl                 = trailingslashit( home_url() );
		$organizationName        = aioseo()->tags->replaceTags( aioseo()->options->searchAppearance->global->schema->organizationName );
		$organizationDescription = aioseo()->tags->replaceTags( aioseo()->options->searchAppearance->global->schema->organizationDescription );

		$data = [
			'@type'        => 'Organization',
			'@id'          => $homeUrl . '#organization',
			'name'         => $organizationName ? $organizationName : aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) ),
			'description'  => $organizationDescription,
			'url'          => $homeUrl,
			'email'        => aioseo()->options->searchAppearance->global->schema->email,
			'telephone'    => aioseo()->options->searchAppearance->global->schema->phone,
			'foundingDate' => aioseo()->options->searchAppearance->global->schema->foundingDate
		];

		$numberOfEmployeesData = aioseo()->options->searchAppearance->global->schema->numberOfEmployees->all();

		if (
			$numberOfEmployeesData['isRange'] &&
			isset( $numberOfEmployeesData['from'] ) &&
			isset( $numberOfEmployeesData['to'] ) &&
			0 < $numberOfEmployeesData['to']
		) {
			$data['numberOfEmployees'] = [
				'@type'    => 'QuantitativeValue',
				'minValue' => $numberOfEmployeesData['from'],
				'maxValue' => $numberOfEmployeesData['to']
			];
		}

		if (
			! $numberOfEmployeesData['isRange'] &&
			! empty( $numberOfEmployeesData['number'] )
		) {
			$data['numberOfEmployees'] = [
				'@type' => 'QuantitativeValue',
				'value' => $numberOfEmployeesData['number']
			];
		}

		$logo = $this->logo();
		if ( ! empty( $logo ) ) {
			$data['logo']  = $logo;
			$data['image'] = [ '@id' => $data['logo']['@id'] ];
		}

		$socialUrls = array_values( $this->getOrganizationProfiles() );
		if ( $socialUrls ) {
			$data['sameAs'] = $socialUrls;
		}

		$data = $this->getAddonData( $data, 'kgOrganization' );

		return $data;
	}

	/**
	 * Returns the logo data.
	 *
	 * @since 4.0.0
	 *
	 * @return array The logo data.
	 */
	public function logo() {
		$logo = aioseo()->options->searchAppearance->global->schema->organizationLogo;
		if ( $logo ) {
			return $this->image( $logo, 'organizationLogo' );
		}

		$imageId = aioseo()->helpers->getSiteLogoId();
		if ( $imageId ) {
			return $this->image( $imageId, 'organizationLogo' );
		}

		return [];
	}
}