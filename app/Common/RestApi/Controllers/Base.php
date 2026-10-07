<?php
namespace AIOSEO\Plugin\Common\RestApi\Controllers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta;

/**
 * Base class for the post/term controller.
 *
 * Merged into the main plugin from the legacy aioseo-rest-api addon.
 *
 * @since 4.9.8
 */
abstract class Base {
	/**
	 * The original main query.
	 *
	 * @since 4.9.8
	 *
	 * @var \WP_Query
	 */
	protected $originalQuery;

	/**
	 * Internally used fields that we can strip from the data that we return to the user.
	 *
	 * @since 4.9.8
	 *
	 * @var array
	 */
	protected $internalFields = [
		'page_analysis',
		'truseo',
		'seo_score',
		'images',
		'image_scan_date',
		'videos',
		'video_thumbnail',
		'video_scan_date',
		'link_scan_date',
		'link_suggestions_scan_date',
		'options'
	];

	/**
	 * The deprecated fields from V3.
	 *
	 * @since 4.9.8
	 *
	 * @var array
	 */
	protected $deprecatedFields = [
		'_aioseop_title'       => 'title',
		'_aioseop_description' => 'description'
	];

	/**
	 * The meta data updates we rejected, each with its request, object ID and error.
	 *
	 * @since 5.0.3
	 *
	 * @var array<int, array{request: \WP_REST_Request, objectId: int, fieldName: string, error: \WP_Error}>
	 */
	protected $rejectedMetaDataUpdates = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.9.8
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register' ] );
		add_filter( 'rest_dispatch_request', [ $this, 'rejectForbiddenMetaDataRequest' ], 10, 4 );
		add_filter( 'rest_request_after_callbacks', [ $this, 'setRejectedMetaDataResponse' ], 10, 3 );
	}

	/**
	 * Registers the HEAD fields.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $object The object (post type or taxonomy).
	 * @return void
	 */
	protected function registerHeadFields( $object ) {
		if ( ! apply_filters( 'aioseo_rest_api_disable_head', false, $object ) ) {
			register_rest_field( $object, 'aioseo_head', [
				'get_callback' => [ $this, 'getHead' ]
			] );
		}

		if ( ! apply_filters( 'aioseo_rest_api_disable_head_json', false, $object ) ) {
			register_rest_field( $object, 'aioseo_head_json', [
				'get_callback' => [ $this, 'getHeadJson' ]
			] );
		}
	}

	/**
	 * Registers the breadcrumb fields.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $object The object (post type or taxonomy).
	 * @return void
	 */
	protected function registerBreadcrumbFields( $object ) {
		if ( ! aioseo()->options->deprecated->breadcrumbs->enable ) {
			return;
		}

		if ( ! apply_filters( 'aioseo_rest_api_disable_breadcrumb', false, $object ) ) {
			register_rest_field( $object, 'aioseo_breadcrumb', [
				'get_callback' => [ $this, 'getBreadcrumb' ]
			] );
		}

		if ( ! apply_filters( 'aioseo_rest_api_disable_breadcrumb_json', false, $object ) ) {
			register_rest_field( $object, 'aioseo_breadcrumb_json', [
				'get_callback' => [ $this, 'getBreadcrumbJson' ]
			] );
		}
	}

	/**
	 * Returns the raw data that we would output in the HEAD for the given object.
	 *
	 * @since 4.9.8
	 *
	 * @param  array  $object The object (post type or taxonomy).
	 * @return string         The raw HEAD data.
	 */
	public function getHead( $object ) {
		if ( apply_filters( 'aioseo_rest_api_disable', false, $object ) ) {
			return '';
		}

		$this->setWpQuery( $object );

		ob_start();
		aioseo()->head->output();
		$output = ob_get_clean();

		// Add the title tag to our own comment block.
		$pageTitle = aioseo()->helpers->escapeRegexReplacement( aioseo()->meta->title->filterPageTitle() );
		$output    = preg_replace( '#(<!--\sAll\sin\sOne\sSEO[a-zA-Z\s0-9.]+\s-->)#', "$1\r\n\t\t<title>$pageTitle</title>", $output, 1 );

		$this->restoreWpQuery();

		return $output;
	}

	/**
	 * Returns the data that we would output in the HEAD for the given object, but as JSON.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $object The object (post type or taxonomy).
	 * @return array         The data (will be JSONified by the WP REST API class).
	 */
	public function getHeadJson( $object ) {
		if ( apply_filters( 'aioseo_rest_api_disable', false, $object ) ) {
			return [];
		}

		$this->setWpQuery( $object );

		$keywordsInstance = new Meta\Keywords();
		$keywords         = $keywordsInstance->getKeywords();

		$siteVerificationInstance        = new Meta\SiteVerification();
		$webmasterTools                  = $siteVerificationInstance->meta();
		$miscellaneous                   = aioseo()->options->webmasterTools->miscellaneousVerification ?: '';
		$webmasterTools['miscellaneous'] = trim( $miscellaneous );

		$data = [
			'title'          => aioseo()->meta->title->getTitle(),
			'description'    => aioseo()->meta->description->getDescription(),
			'canonical_url'  => aioseo()->helpers->canonicalUrl(),
			'robots'         => aioseo()->meta->robots->meta(),
			'keywords'       => $keywords,
			'webmasterTools' => $webmasterTools,
			'schema'         => json_decode( aioseo()->schema->get() )
		];

		$data = array_merge(
			$data,
			aioseo()->social->output->getFacebookMeta(),
			aioseo()->social->output->getTwitterMeta()
		);

		$this->restoreWpQuery();

		return $data;
	}

	/**
	 * Returns the breadcrumb HTML.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $object The object (post type or taxonomy).
	 * @return string        The HTML breadcrumb.
	 */
	public function getBreadcrumb( $object ) {
		if ( apply_filters( 'aioseo_rest_api_disable', false, $object ) ) {
			return '';
		}

		$this->setWpQuery( $object );

		$output = aioseo()->breadcrumbs->frontend->display( false );

		$this->restoreWpQuery();

		return $output;
	}

	/**
	 * Returns the breadcrumb as a JSON.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $object The object (post type or taxonomy).
	 * @return array         The crumbs (will be JSONified by the WP REST API class).
	 */
	public function getBreadcrumbJson( $object ) {
		if ( apply_filters( 'aioseo_rest_api_disable', false, $object ) ) {
			return [];
		}

		$this->setWpQuery( $object );

		$data        = [];
		$breadcrumbs = aioseo()->breadcrumbs->frontend->getBreadcrumbs();
		foreach ( $breadcrumbs as $crumb ) {
			$data[] = [
				'label' => $crumb['label'],
				'link'  => $crumb['link']
			];
		}

		$this->restoreWpQuery();

		return $data;
	}

	/**
	 * Checks whether the current user can edit any of our meta data.
	 *
	 * @since 4.9.8
	 *
	 * @return bool Whether the current user is allowed to edit any of our meta data.
	 */
	protected function canEditMetaData() {
		return (
			current_user_can( 'aioseo_page_general_settings' ) ||
			current_user_can( 'aioseo_page_social_settings' ) ||
			current_user_can( 'aioseo_page_schema_settings' ) ||
			current_user_can( 'aioseo_page_advanced_settings' )
		);
	}

	/**
	 * Validates the meta data field of a request.
	 *
	 * NOTE: An empty JSON object decodes to an empty array, so an empty array passes as an empty object.
	 *
	 * @since 5.0.3
	 *
	 * @param  mixed            $value   The value.
	 * @param  \WP_REST_Request $request The request.
	 * @param  string           $param   The parameter name.
	 * @return true|\WP_Error            True if the value is valid, or the error.
	 */
	public function validateMetaData( $value, $request, $param ) {
		$valid = rest_validate_request_arg( $value, $request, $param );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		// The object schema type also accepts an empty string and a list.
		if ( ! $value instanceof \stdClass && ! $this->isMetaDataObject( $value ) ) {
			return $this->getInvalidMetaDataError( $param );
		}

		return true;
	}

	/**
	 * Checks whether the given meta data is an object, which is an empty or associative array.
	 *
	 * @since 5.0.3
	 *
	 * @param  mixed $metaData The meta data.
	 * @return bool            Whether the meta data is an object.
	 */
	protected function isMetaDataObject( $metaData ) {
		return is_array( $metaData ) && ( empty( $metaData ) || aioseo()->helpers->isArrayAssociative( $metaData ) );
	}

	/**
	 * Returns the error for meta data that isn't an object.
	 *
	 * @since 5.0.3
	 *
	 * @param  string    $param The parameter name.
	 * @return \WP_Error        The error.
	 */
	protected function getInvalidMetaDataError( $param ) {
		return new \WP_Error(
			'rest_invalid_param',
			sprintf(
				// Translators: 1 - The name of a REST API field.
				__( '%1$s must be an object.', 'all-in-one-seo-pack' ),
				$param
			),
			[ 'status' => 400 ]
		);
	}

	/**
	 * Returns the request's first meta data field whose value has the wrong type, with its error.
	 *
	 * NOTE: WooCommerce runs its batch items without the schema validation and saves each field on its own, so the
	 * whole item is checked before any field is saved: one invalid field stops the others, as on the single routes.
	 *
	 * @since 5.0.3
	 *
	 * @param  \WP_REST_Request $request The request.
	 * @return array|null                The field name and its error, or null when every field is valid.
	 */
	protected function getInvalidField( $request ) {
		if ( $request->has_param( 'aioseo_meta_data' ) && ! $this->isMetaDataObject( $request['aioseo_meta_data'] ) ) {
			return [ 'aioseo_meta_data', $this->getInvalidMetaDataError( 'aioseo_meta_data' ) ];
		}

		foreach ( array_keys( $this->deprecatedFields ) as $legacyField ) {
			if ( $request->has_param( $legacyField ) && ! is_string( $request[ $legacyField ] ) ) {
				return [ $legacyField, $this->getInvalidLegacyFieldError( $legacyField ) ];
			}
		}

		return null;
	}

	/**
	 * Returns the error for a legacy single value field that isn't a string.
	 *
	 * @since 5.0.3
	 *
	 * @param  string    $param The parameter name.
	 * @return \WP_Error        The error.
	 */
	protected function getInvalidLegacyFieldError( $param ) {
		return new \WP_Error(
			'rest_invalid_param',
			sprintf(
				// Translators: 1 - The name of a REST API field.
				__( '%1$s must be a string.', 'all-in-one-seo-pack' ),
				$param
			),
			[ 'status' => 400 ]
		);
	}

	/**
	 * Returns the error for a meta data update the current user isn't allowed to make.
	 *
	 * @since 5.0.3
	 *
	 * @return \WP_Error The error.
	 */
	protected function getMetaDataPermissionError() {
		return new \WP_Error(
			'forbidden',
			__( 'You do not have permission to edit the SEO data for this object.', 'all-in-one-seo-pack' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * Rejects a request with a meta data update the current user isn't allowed to make, before the object is saved.
	 *
	 * NOTE: WooCommerce runs its batch items as method calls, so those are still rejected after the save,
	 * in {@see self::setRejectedMetaDataResponse()}.
	 *
	 * @since 5.0.3
	 *
	 * @param  mixed            $result  The dispatch result.
	 * @param  \WP_REST_Request $request The request.
	 * @param  string           $route   The route.
	 * @param  array            $handler The route handler.
	 * @return mixed                     The error if we reject the request, or the dispatch result.
	 */
	public function rejectForbiddenMetaDataRequest( $result, $request, $route, $handler ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if (
			null !== $result ||
			! in_array( $request->get_method(), [ 'POST', 'PUT', 'PATCH' ], true ) ||
			! is_array( $handler['callback'] ?? null ) ||
			! $handler['callback'][0] instanceof \WP_REST_Controller
		) {
			return $result;
		}

		// Additional fields are keyed by the schema title, which is how the controller looks them up too.
		global $wp_rest_additional_fields; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$schema     = $handler['callback'][0]->get_item_schema();
		$objectType = $schema['title'] ?? '';
		$fields     = $wp_rest_additional_fields[ $objectType ] ?? []; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$objectName = $this->getObjectName( $objectType );
		foreach ( $fields as $fieldName => $field ) {
			if (
				! isset( $request[ $fieldName ] ) ||
				! is_array( $field['update_callback'] ?? null ) ||
				$this !== $field['update_callback'][0] ||
				! apply_filters( 'aioseo_rest_api_allow_update', true, $objectName )
			) {
				continue;
			}

			$metaData = isset( $this->deprecatedFields[ $fieldName ] )
				? [ $this->deprecatedFields[ $fieldName ] => $request[ $fieldName ] ]
				: $request[ $fieldName ];

			// A non-object is left to the schema validation and updateMetaData(), which report it.
			if ( ! $this->isMetaDataObject( $metaData ) ) {
				continue;
			}

			unset( $metaData['post_id'] );

			if ( ! empty( $metaData ) && ! $this->isAllowedToUpdate( $objectName ) ) {
				return $this->getMetaDataPermissionError();
			}
		}

		return $result;
	}

	/**
	 * Checks whether the user is allowed to update meta data for the given object.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $objectName The post type or taxonomy name.
	 * @return bool               Whether the user is allowed to update meta data for the object.
	 */
	abstract protected function isAllowedToUpdate( $objectName );

	/**
	 * Returns the post type or taxonomy name for the given REST object type.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $objectType The REST object type.
	 * @return string             The post type or taxonomy name.
	 */
	protected function getObjectName( $objectType ) {
		return $objectType;
	}

	/**
	 * Rejects a meta data update.
	 *
	 * NOTE: The error becomes the response on rest_request_after_callbacks instead of being returned
	 * here. WordPress stops the save before the after-insert hooks when a field callback returns an
	 * error, and WooCommerce discards the return value altogether.
	 *
	 * @since 5.0.3
	 *
	 * @param  \WP_REST_Request $request   The request.
	 * @param  int              $objectId  The ID of the object.
	 * @param  string           $fieldName The name of the rejected field.
	 * @param  \WP_Error        $error     The error.
	 * @return void
	 */
	protected function rejectMetaDataUpdate( $request, $objectId, $fieldName, $error ) {
		$this->rejectedMetaDataUpdates[] = [
			'request'   => $request,
			'objectId'  => (int) $objectId,
			'fieldName' => $fieldName,
			'error'     => $error
		];
	}

	/**
	 * Sets the error for a rejected meta data update as the response of the request.
	 *
	 * @since 5.0.3
	 *
	 * @param  mixed            $response The response.
	 * @param  array            $handler  The route handler.
	 * @param  \WP_REST_Request $request  The request.
	 * @return mixed                      The error if we rejected this request, or the response.
	 */
	public function setRejectedMetaDataResponse( $response, $handler, $request ) {
		if ( empty( $this->rejectedMetaDataUpdates ) ) {
			return $response;
		}

		// WooCommerce runs its batch items as method calls, so the item requests never reach this filter.
		if (
			is_array( $response ) &&
			isset( $handler['callback'][1] ) &&
			'batch_items' === $handler['callback'][1]
		) {
			return $this->setBatchItemErrors( $response, $request );
		}

		foreach ( $this->rejectedMetaDataUpdates as $index => $rejection ) {
			if ( $rejection['request'] !== $request ) {
				continue;
			}

			unset( $this->rejectedMetaDataUpdates[ $index ] );

			// An error from the route itself is closer to what the caller asked for, so it wins.
			return is_wp_error( $response ) ? $response : $rejection['error'];
		}

		return $response;
	}

	/**
	 * Replaces the results of the rejected items of a WooCommerce batch with their errors.
	 *
	 * @since 5.0.3
	 *
	 * @param  array            $response The batch results.
	 * @param  \WP_REST_Request $request  The batch request.
	 * @return array                      The batch results.
	 */
	protected function setBatchItemErrors( $response, $request ) {
		$rejections = [];
		foreach ( $this->rejectedMetaDataUpdates as $index => $rejection ) {
			// WooCommerce builds every item request on the route of the batch request.
			if ( $rejection['request']->get_route() !== $request->get_route() ) {
				continue;
			}

			$rejections[] = $rejection;

			unset( $this->rejectedMetaDataUpdates[ $index ] );
		}

		$actionMethods = [
			'create' => 'POST',
			'update' => 'PUT'
		];

		foreach ( $actionMethods as $action => $method ) {
			$batchItems = $request->get_param( $action );
			if ( empty( $response[ $action ] ) || ! is_array( $response[ $action ] ) || ! is_array( $batchItems ) ) {
				continue;
			}

			// WooCommerce adds one result per item, in the order of the items.
			$batchItems = array_values( $batchItems );
			foreach ( $response[ $action ] as $key => $item ) {
				if ( ! is_array( $item ) || isset( $item['error'] ) || ! isset( $item['id'], $batchItems[ $key ] ) ) {
					continue;
				}

				foreach ( $rejections as $index => $rejection ) {
					if ( ! $this->isBatchItemRejection( $rejection, $method, (int) $item['id'], $batchItems[ $key ] ) ) {
						continue;
					}

					$error = $rejection['error'];

					// The same shape WooCommerce uses for the items it refuses itself.
					$response[ $action ][ $key ] = [
						'id'    => $item['id'],
						'error' => [
							'code'    => $error->get_error_code(),
							'message' => $error->get_error_message(),
							'data'    => $error->get_error_data()
						]
					];

					unset( $rejections[ $index ] );

					break;
				}
			}
		}

		return $response;
	}

	/**
	 * Checks whether the given rejection belongs to the given item of a WooCommerce batch.
	 *
	 * NOTE: The object ID alone isn't enough, since a batch can list the same object more than once.
	 *
	 * @since 5.0.3
	 *
	 * @param  array  $rejection The rejection.
	 * @param  string $method    The HTTP method of the item request.
	 * @param  int    $objectId  The ID of the object of the item.
	 * @param  mixed  $batchItem The item, as sent in the batch request.
	 * @return bool              Whether the rejection belongs to the item.
	 */
	protected function isBatchItemRejection( $rejection, $method, $objectId, $batchItem ) {
		$fieldName = $rejection['fieldName'];

		return (
			$rejection['objectId'] === $objectId &&
			$rejection['request']->get_method() === $method &&
			is_array( $batchItem ) &&
			array_key_exists( $fieldName, $batchItem ) &&
			$rejection['request']->get_param( $fieldName ) === $batchItem[ $fieldName ]
		);
	}

	/**
	 * Removes internal fields from the meta data.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $data The data.
	 * @return array       The modified data.
	 */
	protected function removeInternalFields( $data ) {
		foreach ( $this->internalFields as $internalField ) {
			unset( $data[ $internalField ] );
		}

		return $data;
	}

	/**
	 * Sets the given object as the queried object of the main query.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $object The post object.
	 * @return void
	 */
	abstract protected function setWpQuery( $object );

	/**
	 * Restores the main query back to the original query.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	protected function restoreWpQuery() {
		if ( null === $this->originalQuery ) {
			return;
		}

		global $wp_query; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$wp_query = clone $this->originalQuery; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$this->originalQuery = null;
	}
}