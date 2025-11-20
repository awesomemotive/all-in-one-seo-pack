<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Manager to handle provider selection and operations.
 *
 * @since 4.9.0
 */
class AiManager {
	/**
	 * Available providers.
	 *
	 * @since 4.9.0
	 *
	 * @var array
	 */
	private $providers = [];

	/**
	 * Current provider instance.
	 *
	 * @since 4.9.0
	 *
	 * @var Provider|null
	 */
	private $currentProvider = null;

	/**
	 * Constructor.
	 *
	 * @since 4.9.0
	 */
	public function __construct() {
		$this->initProviders();
	}

	/**
	 * Initialize available providers.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	private function initProviders() {
		$options = aioseo()->options->ai;

		if ( ! empty( $options->openaiApiKey ) ) {
			$this->providers['openai'] = new OpenAi( $options->openaiApiKey );
		}

		if ( ! empty( $options->claudeApiKey ) ) {
			$this->providers['claude'] = new Claude( $options->claudeApiKey );
		}

		if ( ! empty( $options->geminiApiKey ) ) {
			$this->providers['gemini'] = new Gemini( $options->geminiApiKey );
		}
	}

	/**
	 * Get a provider by name.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $providerName The provider name.
	 * @return Provider|null        The provider instance or null.
	 */
	public function getProvider( $providerName ) {
		if ( isset( $this->providers[ strtolower( $providerName ) ] ) ) {
			return $this->providers[ strtolower( $providerName ) ];
		}

		return null;
	}

	/**
	 * Get the current provider (based on settings or default).
	 *
	 * @since 4.9.0
	 *
	 * @return Provider|null The current provider instance or null.
	 */
	public function getCurrentProvider() {
		if ( $this->currentProvider ) {
			return $this->currentProvider;
		}

		$options         = aioseo()->options->ai;
		$defaultProvider = $options->defaultProvider ?? 'openai';

		$this->currentProvider = $this->getProvider( $defaultProvider );

		// Fallback to first available provider
		if ( ! $this->currentProvider && ! empty( $this->providers ) ) {
			$this->currentProvider = reset( $this->providers );
		}

		return $this->currentProvider;
	}

	/**
	 * Set the current provider.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $providerName The provider name.
	 * @return bool                 Whether the provider was set successfully.
	 */
	public function setCurrentProvider( $providerName ) {
		$provider = $this->getProvider( $providerName );

		if ( $provider ) {
			$this->currentProvider = $provider;
			return true;
		}

		return false;
	}

	/**
	 * Get all available providers.
	 *
	 * @since 4.9.0
	 *
	 * @return array Array of provider instances.
	 */
	public function getAvailableProviders() {
		return $this->providers;
	}

	/**
	 * Check if any provider is configured.
	 *
	 * @since 4.9.0
	 *
	 * @return bool Whether any provider is available.
	 */
	public function hasProviders() {
		return ! empty( $this->providers );
	}

	/**
	 * Generate content using the current provider.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $prompt      The prompt.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function generateContent( $prompt, $options = [] ) {
		$provider = $this->getCurrentProvider();

		if ( ! $provider ) {
			return [
				'success' => false,
				'error'   => 'No AI provider is configured. Please add API keys in AIOSEO Settings > AI.',
			];
		}

		return $provider->generateContent( $prompt, $options );
	}

	/**
	 * Analyze SEO using the current provider.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $content     The content to analyze.
	 * @param  string $keyword     The target keyword.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function analyzeSeo( $content, $keyword = '', $options = [] ) {
		$provider = $this->getCurrentProvider();

		if ( ! $provider ) {
			return [
				'success' => false,
				'error'   => 'No AI provider is configured.',
			];
		}

		return $provider->analyzeSeo( $content, $keyword, $options );
	}

	/**
	 * Research keywords using the current provider.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $topic       The topic to research.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function researchKeywords( $topic, $options = [] ) {
		$provider = $this->getCurrentProvider();

		if ( ! $provider ) {
			return [
				'success' => false,
				'error'   => 'No AI provider is configured.',
			];
		}

		return $provider->researchKeywords( $topic, $options );
	}

	/**
	 * Optimize content for AEO using the current provider.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $content     The content to optimize.
	 * @param  array  $questions   Related questions.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function optimizeForAeo( $content, $questions = [], $options = [] ) {
		$provider = $this->getCurrentProvider();

		if ( ! $provider ) {
			return [
				'success' => false,
				'error'   => 'No AI provider is configured.',
			];
		}

		return $provider->optimizeForAeo( $content, $questions, $options );
	}

	/**
	 * Log AI interaction for history/analytics.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $action      The action performed.
	 * @param  array  $data        Interaction data.
	 * @return int|false           Log ID or false on failure.
	 */
	public function logInteraction( $action, $data = [] ) {
		global $wpdb;

		$table = $wpdb->prefix . 'aioseo_ai_interactions';

		$logData = [
			'action'       => $action,
			'provider'     => $this->currentProvider ? $this->currentProvider->getName() : 'unknown',
			'user_id'      => get_current_user_id(),
			'data'         => wp_json_encode( $data ),
			'created_at'   => current_time( 'mysql' ),
		];

		$result = $wpdb->insert( $table, $logData );

		return $result ? $wpdb->insert_id : false;
	}
}
