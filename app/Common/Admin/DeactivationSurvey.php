<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation survey.
 *
 * @since 4.5.5
 */
class DeactivationSurvey {
	/**
	 * The API URL we are calling.
	 *
	 * @since 4.5.5
	 *
	 * @var string
	 */
	public $apiUrl = 'https://plugin.aioseo.com/wp-json/am-deactivate-survey/v1/deactivation-data';

	/**
	 * Name for this plugin.
	 *
	 * @since 4.5.5
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Unique slug for this plugin.
	 *
	 * @since 4.5.5
	 *
	 * @var string
	 */
	public $plugin;

	/**
	 * Primary class constructor.
	 *
	 * @since 4.5.5
	 *
	 * @param string $name Plugin name.
	 * @param string $plugin Plugin slug.
	 */
	public function __construct( $name = '', $plugin = '' ) {
		$this->name   = $name;
		$this->plugin = $plugin;

		// Don't run deactivation survey on dev sites.
		if ( aioseo()->helpers->isDev() ) {
			// return;
		}

		add_action( 'admin_print_scripts', [ $this, 'js' ], 20 );
		add_action( 'admin_print_scripts', [ $this, 'css' ] );
		add_action( 'admin_footer', [ $this, 'modal' ] );
	}

	/**
	 * Returns the URL of the remote endpoint.
	 *
	 * @since 4.5.5
	 *
	 * @return string The URL.
	 */
	public function getApiUrl() {
		if ( defined( 'AIOSEO_DEACTIVATION_SURVEY_URL' ) ) {
			return AIOSEO_DEACTIVATION_SURVEY_URL;
		}

		return $this->apiUrl;
	}

	/**
	 * Checks if current admin screen is the plugins page.
	 *
	 * @since 4.5.5
	 *
	 * @return bool True if it is, false if not.
	 */
	public function isPluginPage() {
		$screen = aioseo()->helpers->getCurrentScreen();
		if ( empty( $screen->id ) ) {
			return false;
		}

		return in_array( $screen->id, [ 'plugins', 'plugins-network' ], true );
	}

	/**
	 * Survey javascript.
	 *
	 * @since 4.5.5
	 *
	 * @return void
	 */
	public function js() {
		if ( ! $this->isPluginPage() ) {
			return;
		}

		?>
		<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function() {
			var deactivateLink = document.querySelector('#the-list [data-slug="<?php echo esc_html( $this->plugin ); ?>"] span.deactivate a') ||
				document.querySelector('#deactivate-<?php echo esc_html( $this->plugin ); ?>'),
				overlay = document.querySelector('#am-deactivate-survey-<?php echo esc_html( $this->plugin ); ?>'),
				form = overlay.querySelector('form'),
				formOpen = false;

			deactivateLink.addEventListener('click', function(event) {
				event.preventDefault();
				overlay.style.display = 'table';
				formOpen = true;
				form.querySelector('.am-deactivate-survey-option:first-of-type input[type=radio]').focus();
			});

			form.addEventListener('change', function(event) {
				if (event.target.matches('input[type=radio]')) {
					event.preventDefault();
					Array.from(form.querySelectorAll('input[type=text], .error')).forEach(function(el) { el.style.display = 'none'; });
					Array.from(form.querySelectorAll('.am-deactivate-survey-option')).forEach(function(el) { el.classList.remove('selected'); });
					var option = event.target.closest('.am-deactivate-survey-option');
					option.classList.add('selected');
					
					var otherField = option.querySelector('input[type=text]');
					if (otherField) {
						otherField.style.display = 'block';
						otherField.focus();
					}
				}
			});

			form.addEventListener('click', function(event) {
				if (event.target.matches('.am-deactivate-survey-deactivate')) {
					event.preventDefault();
					window.location.href = deactivateLink.getAttribute('href');
				}
			});

			form.addEventListener('submit', function(event) {
				event.preventDefault();
				if (!form.querySelector('input[type=radio]:checked')) {
					if(!form.querySelector('span[class="error"]')) {
						form.querySelector('.am-deactivate-survey-footer')
						.insertAdjacentHTML('afterbegin', '<span class="error"><?php echo esc_js( __( 'Please select an option', 'all-in-one-seo-pack' ) ); ?></span>');
					}
					return;
				}

				var selected = form.querySelector('.selected');
				var otherField = selected.querySelector('input[type=text]');
				var data = {
					code: selected.querySelector('input[type=radio]').value,
					reason: selected.querySelector('.am-deactivate-survey-option-reason').textContent,
					details: otherField ? otherField.value : '',
					site: '<?php echo esc_url( home_url() ); ?>',
					plugin: '<?php echo esc_html( $this->plugin ); ?>'
				}

				var submitSurvey = fetch('<?php echo esc_url( $this->getApiUrl() ); ?>', {
					method: 'POST',
					body: JSON.stringify(data),
					headers: { 'Content-Type': 'application/json' }
				});

				submitSurvey.then(function() {
					window.location.href = deactivateLink.getAttribute('href');
				});
			});

			document.addEventListener('keyup', function(event) {
				if (27 === event.keyCode && formOpen) {
					overlay.style.display = 'none';
					formOpen = false;
					deactivateLink.focus();
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Survey CSS.
	 *
	 * @since 4.5.5
	 *
	 * @return void
	 */
	public function css() {
		if ( ! $this->isPluginPage() ) {
			return;
		}

		?>
		<style type="text/css">
		.am-deactivate-survey-modal {
			display: none;
			table-layout: fixed;
			position: fixed;
			z-index: 9999;
			width: 100%;
			height: 100%;
			text-align: center;
			font-size: 14px;
			top: 0;
			left: 0;
			background: rgba(0,0,0,0.8);
		}
		.am-deactivate-survey-wrap {
			display: table-cell;
			vertical-align: middle;
		}
		.am-deactivate-survey {
			background-color: #fff;
			max-width: 550px;
			margin: 0 auto;
			padding: 30px;
			text-align: left;
		}
		.am-deactivate-survey .error {
			display: block;
			color: red;
			margin: 0 0 10px 0;
		}
		.am-deactivate-survey-title {
			display: block;
			font-size: 18px;
			font-weight: 700;
			text-transform: uppercase;
			border-bottom: 1px solid #ddd;
			padding: 0 0 18px 0;
			margin: 0 0 18px 0;
		}
		.am-deactivate-survey-title span {
			color: #999;
			margin-right: 10px;
		}
		.am-deactivate-survey-desc {
			display: block;
			font-weight: 600;
			margin: 0 0 18px 0;
		}
		.am-deactivate-survey-option {
			margin: 0 0 10px 0;
		}
		.am-deactivate-survey-option-input {
			margin-right: 10px !important;
		}
		.am-deactivate-survey-option-details {
			display: none;
			width: 90%;
			margin: 10px 0 0 30px;
		}
		.am-deactivate-survey-footer {
			margin-top: 18px;
		}
		.am-deactivate-survey-deactivate {
			float: right;
			font-size: 13px;
			color: #ccc;
			text-decoration: none;
			padding-top: 7px;
		}
		</style>
		<?php
	}

	/**
	 * Survey modal.
	 *
	 * @since 4.5.5
	 *
	 * @return void
	 */
	public function modal() {
		if ( ! $this->isPluginPage() ) {
			return;
		}

		$options = [
			1 => [
				'title' => esc_html__( 'I no longer need the plugin', 'all-in-one-seo-pack' ),
			],
			2 => [
				'title'   => esc_html__( 'I\'m switching to a different plugin', 'all-in-one-seo-pack' ),
				'details' => esc_html__( 'Please share which plugin', 'all-in-one-seo-pack' ),
			],
			3 => [
				'title' => esc_html__( 'I couldn\'t get the plugin to work', 'all-in-one-seo-pack' ),
			],
			4 => [
				'title' => esc_html__( 'It\'s a temporary deactivation', 'all-in-one-seo-pack' ),
			],
			5 => [
				'title'   => esc_html__( 'Other', 'all-in-one-seo-pack' ),
				'details' => esc_html__( 'Please share the reason', 'all-in-one-seo-pack' ),
			],
		];
		?>

		<div class="am-deactivate-survey-modal" id="am-deactivate-survey-<?php echo esc_html( $this->plugin ); ?>">
			<div class="am-deactivate-survey-wrap">
				<form class="am-deactivate-survey" method="post">
					<span class="am-deactivate-survey-title"><span class="dashicons dashicons-testimonial"></span><?php echo ' ' . esc_html__( 'Quick Feedback', 'all-in-one-seo-pack' ); ?></span>
					<span class="am-deactivate-survey-desc">
						<?php
						echo esc_html(
							sprintf(
								// Translators: 1 - The plugin name.
								__( 'If you have a moment, please share why you are deactivating %1$s:', 'all-in-one-seo-pack' ),
								$this->name
							)
						);
						?>
					</span>
					<div class="am-deactivate-survey-options">
						<?php foreach ( $options as $id => $option ) : ?>
							<div class="am-deactivate-survey-option">
								<label for="am-deactivate-survey-option-<?php echo esc_html( $this->plugin ); ?>-<?php echo intval( $id ); ?>" class="am-deactivate-survey-option-label">
									<input
										id="am-deactivate-survey-option-<?php echo esc_html( $this->plugin ); ?>-<?php echo intval( $id ); ?>"
										class="am-deactivate-survey-option-input"
										type="radio"
										name="code"
										value="<?php echo intval( $id ); ?>"
									/>
									<span class="am-deactivate-survey-option-reason"><?php echo esc_html( $option['title'] ); ?></span>
								</label>
								<?php if ( ! empty( $option['details'] ) ) : ?>
									<input class="am-deactivate-survey-option-details" type="text" placeholder="<?php echo esc_html( $option['details'] ); ?>" />
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="am-deactivate-survey-footer">
						<button type="submit" class="am-deactivate-survey-submit button button-primary button-large">
							<?php
							echo sprintf(
								// Translators: 1 - & symbol.
								esc_html__( 'Submit %1$s Deactivate', 'all-in-one-seo-pack' ),
								'&amp;'
							);
							?>
						</button>
						<a href="#" class="am-deactivate-survey-deactivate">
						<?php
						echo sprintf(
							// Translators: 1 - & symbol.
							esc_html__( 'Skip %1$s Deactivate', 'all-in-one-seo-pack' ),
							'&amp;'
						);
						?>
						</a>
					</div>
				</form>
			</div>
		</div>
		<?php
	}
}