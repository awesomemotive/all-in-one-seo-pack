<?php
namespace AIOSEO\Plugin\Common\Integrations\Elementor\Widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Content Widget for Elementor.
 *
 * @since 4.9.0
 */
class AiContent extends \Elementor\Widget_Base {
	/**
	 * Get widget name.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'aioseo_ai_content';
	}

	/**
	 * Get widget title.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'AI Content Generator', 'all-in-one-seo-pack' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-ai';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 4.9.0
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general', 'aioseo' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'AI Content Settings', 'all-in-one-seo-pack' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'prompt',
			[
				'label'       => __( 'Content Prompt', 'all-in-one-seo-pack' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'placeholder' => __( 'Enter your content prompt here...', 'all-in-one-seo-pack' ),
				'description' => __( 'Describe what content you want the AI to generate.', 'all-in-one-seo-pack' ),
			]
		);

		$this->add_control(
			'provider',
			[
				'label'   => __( 'AI Provider', 'all-in-one-seo-pack' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'openai',
				'options' => [
					'openai' => __( 'OpenAI', 'all-in-one-seo-pack' ),
					'claude' => __( 'Claude', 'all-in-one-seo-pack' ),
					'gemini' => __( 'Gemini', 'all-in-one-seo-pack' ),
				],
			]
		);

		$this->add_control(
			'auto_generate',
			[
				'label'        => __( 'Auto Generate on Load', 'all-in-one-seo-pack' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'all-in-one-seo-pack' ),
				'label_off'    => __( 'No', 'all-in-one-seo-pack' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'generate_button',
			[
				'type'      => \Elementor\Controls_Manager::BUTTON,
				'text'      => __( 'Generate Content', 'all-in-one-seo-pack' ),
				'event'     => 'aioseo:generate:content',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Content Style', 'all-in-one-seo-pack' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Text Color', 'all-in-one-seo-pack' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aioseo-ai-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .aioseo-ai-content',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$prompt   = $settings['prompt'] ?? '';
		$provider = $settings['provider'] ?? 'openai';

		?>
		<div class="aioseo-ai-content-widget" data-provider="<?php echo esc_attr( $provider ); ?>" data-prompt="<?php echo esc_attr( $prompt ); ?>">
			<?php if ( ! empty( $prompt ) && 'yes' === $settings['auto_generate'] ) : ?>
				<div class="aioseo-ai-loading">
					<?php esc_html_e( 'Generating content...', 'all-in-one-seo-pack' ); ?>
				</div>
			<?php endif; ?>
			<div class="aioseo-ai-content"></div>
		</div>
		<?php
	}

	/**
	 * Render widget output in the editor.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	protected function content_template() {
		?>
		<#
		var prompt = settings.prompt || '';
		var provider = settings.provider || 'openai';
		#>
		<div class="aioseo-ai-content-widget" data-provider="{{ provider }}" data-prompt="{{ prompt }}">
			<# if ( prompt && 'yes' === settings.auto_generate ) { #>
				<div class="aioseo-ai-loading">
					<?php esc_html_e( 'Generating content...', 'all-in-one-seo-pack' ); ?>
				</div>
			<# } #>
			<div class="aioseo-ai-content">
				<p><?php esc_html_e( 'AI-generated content will appear here.', 'all-in-one-seo-pack' ); ?></p>
			</div>
		</div>
		<?php
	}
}
