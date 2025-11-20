<?php
namespace AIOSEO\Plugin\Common\Integrations\Elementor\Controls;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Prompt Control for Elementor.
 *
 * @since 4.9.0
 */
class AiPrompt extends \Elementor\Base_Control {
	/**
	 * Get control type.
	 *
	 * @since 4.9.0
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'aioseo_ai_prompt';
	}

	/**
	 * Enqueue control scripts and styles.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_script(
			'aioseo-elementor-ai-prompt',
			AIOSEO_URL . 'dist/elementor-ai-prompt-control.js',
			[ 'jquery' ],
			AIOSEO_VERSION,
			true
		);

		wp_enqueue_style(
			'aioseo-elementor-ai-prompt',
			AIOSEO_URL . 'dist/elementor-ai-prompt-control.css',
			[],
			AIOSEO_VERSION
		);
	}

	/**
	 * Get default settings.
	 *
	 * @since 4.9.0
	 *
	 * @return array Default settings.
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'rows'        => 5,
			'placeholder' => __( 'Enter your AI prompt...', 'all-in-one-seo-pack' ),
			'ai_action'   => 'generate',
		];
	}

	/**
	 * Render control output in the editor.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<# if ( data.label ) {#>
				<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper">
				<textarea id="<?php echo esc_attr( $control_uid ); ?>"
					class="elementor-control-tag-area"
					rows="{{ data.rows }}"
					data-setting="{{ data.name }}"
					placeholder="{{ data.placeholder }}"></textarea>
				<div class="aioseo-ai-prompt-actions">
					<button type="button" class="aioseo-ai-generate-btn" data-action="{{ data.ai_action }}">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'Generate with AI', 'all-in-one-seo-pack' ); ?>
					</button>
					<select class="aioseo-ai-provider-select">
						<option value="openai"><?php esc_html_e( 'OpenAI', 'all-in-one-seo-pack' ); ?></option>
						<option value="claude"><?php esc_html_e( 'Claude', 'all-in-one-seo-pack' ); ?></option>
						<option value="gemini"><?php esc_html_e( 'Gemini', 'all-in-one-seo-pack' ); ?></option>
					</select>
				</div>
			</div>
			<# if ( data.description ) { #>
				<div class="elementor-control-field-description">{{{ data.description }}}</div>
			<# } #>
		</div>
		<?php
	}
}
