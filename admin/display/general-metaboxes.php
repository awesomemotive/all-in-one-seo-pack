<?php
/**
 * General Metaboxes
 *
 * @package All_in_One_SEO_Pack
 * @since 2.3.3
 */

// @codingStandardsIgnoreStart
class aiosp_metaboxes {
// @codingStandardsIgnoreEnd

	/**
	 * Constructor
	 *
	 * AIOSEOP metaboxes constructor.
	 *
	 * @since 2.3.3
	 */
	function __construct() {
		// construct.
	}

	/**
	 * Display Metaboxes
	 *
	 * @since 2.3.3
	 *
	 * @param $add
	 * @param $meta
	 */
	static function display_extra_metaboxes( $add, $meta ) {
		echo "<div class='aioseop_metabox_wrapper' >";
		switch ( $meta['id'] ) :
			case 'aioseop-about':
				?>
				<div class="aioseop_metabox_text">
					<h2 style="display:inline;"><?php echo AIOSEOP_PLUGIN_NAME; ?></h2>
					<?php
					global $current_user;
					$user_id = $current_user->ID;
					$ignore  = get_user_meta( $user_id, 'aioseop_ignore_notice' );
					if ( ! empty( $ignore ) ) {
						$qa = array();
						wp_parse_str( $_SERVER['QUERY_STRING'], $qa );
						$qa['aioseop_reset_notices'] = 1;
						$url                         = '?' . build_query( $qa );
						echo '<p><a href="' . $url . '">' . __( 'Reset Dismissed Notices', 'all-in-one-seo-pack' ) . '</a></p>';
					}
					?>
					<?php if ( ! AIOSEOPPRO ) : ?>
						<p>
							<strong>
								<?php
								echo aiosp_common::get_upgrade_hyperlink( 'side', __( 'Pro Version', 'all-in-one-seo-pack' ), __( 'CLICK HERE', 'all-in-one-seo-pack' ), '_blank' );
								/* translators: after this string comes a list of exclusive features that are not included in the free version of the plugin */
								echo ' ' . __( 'to upgrade to Pro Version and get:', 'all-in-one-seo-pack' );
								?>
							</strong>
						</p>
					<?php endif; ?>
				</div>
				<?php
				// Fall-through.
			case 'aioseop-donate':
				?>
				<div>
				<?php if ( ! AIOSEOPPRO ) : ?>
						<div class="aioseop_metabox_text">
								<?php self::pro_meta_content(); ?>
						</div>
					<?php endif; ?>
					<div class="aioseop_metabox_feature">
						<div class="aiosp-di">
							<a class="dashicons di-twitter" target="_blank" href="https://twitter.com/aioseopack" title="Follow me on Twitter"></a>
							<a class="dashicons di-facebook" target="_blank" href="https://www.facebook.com/aioseopack" title="Follow me on Facebook"></a>
						</div>
					</div>
					<?php
					$aiosp_trans = new AIOSEOP_Translations();
					// Eventually if nothing is returned we should just remove this section.
					if ( get_locale() != 'en_US' ) :
						?>
						<div class="aioseop_translations">
							<strong>
								<?php
								if ( $aiosp_trans->percent_translated < 100 ) {
									if ( ! empty( $aiosp_trans->native_name ) ) {
										$maybe_native_name = $aiosp_trans->native_name;
									} else {
										$maybe_native_name = $aiosp_trans->name;
									}

									/* translators: %1$s, %2$s, etc. are placeholders and shouldn't be translated. %1$s expands to the number of languages All in One SEO Pack has been translated into, %2$s to the name of the plugin, $3%s to the percentage translated of the current language, $4%s to the language name, %5$s and %6$s to anchor tags with link to the translation page at translate.wordpress.org  */
									printf(
										__( '%1$s has been translated into %2$s languages, but currently the %3$s translation is only %4$s percent complete. %5$sClick here%6$s to help get it to 100 percent.', 'all-in-one-seo-pack' ),
										AIOSEOP_PLUGIN_NAME,
										$aiosp_trans->translated_count,
										$maybe_native_name,
										$aiosp_trans->percent_translated,
										"<a href=\"$aiosp_trans->translation_url\" target=\"_BLANK\">",
										'</a>'
									);
								}

								?>
							</strong>
						</div>
					<?php endif; ?>
				</div>
								<?php break; ?>
			<?php
			case 'aioseop-list':
				?>
				<div class="aioseop_metabox_text">
					<form
						<?php if ( AIOSEOPPRO ) : ?>
							action="https://semperplugins.us1.list-manage.com/subscribe/post?u=794674d3d54fdd912f961ef14&amp;id=b786958a9a"
						<?php else : ?>
							action="https://semperplugins.us1.list-manage.com/subscribe/post?u=794674d3d54fdd912f961ef14&amp;id=af0a96d3d9"
						<?php endif; ?>
						method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
						target="_blank">
						<h2><?php _e( 'Join our mailing list for tips, tricks, and WordPress secrets.', 'all-in-one-seo-pack' ); ?></h2>
						<p>
							<i><?php _e( 'Sign up today and receive a free copy of the e-book 5 SEO Tips for WordPress ($39 value).', 'all-in-one-seo-pack' ); ?></i>
						</p>
						<p>
							<input
									type="text" value="" name="EMAIL" class="required email" id="mce-EMAIL"
									placeholder="<?php _e( 'Email Address', 'all-in-one-seo-pack' ); ?>" aria-label="<?php _e( 'Enter your email address', 'all-in-one-seo-pack' ); ?>">
							<input
									type="submit" value="<?php _e( 'Subscribe', 'all-in-one-seo-pack' ); ?>" name="subscribe" id="mc-embedded-subscribe"
									class="button-primary" aria-label="<?php _e( 'Subscribe to our mailing list', 'all-in-one-seo-pack' ); ?>">
						</p>
					</form>
				</div>
				<?php break; ?>
			<?php
			case 'aioseop-support':
				?>
				<div class="aioseop_metabox_text">
					<ul>
						<li>
							<div class="aioseop_icon aioseop-icon-file"></div>
							<a target="_blank" rel="noopener noreferrer"
							href="https://semperplugins.com/documentation/">
								<?php
								/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
								printf( __( 'Read the %s user guide', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME );
								?>
							</a>
						</li>
						<li>
							<div class="aioseop_icon aioseop-icon-support"></div>
							<a target="_blank" rel="noopener noreferrer"
							title="<?php _e( 'All in One SEO Pro Plugin Support Forum', 'all-in-one-seo-pack' ); ?>"
							href="https://semperplugins.com/support/"><?php _e( 'Access our Premium Support Forums', 'all-in-one-seo-pack' ); ?></a>
						</li>
						<li>	
							<div class="aioseop_icon aioseop-icon-cog"></div>
							<a target="_blank" rel="noopener noreferrer" title="<?php _e( 'All in One SEO Pro Plugin Changelog', 'all-in-one-seo-pack' ); ?>"
								href="https://semperplugins.com/all-in-one-seo-pack-changelog/"><?php _e( 'View the Changelog', 'all-in-one-seo-pack' ); ?></a>
						</li>
						<li>	
							<div class="aioseop_icon aioseop-icon-youtube"></div>
							<a target="_blank" rel="noopener noreferrer"
							href="https://semperplugins.com/doc-type/video/"><?php _e( 'Watch video tutorials', 'all-in-one-seo-pack' ); ?></a>
						</li>
						<li>	
							<div class="aioseop_icon aioseop-icon-book"></div>
							<a target="_blank" rel="noopener noreferrer"
							href="https://semperplugins.com/documentation/quick-start-guide/"><?php _e( 'Getting started? Read the Beginners Guide', 'all-in-one-seo-pack' ); ?></a>
						</li>
					</ul>		
				</div>
				<?php break; ?>
		<?php endswitch; ?>
		</div>
		<?php
	}

	/**
	 * Pro Meta Content
	 *
	 * @since 2.3.11
	 */
	static function pro_meta_content() {

		echo '<ul>';

		if ( class_exists( 'WooCommerce' ) ) {
			echo '<li>' . __( 'Advanced support for WooCommerce', 'all-in-one-seo-pack' ) . '</li>';
		} else {
			echo '<li>' . __( 'Advanced support for e-commerce', 'all-in-one-seo-pack' ) . '</li>';
		}

		echo '<li>' . __( 'Video SEO Module', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'SEO for Categories, Tags and Custom Taxonomies', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'Social Meta for Categories, Tags and Custom Taxonomies', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'Advanced Google Analytics tracking', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'Greater control over display settings', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'Ad free (no banner adverts)', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'Access to Video Screencasts', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'Access to Premium Support Forums', 'all-in-one-seo-pack' ) . '</li>';
		echo '<li>' . __( 'Access to Knowledge Center', 'all-in-one-seo-pack' ) . '</li>';

		echo '</ul>';

		echo sprintf( __( '%1$sClick here%2$s to file a feature request/bug report.', 'all-in-one-seo-pack' ), '<a href="https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/new" target="_blank" rel="noopener noreferrer">', '</a>' );

	}

}
