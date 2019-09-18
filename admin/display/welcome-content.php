<?php
/**
 * Welcome Content
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

?>
<div class="welcome-panel">
	<div class="welcome-panel-content">
		<div class="welcome-panel-column-container">
			<div>
				<h3><a href="https://semperplugins.com/all-in-one-seo-pack-3-2/" target="_blank"><?php echo esc_html( __( "Check out what's new in our latest release post!", 'all-in-one-seo-pack' ) ); ?></a></h3>
			</div>
			<div class="welcome-panel-column">
				<h3>
					<?php
					/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
					echo esc_html( sprintf( __( 'Support %s', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ) );
					?>
				</h3>
				<p class="message welcome-icon welcome-edit-page">
				<?php
					/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
					echo esc_html( sprintf( __( 'There are many ways you can help support %s.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ) );
				?>
					</p>
				<p class="message aioseop-message welcome-icon welcome-edit-page">
				<?php
					/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the premium version of the plugin, All in One SEO Pack Pro. */
					echo esc_html( sprintf( __( 'Upgrade to %s to access priority support and premium features.', 'all-in-one-seo-pack' ), 'All in One SEO Pack Pro' ) );
				?>
					</p>
				<p class="call-to-action">
					<a
						href="https://semperplugins.com/all-in-one-seo-pack-pro-version/?loc=aio_welcome"
						target="_blank"
						class="button button-primary button-orange"><?php echo __( 'Upgrade', 'all-in-one-seo-pack' ); ?></a>
				</p>
				<p class="message aioseop-message welcome-icon welcome-edit-page">
				<?php
					/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
					echo esc_html( sprintf( __( 'Help translate %s into your language.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ) );
				?>
					</p>
				<p class="call-to-action">
					<a
						href="https://translate.wordpress.org/projects/wp-plugins/all-in-one-seo-pack"
						class="button button-primary"
						target="_blank"><?php echo __( 'Translate', 'all-in-one-seo-pack' ); ?></a></p>
			</div>

			<div class="welcome-panel-column">
				<h3><?php echo esc_html( __( 'Get Started', 'all-in-one-seo-pack' ) ); ?></h3>
				<ul>
					<li>
						<a
							href="https://semperplugins.com/documentation/quick-start-guide/"
							target="_blank"
							class="welcome-icon welcome-add-page">
							<?php
							/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
							echo sprintf( __( 'Beginners Guide for %s', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME );
							?>
							</a>

					</li>
					<li>
						<a
							href="https://semperplugins.com/documentation/beginners-guide-to-xml-sitemaps/"
							target="_blank"
							class="welcome-icon welcome-add-page"><?php echo __( 'Beginners Guide for XML Sitemap module', 'all-in-one-seo-pack' ); ?></a>
					</li>
					<li>
						<a
							href="https://semperplugins.com/documentation/beginners-guide-to-social-meta/"
							target="_blank"
							class="welcome-icon welcome-add-page"><?php echo __( 'Beginners Guide for Social Meta module', 'all-in-one-seo-pack' ); ?></a>
					</li>
					<li>
						<a
							href="https://semperplugins.com/documentation/top-tips-for-good-on-page-seo/"
							target="_blank"
							class="welcome-icon welcome-add-page"><?php echo __( 'Tips for good on-page SEO', 'all-in-one-seo-pack' ); ?></a>
					</li>
					<li>
						<a
							href="https://semperplugins.com/documentation/quality-guidelines-for-seo-titles-and-descriptions/"
							target="_blank"
							class="welcome-icon welcome-add-page"><?php echo __( 'Quality guidelines for SEO titles and descriptions', 'all-in-one-seo-pack' ); ?></a>
					</li>
					<li>
						<a
							href="https://semperplugins.com/documentation/submitting-an-xml-sitemap-to-google/"
							target="_blank"
							class="welcome-icon welcome-add-page"><?php echo __( 'Submit an XML Sitemap to Google', 'all-in-one-seo-pack' ); ?></a>
					</li>
					<li>
						<a
							href="https://semperplugins.com/documentation/setting-up-google-analytics/"
							target="_blank"
							class="welcome-icon welcome-add-page"><?php echo __( 'Set up Google Analytics', 'all-in-one-seo-pack' ); ?></a>
					</li>
				</ul>
			</div>

			<div class="welcome-panel-column">
				<h3><?php echo esc_html( __( 'Did You Know?', 'all-in-one-seo-pack' ) ); ?></h3>
				<ul>
					<li>
						<a
							href="https://semperplugins.com/documentation/"
							target="_blank"
							class="welcome-icon welcome-learn-more"><?php echo __( 'We have complete documentation on every setting and feature', 'all-in-one-seo-pack' ); ?></a>

					</li>
					<li>
						<a
							href="https://semperplugins.com/videos/"
							target="_blank"
							class="welcome-icon welcome-learn-more"><?php echo __( 'Access to video tutorials about SEO with the Pro version', 'all-in-one-seo-pack' ); ?></a>
					</li>
					<li>
						<a
							href="https://semperplugins.com/all-in-one-seo-pack-pro-version/?loc=aio_welcome"
							target="_blank"
							class="welcome-icon welcome-learn-more"><?php echo __( 'Control SEO on categories, tags and custom taxonomies with the Pro version', 'all-in-one-seo-pack' ); ?></a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<p>
		<a href=" <?php echo get_admin_url( null, 'admin.php?page=' . AIOSEOP_PLUGIN_DIRNAME . '/aioseop_class.php' ); ?>  "><?php _e( 'Continue to the General Settings', 'all-in-one-seo-pack' ); ?></a> &raquo;
	</p>
</div>
