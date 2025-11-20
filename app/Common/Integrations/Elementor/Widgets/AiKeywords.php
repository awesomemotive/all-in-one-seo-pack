<?php
namespace AIOSEO\Plugin\Common\Integrations\Elementor\Widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Keyword Research Widget for Elementor.
 *
 * @since 4.9.0
 */
class AiKeywords extends \Elementor\Widget_Base {
	/**
	 * Get widget name.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'aioseo_ai_keywords';
	}

	/**
	 * Get widget title.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'AI Keyword Research', 'all-in-one-seo-pack' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-search';
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
				'label' => __( 'Keyword Research Settings', 'all-in-one-seo-pack' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'topic',
			[
				'label'       => __( 'Topic', 'all-in-one-seo-pack' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter topic for keyword research', 'all-in-one-seo-pack' ),
				'description' => __( 'AI will research and suggest relevant keywords.', 'all-in-one-seo-pack' ),
			]
		);

		$this->add_control(
			'provider',
			[
				'label'   => __( 'AI Provider', 'all-in-one-seo-pack' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'gemini',
				'options' => [
					'openai' => __( 'OpenAI', 'all-in-one-seo-pack' ),
					'claude' => __( 'Claude', 'all-in-one-seo-pack' ),
					'gemini' => __( 'Gemini', 'all-in-one-seo-pack' ),
				],
			]
		);

		$this->add_control(
			'show_primary',
			[
				'label'        => __( 'Show Primary Keywords', 'all-in-one-seo-pack' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'all-in-one-seo-pack' ),
				'label_off'    => __( 'No', 'all-in-one-seo-pack' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_long_tail',
			[
				'label'        => __( 'Show Long-tail Keywords', 'all-in-one-seo-pack' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'all-in-one-seo-pack' ),
				'label_off'    => __( 'No', 'all-in-one-seo-pack' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_questions',
			[
				'label'        => __( 'Show Question Keywords', 'all-in-one-seo-pack' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'all-in-one-seo-pack' ),
				'label_off'    => __( 'No', 'all-in-one-seo-pack' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_lsi',
			[
				'label'        => __( 'Show LSI Keywords', 'all-in-one-seo-pack' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'all-in-one-seo-pack' ),
				'label_off'    => __( 'No', 'all-in-one-seo-pack' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'generate_button',
			[
				'type'      => \Elementor\Controls_Manager::BUTTON,
				'text'      => __( 'Research Keywords', 'all-in-one-seo-pack' ),
				'event'     => 'aioseo:research:keywords',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Keywords Style', 'all-in-one-seo-pack' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label'     => __( 'Heading Color', 'all-in-one-seo-pack' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aioseo-keywords-heading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'keyword_color',
			[
				'label'     => __( 'Keyword Color', 'all-in-one-seo-pack' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aioseo-keyword-item' => 'color: {{VALUE}}',
				],
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

		?>
		<div class="aioseo-ai-keywords-widget"
			data-topic="<?php echo esc_attr( $settings['topic'] ); ?>"
			data-provider="<?php echo esc_attr( $settings['provider'] ); ?>"
			data-show-primary="<?php echo esc_attr( $settings['show_primary'] ); ?>"
			data-show-long-tail="<?php echo esc_attr( $settings['show_long_tail'] ); ?>"
			data-show-questions="<?php echo esc_attr( $settings['show_questions'] ); ?>"
			data-show-lsi="<?php echo esc_attr( $settings['show_lsi'] ); ?>">
			<div class="aioseo-keywords-container"></div>
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
		<div class="aioseo-ai-keywords-widget"
			data-topic="{{ settings.topic }}"
			data-provider="{{ settings.provider }}"
			data-show-primary="{{ settings.show_primary }}"
			data-show-long-tail="{{ settings.show_long_tail }}"
			data-show-questions="{{ settings.show_questions }}"
			data-show-lsi="{{ settings.show_lsi }}">
			<div class="aioseo-keywords-container">
				<p><?php esc_html_e( 'AI-generated keyword research will appear here. Click "Research Keywords" to start.', 'all-in-one-seo-pack' ); ?></p>
			</div>
		</div>
		<?php
	}
}
