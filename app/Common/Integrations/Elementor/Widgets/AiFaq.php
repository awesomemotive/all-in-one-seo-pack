<?php
namespace AIOSEO\Plugin\Common\Integrations\Elementor\Widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI FAQ Widget for Elementor - AEO Optimized.
 *
 * @since 4.9.0
 */
class AiFaq extends \Elementor\Widget_Base {
	/**
	 * Get widget name.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'aioseo_ai_faq';
	}

	/**
	 * Get widget title.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'AI FAQ Generator (AEO)', 'all-in-one-seo-pack' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 4.9.0
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-faq';
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
				'label' => __( 'FAQ Settings', 'all-in-one-seo-pack' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'topic',
			[
				'label'       => __( 'Topic', 'all-in-one-seo-pack' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter the topic for FAQs', 'all-in-one-seo-pack' ),
				'description' => __( 'AI will generate relevant questions and answers about this topic.', 'all-in-one-seo-pack' ),
			]
		);

		$this->add_control(
			'num_questions',
			[
				'label'   => __( 'Number of Questions', 'all-in-one-seo-pack' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 3,
				'max'     => 15,
				'step'    => 1,
				'default' => 5,
			]
		);

		$this->add_control(
			'provider',
			[
				'label'   => __( 'AI Provider', 'all-in-one-seo-pack' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'claude',
				'options' => [
					'openai' => __( 'OpenAI', 'all-in-one-seo-pack' ),
					'claude' => __( 'Claude', 'all-in-one-seo-pack' ),
					'gemini' => __( 'Gemini', 'all-in-one-seo-pack' ),
				],
			]
		);

		$this->add_control(
			'schema_enabled',
			[
				'label'        => __( 'Enable FAQ Schema', 'all-in-one-seo-pack' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'all-in-one-seo-pack' ),
				'label_off'    => __( 'No', 'all-in-one-seo-pack' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Add FAQ schema markup for better search engine visibility.', 'all-in-one-seo-pack' ),
			]
		);

		$this->add_control(
			'generate_button',
			[
				'type'      => \Elementor\Controls_Manager::BUTTON,
				'text'      => __( 'Generate FAQs', 'all-in-one-seo-pack' ),
				'event'     => 'aioseo:generate:faq',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'FAQ Style', 'all-in-one-seo-pack' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'question_color',
			[
				'label'     => __( 'Question Color', 'all-in-one-seo-pack' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aioseo-faq-question' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'answer_color',
			[
				'label'     => __( 'Answer Color', 'all-in-one-seo-pack' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aioseo-faq-answer' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'question_typography',
				'label'    => __( 'Question Typography', 'all-in-one-seo-pack' ),
				'selector' => '{{WRAPPER}} .aioseo-faq-question',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'answer_typography',
				'label'    => __( 'Answer Typography', 'all-in-one-seo-pack' ),
				'selector' => '{{WRAPPER}} .aioseo-faq-answer',
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
		$topic    = $settings['topic'] ?? '';

		?>
		<div class="aioseo-ai-faq-widget"
			data-topic="<?php echo esc_attr( $topic ); ?>"
			data-provider="<?php echo esc_attr( $settings['provider'] ); ?>"
			data-num-questions="<?php echo esc_attr( $settings['num_questions'] ); ?>"
			data-schema-enabled="<?php echo esc_attr( $settings['schema_enabled'] ); ?>">
			<div class="aioseo-faq-container"></div>
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
		var topic = settings.topic || '';
		#>
		<div class="aioseo-ai-faq-widget"
			data-topic="{{ topic }}"
			data-provider="{{ settings.provider }}"
			data-num-questions="{{ settings.num_questions }}"
			data-schema-enabled="{{ settings.schema_enabled }}">
			<div class="aioseo-faq-container">
				<p><?php esc_html_e( 'AI-generated FAQ will appear here. Click "Generate FAQs" to create content.', 'all-in-one-seo-pack' ); ?></p>
			</div>
		</div>
		<?php
	}
}
