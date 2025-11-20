<?php
namespace AIOSEO\Plugin\Common\Integrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor integration for AI features.
 *
 * @since 4.9.0
 */
class Elementor {
	/**
	 * Constructor.
	 *
	 * @since 4.9.0
	 */
	public function __construct() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueueEditorScripts' ] );
		add_action( 'elementor/editor/footer', [ $this, 'renderAiPanel' ] );
		add_action( 'elementor/widgets/register', [ $this, 'registerWidgets' ] );
		add_action( 'elementor/controls/register', [ $this, 'registerControls' ] );
	}

	/**
	 * Enqueue scripts for Elementor editor.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function enqueueEditorScripts() {
		wp_enqueue_script(
			'aioseo-elementor-ai',
			AIOSEO_URL . 'dist/elementor-ai.js',
			[ 'jquery', 'elementor-editor' ],
			AIOSEO_VERSION,
			true
		);

		wp_localize_script(
			'aioseo-elementor-ai',
			'aioseoElementorAi',
			[
				'restUrl'   => rest_url( 'aioseo/v1/' ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'enabled'   => aioseo()->options->ai->enabled,
				'providers' => $this->getAvailableProviders(),
				'i18n'      => [
					'generateContent' => __( 'Generate Content with AI', 'all-in-one-seo-pack' ),
					'optimizeSeo'     => __( 'Optimize for SEO', 'all-in-one-seo-pack' ),
					'optimizeAeo'     => __( 'Optimize for AEO', 'all-in-one-seo-pack' ),
					'researchKeywords' => __( 'Research Keywords', 'all-in-one-seo-pack' ),
					'generating'      => __( 'Generating...', 'all-in-one-seo-pack' ),
					'error'           => __( 'Error', 'all-in-one-seo-pack' ),
					'success'         => __( 'Success', 'all-in-one-seo-pack' ),
				],
			]
		);

		wp_enqueue_style(
			'aioseo-elementor-ai',
			AIOSEO_URL . 'dist/elementor-ai.css',
			[],
			AIOSEO_VERSION
		);
	}

	/**
	 * Get available AI providers.
	 *
	 * @since 4.9.0
	 *
	 * @return array Available providers.
	 */
	private function getAvailableProviders() {
		$aiManager = new \AIOSEO\Plugin\Common\Ai\AiManager();
		$providers = $aiManager->getAvailableProviders();

		$providerList = [];
		foreach ( $providers as $key => $provider ) {
			$providerList[] = [
				'key'  => $key,
				'name' => $provider->getName(),
			];
		}

		return $providerList;
	}

	/**
	 * Render AI panel in Elementor editor.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function renderAiPanel() {
		if ( ! aioseo()->options->ai->enabled ) {
			return;
		}
		?>
		<div id="aioseo-elementor-ai-panel" style="display: none;">
			<div class="aioseo-ai-panel-content">
				<h3><?php esc_html_e( 'AIOSEO AI Assistant', 'all-in-one-seo-pack' ); ?></h3>
				<div class="aioseo-ai-actions">
					<button class="aioseo-ai-btn" data-action="generate-content">
						<?php esc_html_e( 'Generate Content', 'all-in-one-seo-pack' ); ?>
					</button>
					<button class="aioseo-ai-btn" data-action="optimize-seo">
						<?php esc_html_e( 'Optimize SEO', 'all-in-one-seo-pack' ); ?>
					</button>
					<button class="aioseo-ai-btn" data-action="optimize-aeo">
						<?php esc_html_e( 'Optimize for AEO', 'all-in-one-seo-pack' ); ?>
					</button>
					<button class="aioseo-ai-btn" data-action="research-keywords">
						<?php esc_html_e( 'Research Keywords', 'all-in-one-seo-pack' ); ?>
					</button>
				</div>
				<div class="aioseo-ai-output"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Register custom Elementor widgets.
	 *
	 * @since 4.9.0
	 *
	 * @param  object $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function registerWidgets( $widgets_manager ) {
		if ( ! aioseo()->options->ai->elementor->enableAiWidgets ) {
			return;
		}

		require_once AIOSEO_DIR . '/app/Common/Integrations/Elementor/Widgets/AiContent.php';
		require_once AIOSEO_DIR . '/app/Common/Integrations/Elementor/Widgets/AiFaq.php';
		require_once AIOSEO_DIR . '/app/Common/Integrations/Elementor/Widgets/AiKeywords.php';

		$widgets_manager->register( new \AIOSEO\Plugin\Common\Integrations\Elementor\Widgets\AiContent() );
		$widgets_manager->register( new \AIOSEO\Plugin\Common\Integrations\Elementor\Widgets\AiFaq() );
		$widgets_manager->register( new \AIOSEO\Plugin\Common\Integrations\Elementor\Widgets\AiKeywords() );
	}

	/**
	 * Register custom Elementor controls.
	 *
	 * @since 4.9.0
	 *
	 * @param  object $controls_manager Elementor controls manager.
	 * @return void
	 */
	public function registerControls( $controls_manager ) {
		if ( ! aioseo()->options->ai->enabled ) {
			return;
		}

		require_once AIOSEO_DIR . '/app/Common/Integrations/Elementor/Controls/AiPrompt.php';

		$controls_manager->register( new \AIOSEO\Plugin\Common\Integrations\Elementor\Controls\AiPrompt() );
	}
}
