<?php
/**
 * SEO Alerts email template.
 *
 * @since 4.9.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable Generic.Files.LineLength.MaxExceeded
?>
<div style="background-color: #f3f4f5; color: #141b38; font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 22px; margin: 0; padding: 0;">
	<span style="display: none !important; visibility: hidden; opacity: 0; height: 0; width: 0;"><?php echo esc_html( $preHeader ) ?></span>

	<div style="margin: 0 auto; padding: 70px 0; width: 100%; max-width: 680px;">
		<div style="background-color: #ffffff; border: 1px solid #e8e8eb;">
			<div style="padding-left: 20px; padding-right: 20px; padding-bottom: 20px; overflow-x: auto;">
				<div style="padding-top: 20px;">
					<img
							style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
							width="100"
							height="20"
							src="https://static.aioseo.io/report/ste/text-logo.jpg"
							alt="<?php echo esc_attr( AIOSEO_PLUGIN_SHORT_NAME ) ?>"
					/>
				</div>

				<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
					<thead>
					<tr>
						<th style="padding: 0; width: 100%; line-height: 1;"></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td style="padding: 0; word-break: break-word;">
							<div style="padding-top: 20px;">
								<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;"><?php echo esc_html( $heading ) ?></p>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>

			<div style="background-color: #004f9d; padding-bottom: 20px;">
				<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
					<thead>
					<tr>
						<th style="padding: 0; width: 100%; line-height: 1;"></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td style="padding: 0; word-break: break-word;">
							<div style="padding-right: 20px; padding-left: 20px; padding-top: 20px; line-height: 1;">
								<span style="color: #ffffff; margin-right: 3px; font-weight: 700; font-size: 28px; vertical-align: middle;"><?php esc_html_e( 'Hi there!', 'all-in-one-seo-pack' ); ?></span>

								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="28"
										height="28"
										src="https://static.aioseo.io/report/ste/emoji-1f44b.png"
										alt="Waving Hand Sign"
								/>
							</div>

							<div style="color: #ffffff; padding-right: 20px; padding-left: 20px; padding-top: 20px; font-size: 20px; line-height: 26px; font-weight: 400;">
								<?php echo esc_html( $subheading ) ?>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>

			<div style="padding: 20px;">
				<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
					<h2 style="font-size: 20px; font-weight: 700; line-height: 24px; margin-bottom: 0; margin-top: 0; vertical-align: middle; display: inline-block;"><?php esc_html_e( 'SEO Alerts', 'all-in-one-seo-pack' ); ?></h2>
				</div>

				<div style="padding: 20px;">
					<?php foreach ( $alertMessages as $message ) : ?>
						<div style="margin-bottom: 16px; font-size: 14px;">
							<?php // $message is escaped with esc_html() first; make_clickable() only adds esc_url()'d links. ?>
							<span style="vertical-align: middle;"><?php echo make_clickable( esc_html( $message ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</div>
					<?php endforeach; ?>

					<div style="margin-top: 20px; text-align: center;">
						<a
								href="<?php echo esc_attr( $links['manage'] ) ?>"
								style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
						>
							<?php esc_html_e( 'Manage SEO Alerts', 'all-in-one-seo-pack' ) ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div style="width: 600px; max-width: 90%; margin-top: 20px; margin-left: auto; margin-right: auto;">
			<div style="text-align: center;">
				<img
						style="border-radius: 9999px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-left: auto; margin-right: auto;"
						src="https://static.aioseo.io/report/ste/danny-circle.jpg"
						width="50"
						height="50"
						alt="<?php echo esc_attr( AIOSEO_PLUGIN_SHORT_NAME ) ?>"
				/>

				<p style="font-size: 14px; margin-bottom: 0; margin-top: 20px; text-align: center; color: #434960;">
					<?php
					// Translators: 1 - The plugin short name ("AIOSEO").
					printf( esc_html__( 'This email was auto-generated and sent from %1$s.', 'all-in-one-seo-pack' ), esc_html( AIOSEO_PLUGIN_SHORT_NAME ) )
					?>
				</p>

				<p style="font-size: 14px; margin-bottom: 0; margin-top: 10px; text-align: center; color: #434960;">
					<?php
					printf(
						// Translators: 1 - Opening link tag, 2 - Closing link tag.
						esc_html__( 'If you no longer want to receive these emails, disable SEO Alerts or remove your email address in the %1$sSEO Alerts settings%2$s.', 'all-in-one-seo-pack' ),
						'<a style="color: #005ae0; text-decoration: none;" href="' . esc_url( $links['manage'] ) . '">',
						'</a>'
					)
					?>
				</p>
			</div>

			<div style="margin-top: 20px;">
				<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
			</div>

			<div style="margin-top: 20px;">
				<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
					<tbody>
					<tr>
						<td style="line-height: 1; word-break: break-word;">
							<a
									style="color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
									href="<?php echo esc_attr( $links['marketing-site'] ) ?>"
							>
								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="82"
										height="17"
										src="https://static.aioseo.io/report/ste/text-logo.png"
										alt="<?php echo esc_attr( AIOSEO_PLUGIN_SHORT_NAME ) ?>"
								/>
							</a>
						</td>

						<td style="line-height: 1; text-align: right; word-break: break-word;">
							<a
									style="margin-right: 6px; color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
									href="<?php echo esc_attr( $links['facebook'] ) ?>"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/facebook.jpg"
										alt="Fb"
										width="20"
										height="20"
								/>
							</a>

							<a
									style="margin-right: 6px; color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
									href="<?php echo esc_attr( $links['linkedin'] ) ?>"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/linkedin.jpg"
										alt="In"
										width="20"
										height="20"
								/>
							</a>

							<a
									style="margin-right: 6px; color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
									href="<?php echo esc_attr( $links['youtube'] ) ?>"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/youtube.jpg"
										alt="Yt"
										width="20"
										height="20"
								/>
							</a>

							<a
									style="color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
									href="<?php echo esc_attr( $links['twitter'] ) ?>"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/x.jpg"
										alt="Tw"
										width="20"
										height="20"
								/>
							</a>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div> 