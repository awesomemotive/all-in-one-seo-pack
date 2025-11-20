<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract base class for AI providers.
 *
 * @since 4.9.0
 */
abstract class Provider {
	/**
	 * The provider name.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * The API key.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $apiKey = '';

	/**
	 * The API endpoint.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $endpoint = '';

	/**
	 * Maximum tokens for response.
	 *
	 * @since 4.9.0
	 *
	 * @var int
	 */
	protected $maxTokens = 2000;

	/**
	 * Temperature setting (0-1).
	 *
	 * @since 4.9.0
	 *
	 * @var float
	 */
	protected $temperature = 0.7;

	/**
	 * Constructor.
	 *
	 * @since 4.9.0
	 *
	 * @param string $apiKey The API key.
	 */
	public function __construct( $apiKey = '' ) {
		$this->apiKey = $apiKey;
	}

	/**
	 * Get the provider name.
	 *
	 * @since 4.9.0
	 *
	 * @return string The provider name.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set the maximum tokens.
	 *
	 * @since 4.9.0
	 *
	 * @param int $tokens The maximum tokens.
	 * @return void
	 */
	public function setMaxTokens( $tokens ) {
		$this->maxTokens = (int) $tokens;
	}

	/**
	 * Set the temperature.
	 *
	 * @since 4.9.0
	 *
	 * @param float $temperature The temperature (0-1).
	 * @return void
	 */
	public function setTemperature( $temperature ) {
		$this->temperature = max( 0, min( 1, (float) $temperature ) );
	}

	/**
	 * Generate content using the AI provider.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $prompt      The prompt for content generation.
	 * @param  array  $options     Additional options.
	 * @return array               Response array with 'success' and 'content' or 'error'.
	 */
	abstract public function generateContent( $prompt, $options = [] );

	/**
	 * Analyze SEO for given content.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $content     The content to analyze.
	 * @param  string $keyword     The target keyword.
	 * @param  array  $options     Additional options.
	 * @return array               Response array with 'success' and 'analysis' or 'error'.
	 */
	abstract public function analyzeSeo( $content, $keyword = '', $options = [] );

	/**
	 * Research keywords and topics.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $topic       The topic to research.
	 * @param  array  $options     Additional options.
	 * @return array               Response array with 'success' and 'keywords' or 'error'.
	 */
	abstract public function researchKeywords( $topic, $options = [] );

	/**
	 * Optimize content for AEO (Answer Engine Optimization).
	 *
	 * @since 4.9.0
	 *
	 * @param  string $content     The content to optimize.
	 * @param  array  $questions   Related questions.
	 * @param  array  $options     Additional options.
	 * @return array               Response array with 'success' and 'optimized_content' or 'error'.
	 */
	abstract public function optimizeForAeo( $content, $questions = [], $options = [] );

	/**
	 * Make an API request.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $endpoint    The API endpoint.
	 * @param  array  $body        The request body.
	 * @param  array  $headers     Additional headers.
	 * @return array               Response array.
	 */
	protected function makeRequest( $endpoint, $body = [], $headers = [] ) {
		$defaultHeaders = [
			'Content-Type' => 'application/json',
		];

		$headers = array_merge( $defaultHeaders, $headers );

		$response = wp_remote_post(
			$endpoint,
			[
				'timeout' => 60,
				'headers' => $headers,
				'body'    => wp_json_encode( $body ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'error'   => $response->get_error_message(),
			];
		}

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = wp_remote_retrieve_body( $response );
		$data         = json_decode( $responseBody, true );

		if ( $responseCode !== 200 ) {
			return [
				'success' => false,
				'error'   => isset( $data['error']['message'] ) ? $data['error']['message'] : 'API request failed',
			];
		}

		return [
			'success' => true,
			'data'    => $data,
		];
	}

	/**
	 * Validate API key.
	 *
	 * @since 4.9.0
	 *
	 * @return bool Whether the API key is valid.
	 */
	public function validateApiKey() {
		return ! empty( $this->apiKey );
	}
}
