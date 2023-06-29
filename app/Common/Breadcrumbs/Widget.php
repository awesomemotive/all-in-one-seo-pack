<?php
namespace AIOSEO\Plugin\Common\Breadcrumbs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Widget.
 *
 * @since 4.1.1
 */
class Widget extends \WP_Widget {
	/**
	 * The default attributes.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $defaults = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		// Widget defaults.
		$this->defaults = [
			'title' => ''
		];

		// Widget Slug.
		$widgetSlug = 'aioseo-breadcrumb-widget';

		// Widget basics.
		$widgetOps = [
			'classname'   => $widgetSlug,
			'description' => esc_html__( 'Display the current page breadcrumb.', 'all-in-one-seo-pack' ),
		];

		// Widget controls.
		$controlOps = [
			'id_base' => $widgetSlug,
		];

		// Translators: 1 - The plugin short name ("AIOSEO").
		$name = sprintf( esc_html__( '%1$s - Breadcrumbs', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME );
		$name .= ' ' . esc_html__( '(legacy)', 'all-in-one-seo-pack' );
		parent::__construct( $widgetSlug, $name, $widgetOps, $controlOps );
	}

	/**
	 * Widget callback.
	 *
	 * @since 4.1.1
	 *
	 * @param  array $args     Widget args.
	 * @param  array $instance The widget instance options.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		// Title.
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			echo $args['after_title'];
		}

		// If not being previewed in the Customizer maybe show the dummy preview.
		if (
			! is_customize_preview() &&
			(
				false !== strpos( wp_get_referer(), admin_url( 'widgets.php' ) ) ||
				false !== strpos( wp_get_referer(), admin_url( 'customize.php' ) )
			)
		) {
			aioseo()->breadcrumbs->frontend->preview();
		} else {
			aioseo()->breadcrumbs->frontend->display();
		}

		echo $args['after_widget'];
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Widget option update.
	 *
	 * @since 4.1.1
	 *
	 * @param array $newInstance New instance options.
	 * @param array $oldInstance Old instance options.
	 * @return array              Processed new instance options.
	 */
	public function update( $newInstance, $oldInstance ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$newInstance['title'] = wp_strip_all_tags( $newInstance['title'] );

		return $newInstance;
	}

	/**
	 * Widget options form.
	 *
	 * @since 4.1.1
	 *
	 * @param array $instance The widget instance options.
	 * @return void
	 */
	public function form( $instance ) {
		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php echo esc_html( __( 'Title:', 'all-in-one-seo-pack' ) ); ?>
			</label>
			<input
					type="text"
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					value="<?php echo esc_attr( $instance['title'] ); ?>"
					class="widefat"
			/>
		</p>
		<?php
	}
}