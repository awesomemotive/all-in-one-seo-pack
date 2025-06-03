<?php
/**
 * Summary report view.
 *
 * @since 4.7.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable Generic.Files.LineLength.MaxExceeded
?>
<div style="background-color: #f3f4f5; color: #141b38; font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 22px; margin: 0; padding: 0;">
	<span style="display: none !important; visibility: hidden; opacity: 0; height: 0; width: 0;"><?php echo $preHeader ?? '' ?></span>

	<div style="margin: 0 auto; padding: 70px 0; width: 100%; max-width: 680px;">
		<div style="background-color: #ffffff; border: 1px solid #e8e8eb;">
			<div style="padding-left: 20px; padding-right: 20px; padding-bottom: 20px;">
				<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
					<thead>
					<tr>
						<th style="padding: 0; width: 60%; line-height: 1;"></th>
						<th style="padding: 0; width: 40%; line-height: 1;"></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td style="padding: 0;">
							<div style="padding-top: 20px;">
								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none;"
										width="100"
										height="20"
										src="https://static.aioseo.io/report/ste/text-logo.jpg"
										alt="<?php echo esc_attr( AIOSEO_PLUGIN_SHORT_NAME ) ?>"
								/>
							</div>
						</td>

						<td style="padding: 0; word-break: break-word;">
							<div style="padding-top: 20px; font-size: 12px; text-align: right; line-height: 15px;"><?php echo $dateRange['range'] ?? ''; ?></div>
						</td>
					</tr>

					<tr>
						<td style="padding: 0; word-break: break-word;">
							<div style="padding-top: 10px;">
								<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;"><?php echo $heading ?? ''; ?></p>
							</div>
						</td>

						<td style="padding: 0; word-break: break-word;">
							<div style="padding-top: 10px; font-size: 12px; text-align: right; line-height: 15px;">
								<a
										href="<?php echo site_url(); ?>"
										style="color: #005ae0; font-weight: normal; text-decoration: none;"
								><?php echo site_url(); ?></a>
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
						<th style="padding: 0; width: 70%; line-height: 1;"></th>
						<th style="padding: 0; width: 30%; line-height: 1;"></th>
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

							<div style="color: #ffffff; padding-right: 20px; padding-left: 20px; padding-top: 20px; font-size: 20px; line-height: 26px;  font-weight: 400;">
								<?php echo $subheading ?? ''; ?>
							</div>
						</td>

						<td style="padding: 0; text-align: right; word-break: break-word;">
							<img
									style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; padding-top: 20px;"
									width="142"
									height="140"
									src="<?php echo esc_attr( 'https://static.aioseo.io/report/ste/' . ( $iconCalendar ?? '' ) . '.png' ) ?>"
									alt=""
							/>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>

		<?php if ( aioseo()->siteHealth->shouldUpdate() ) { ?>
			<div style="margin-top: 20px;">
				<div style="text-align: center; font-size: 14px; border-radius: 4px; margin: 0; padding: 8px 12px; background-color: #fffbeb; border: 1px solid #f18200;">
					<?php
					printf(
						// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Opening link tag, 3 - HTML arrow, 4 - Closing link tag.
						__( 'An update is available for %1$s. %2$sUpgrade to the latest version%3$s%4$s', 'all-in-one-seo-pack' ),
						AIOSEO_PLUGIN_SHORT_NAME,
						'<a href="' . ( $links['update'] ?? '#' ) . '" style="color: #005ae0; font-weight: normal; text-decoration: underline;">',
						'&nbsp;&rarr;',
						'</a>'
					)
					?>
				</div>
			</div>
		<?php } ?>

		<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
			<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
				<img
						style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
						width="35"
						height="35"
						src="https://static.aioseo.io/report/ste/icon-report.png"
						alt=""
				/>

				<h2 style="font-size: 20px; font-weight: 700; line-height: 24px; margin-bottom: 0; margin-top: 0; vertical-align: middle; display: inline-block;"><?php esc_html_e( 'SEO Report', 'all-in-one-seo-pack' ); ?></h2>
			</div>

			<div style="padding: 20px;">
				<?php if ( ! empty( $statisticsReport['posts']['winning'] ) ) { ?>
					<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
						<tbody>
						<tr>
							<td style="padding: 0; word-break: break-word;">
								<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;"><?php esc_html_e( 'Top Winning Posts', 'all-in-one-seo-pack' ); ?></p>
							</td>

							<td style="text-align: right; word-break: break-word;">
								<?php if ( ! empty( $statisticsReport['posts']['winning']['url'] ) ) { ?>
									<a
											href="<?php echo esc_attr( $statisticsReport['posts']['winning']['url'] ) ?>"
											style="color: #005ae0; font-weight: 700; text-decoration: underline;"
									>
										<?php esc_html_e( 'View All', 'all-in-one-seo-pack' ); ?>
									</a>
								<?php } ?>
							</td>
						</tr>
						</tbody>
					</table>

					<div style="margin-top: 16px; overflow-x: auto;">
						<table style="min-width: 460px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
							<thead>
							<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
								<th style="width: 59%; background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
									<?php esc_html_e( 'Post', 'all-in-one-seo-pack' ); ?>
								</th>

								<th style="width: <?php echo $statisticsReport['posts']['winning']['show_tru_seo'] ? '17%' : '0' ?>; text-align: center; background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
									<?php if ( $statisticsReport['posts']['winning']['show_tru_seo'] ) { ?>
										TruSEO
									<?php } ?>
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960;  line-height: 1;">
									<?php esc_html_e( 'Clicks', 'all-in-one-seo-pack' ); ?>
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
									<?php esc_html_e( 'Diff', 'all-in-one-seo-pack' ); ?>
								</th>
							</tr>
							</thead>

							<tbody>
							<?php foreach ( ( $statisticsReport['posts']['winning']['items'] ?? [] ) as $i => $item ) { ?>
								<tr style="<?php echo 0 === $i % 2 ? 'background-color: #f3f4f5;' : '' ?>">
									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
											<a style="color: #141b38; font-weight: normal; text-decoration: none;"<?php echo ! empty( $item['url'] ) ? ' href="' . esc_attr( $item['url'] ) . '"' : '' ?>>
												<?php echo $item['title']; ?>
											</a>
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<?php if ( ! empty( $item['tru_seo'] ) ) { ?>
											<div style="padding: 6px;">
												<div style="width: 45px; padding: 6px; margin-left: auto; margin-right: auto; font-size: 12px; text-align: center; line-height: 1; border-radius: 4px; border-width: 1px; border-style: solid; <?php echo "color: {$item['tru_seo']['color']}"; ?>">
													<?php echo $item['tru_seo']['text']; ?>
												</div>
											</div>
										<?php } ?>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
											<?php echo $item['clicks']; ?>
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 0; <?php echo "color: {$item['difference']['clicks']['color']}"; ?>">
											<?php if ( '#00aa63' === $item['difference']['clicks']['color'] ) { ?>
												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>
											<?php } ?>

											<?php if ( '#df2a4a' === $item['difference']['clicks']['color'] ) { ?>
												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>
											<?php } ?>

											<span style="display: inline-block; vertical-align: middle; font-size: 14px;"><?php echo $item['difference']['clicks']['text']; ?></span>
										</div>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>

				<?php if ( ! empty( $statisticsReport['posts']['losing'] ) ) { ?>
					<?php if ( ! empty( $statisticsReport['posts']['winning'] ) ) { ?>
						<div style="margin-top: 20px; margin-bottom: 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
					<?php } ?>

					<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
						<tbody>
						<tr>
							<td style="padding: 0; word-break: break-word;">
								<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;"><?php esc_html_e( 'Top Losing Posts', 'all-in-one-seo-pack' ); ?></p>
							</td>

							<td style="text-align: right; word-break: break-word;">
								<?php if ( ! empty( $statisticsReport['posts']['losing']['url'] ) ) { ?>
									<a
											href="<?php echo esc_attr( $statisticsReport['posts']['losing']['url'] ) ?>"
											style="color: #005ae0; font-weight: 700; text-decoration: underline;"
									>
										<?php esc_html_e( 'View All', 'all-in-one-seo-pack' ); ?>
									</a>
								<?php } ?>
							</td>
						</tr>
						</tbody>
					</table>

					<div style="margin-top: 16px; overflow-x: auto;">
						<table style="table-layout: fixed; min-width: 460px; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
							<thead>
							<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
								<th style="width: 59%; background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
									<?php esc_html_e( 'Post', 'all-in-one-seo-pack' ); ?>
								</th>

								<th style="width: <?php echo $statisticsReport['posts']['losing']['show_tru_seo'] ? '17%' : '0' ?>; text-align: center; background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
									<?php if ( $statisticsReport['posts']['losing']['show_tru_seo'] ) { ?>
										TruSEO
									<?php } ?>
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
									<?php esc_html_e( 'Clicks', 'all-in-one-seo-pack' ); ?>
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
									<?php esc_html_e( 'Diff', 'all-in-one-seo-pack' ); ?>
								</th>
							</tr>
							</thead>

							<tbody>
							<?php foreach ( ( $statisticsReport['posts']['losing']['items'] ?? [] ) as $i => $item ) { ?>
								<tr style="<?php echo 0 === $i % 2 ? 'background-color: #f3f4f5;' : '' ?>">
									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
											<a style="color: #141b38; font-weight: normal; text-decoration: none;"<?php echo ! empty( $item['url'] ) ? ' href="' . esc_attr( $item['url'] ) . '"' : '' ?>>
												<?php echo $item['title']; ?>
											</a>
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<?php if ( ! empty( $item['tru_seo'] ) ) { ?>
											<div style="padding: 6px;">
												<div style="width: 45px; padding: 6px; margin-left: auto; margin-right: auto; font-size: 12px; text-align: center; line-height: 1; border-radius: 4px; border-width: 1px; border-style: solid; <?php echo "color: {$item['tru_seo']['color']}"; ?>">
													<?php echo $item['tru_seo']['text']; ?>
												</div>
											</div>
										<?php } ?>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
											<?php echo $item['clicks']; ?>
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 0; <?php echo "color: {$item['difference']['clicks']['color']}"; ?>">
											<?php if ( '#00aa63' === $item['difference']['clicks']['color'] ) { ?>
												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>
											<?php } ?>

											<?php if ( '#df2a4a' === $item['difference']['clicks']['color'] ) { ?>
												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>
											<?php } ?>

											<span style="display: inline-block; vertical-align: middle; font-size: 14px;"><?php echo $item['difference']['clicks']['text']; ?></span>
										</div>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>

				<?php if ( ! empty( $statisticsReport['keywords']['winning'] ) || ! empty( $statisticsReport['keywords']['losing'] ) ) { ?>
					<?php if ( ! empty( $statisticsReport['posts']['winning'] ) || ! empty( $statisticsReport['posts']['losing'] ) ) { ?>
						<div style="margin-top: 20px; margin-bottom: 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
					<?php } ?>
					<div style="overflow-x: auto;">
						<table style="min-width: 600px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
							<thead>
							<tr>
								<th style="width: 47%; line-height: 1;"></th>
								<th style="width: 6%; line-height: 1;"></th>
								<th style="width: 47%; line-height: 1;"></th>
							</tr>
							</thead>

							<tbody>
							<tr style="height: 1px;">
								<td style="vertical-align: top; word-break: break-word;">
									<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<tbody>
										<tr>
											<td style="padding: 0; word-break: break-word;">
												<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
													<?php esc_html_e( 'Top Winning Keywords', 'all-in-one-seo-pack' ); ?>
												</p>
											</td>

											<td style="text-align: right; word-break: break-word;">
												<?php if ( ! empty( $statisticsReport['keywords']['winning']['url'] ) ) { ?>
													<a
															href="<?php echo esc_attr( $statisticsReport['keywords']['winning']['url'] ) ?>"
															style="color: #005ae0; font-weight: 700; text-decoration: underline;"
													>
														<?php esc_html_e( 'View All', 'all-in-one-seo-pack' ); ?>
													</a>
												<?php } ?>
											</td>
										</tr>
										</tbody>
									</table>

									<table style="margin-top: 16px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<thead>
										<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
											<th style="width: 64%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
												<?php esc_html_e( 'Keyword', 'all-in-one-seo-pack' ); ?>
											</th>
											<th style="width: 16%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
												<?php esc_html_e( 'Clicks', 'all-in-one-seo-pack' ); ?>
											</th>
											<th style="width: 20%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
												<?php esc_html_e( 'Diff', 'all-in-one-seo-pack' ); ?>
											</th>
										</tr>
										</thead>

										<tbody>
										<?php foreach ( ( $statisticsReport['keywords']['winning']['items'] ?? [] ) as $i => $item ) { ?>
											<tr style="<?php echo 0 === $i % 2 ? 'background-color: #f3f4f5;' : '' ?>">
												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
														<?php echo $item['title']; ?>
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
														<?php echo $item['clicks']; ?>
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 0; <?php echo "color: {$item['difference']['clicks']['color']}"; ?>">
														<?php if ( '#00aa63' === $item['difference']['clicks']['color'] ) { ?>
															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>
														<?php } ?>

														<?php if ( '#df2a4a' === $item['difference']['clicks']['color'] ) { ?>
															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>
														<?php } ?>

														<span style="display: inline-block; vertical-align: middle; font-size: 14px;"><?php echo $item['difference']['clicks']['text']; ?></span>
													</div>
												</td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
								</td>

								<td style="height: inherit; padding: 0; text-align: center; vertical-align: baseline; overflow: hidden; word-break: break-word;">
									<div style="width: 1px; margin-left: auto; margin-right: auto; background-color: #e5e5e5; height: 100%;"></div>
								</td>

								<td style="vertical-align: top; word-break: break-word;">
									<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<tbody>
										<tr>
											<td style="padding: 0; word-break: break-word;">
												<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
													<?php esc_html_e( 'Top Losing Keywords', 'all-in-one-seo-pack' ); ?>
												</p>
											</td>

											<td style="text-align: right; word-break: break-word;">
												<?php if ( ! empty( $statisticsReport['keywords']['losing']['url'] ) ) { ?>
													<a
															href="<?php echo esc_attr( $statisticsReport['keywords']['losing']['url'] ) ?>"
															style="color: #005ae0; font-weight: 700; text-decoration: underline;"
													>
														<?php esc_html_e( 'View All', 'all-in-one-seo-pack' ); ?>
													</a>
												<?php } ?>
											</td>
										</tr>
										</tbody>
									</table>

									<table style="margin-top: 16px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<thead>
										<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
											<th style="width: 64%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
												<?php esc_html_e( 'Keyword', 'all-in-one-seo-pack' ); ?>
											</th>
											<th style="width: 16%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
												<?php esc_html_e( 'Clicks', 'all-in-one-seo-pack' ); ?>
											</th>
											<th style="width: 20%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
												<?php esc_html_e( 'Diff', 'all-in-one-seo-pack' ); ?>
											</th>
										</tr>
										</thead>

										<tbody>
										<?php foreach ( ( $statisticsReport['keywords']['losing']['items'] ?? [] ) as $i => $item ) { ?>
											<tr style="<?php echo 0 === $i % 2 ? 'background-color: #f3f4f5;' : '' ?>">
												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
														<?php echo $item['title']; ?>
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
														<?php echo $item['clicks']; ?>
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 0; <?php echo "color: {$item['difference']['clicks']['color']}"; ?>">
														<?php if ( '#00aa63' === $item['difference']['clicks']['color'] ) { ?>
															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>
														<?php } ?>

														<?php if ( '#df2a4a' === $item['difference']['clicks']['color'] ) { ?>
															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>
														<?php } ?>

														<span style="display: inline-block; vertical-align: middle; font-size: 14px;"><?php echo $item['difference']['clicks']['text']; ?></span>
													</div>
												</td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				<?php } ?>

				<?php if ( ! empty( $upsell['search-statistics'] ) ) { ?>
					<div>
						<img
								style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
								width="638"
								height="146"
								src="https://static.aioseo.io/report/ste/banner-search-statistics-cta-upsell.jpg"
								alt=""
						/>

						<p style="font-size: 16px; margin-bottom: 0; margin-top: 20px; text-align: center;">
							<?php esc_html_e( 'Connect your site to Google Search Console to receive insights on how content is being discovered. Identify areas for improvement and drive traffic to your website.', 'all-in-one-seo-pack' ); ?>
						</p>

						<div style="width: 475px; max-width: 96%; margin-top: 20px; margin-left: auto; margin-right: auto;">
							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 3px;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

								<span style="display: inline-block; vertical-align: middle; line-height: 20px; max-width: 185px;"><?php esc_html_e( 'Search traffic insights', 'all-in-one-seo-pack' ); ?></span>
							</div>

							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="margin-right: 3px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

								<span style="display: inline-block; vertical-align: middle; line-height: 20px; max-width: 185px;"><?php esc_html_e( 'Track page rankings', 'all-in-one-seo-pack' ); ?></span>
							</div>

							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="margin-right: 3px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

								<span style="display: inline-block; vertical-align: middle; line-height: 20px; max-width: 185px;"><?php esc_html_e( 'Track keyword rankings', 'all-in-one-seo-pack' ); ?></span>
							</div>

							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="margin-right: 3px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

								<span style="display: inline-block; vertical-align: middle; line-height: 20px; max-width: 185px;"><?php esc_html_e( 'Speed tests for individual pages/posts', 'all-in-one-seo-pack' ); ?></span>
							</div>
						</div>

						<div style="margin-top: 20px; text-align: center;">
							<a
									href="<?php echo esc_attr( $upsell['search-statistics']['cta']['url'] ) ?>"
									style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #00aa63; color: #ffffff; padding: 8px 20px;"
							>
								<?php echo $upsell['search-statistics']['cta']['text']; ?>
							</a>
						</div>
					</div>
				<?php } ?>

				<?php if ( empty( $upsell['search-statistics'] ) && ! empty( $statisticsReport['cta'] ) ) { ?>
					<div style="margin-top: 20px; margin-bottom: 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>

					<div style="text-align: center;">
						<a
								href="<?php echo esc_attr( $statisticsReport['cta']['url'] ) ?>"
								style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
						>
							<?php echo $statisticsReport['cta']['text']; ?>
						</a>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php if ( ! empty( $posts ) ) { ?>
			<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
				<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
					<img
							style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
							width="35"
							height="35"
							src="https://static.aioseo.io/report/ste/icon-summary.png"
							alt=""
					/>

					<h2 style="font-size: 20px; font-weight: 700; line-height: 24px; margin-bottom: 0; margin-top: 0; vertical-align: middle; display: inline-block;"><?php esc_html_e( 'Content Summary', 'all-in-one-seo-pack' ); ?></h2>
				</div>

				<div style="padding: 20px;">
					<?php if ( ! empty( $posts['publish']['items'] ) ) { ?>
						<div>
							<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
								<tbody>
								<tr>
									<td style="padding: 0; word-break: break-word;">
										<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
											<?php esc_html_e( 'Most Recent Published', 'all-in-one-seo-pack' ); ?>
										</p>
									</td>

									<td style="text-align: right; word-break: break-word;">
										<a
												href="<?php echo esc_attr( $posts['publish']['url'] ) ?>"
												style="color: #005ae0; font-weight: 700; text-decoration: underline;"
										>
											<?php esc_html_e( 'View All', 'all-in-one-seo-pack' ); ?>
										</a>
									</td>
								</tr>
								</tbody>
							</table>

							<div style="margin-top: 16px; overflow-x: auto;">
								<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
									<thead>
									<tr>
										<th style="background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; width: 210px; line-height: 1;">
											<?php esc_html_e( 'Post', 'all-in-one-seo-pack' ); ?>
										</th>

										<th style="text-align: center; background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1; width: <?php echo $posts['publish']['show_tru_seo'] ? '115px' : '0' ?>">
											<?php if ( $posts['publish']['show_tru_seo'] ) { ?>
												TruSEO
											<?php } ?>
										</th>

										<th style="background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1; width: <?php echo $posts['publish']['show_stats'] ? '135px' : '0' ?>">
											<?php if ( $posts['publish']['show_stats'] ) { ?>
												<?php esc_html_e( 'Stats', 'all-in-one-seo-pack' ); ?>
											<?php } ?>
										</th>
									</tr>
									</thead>

									<tbody>
									<?php foreach ( $posts['publish']['items'] as $i => $item ) { ?>
										<?php if ( $i > 0 ) { ?>
											<tr>
												<td
														colspan="3"
														style="padding: 0; word-break: break-word;"
												>
													<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
												</td>
											</tr>
										<?php } ?>
										<tr>
											<td style="padding: 12px; word-break: break-word;">
												<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
													<thead>
													<tr>
														<th style="width: 35%; padding: 0; line-height: 1;"></th>
														<th style="width: 65%; padding: 0; line-height: 1;"></th>
													</tr>
													</thead>

													<tbody>
													<tr>
														<td style="padding: 0; word-break: break-word;">
															<div style="width: 100%; height: 65px; position: relative; overflow: hidden;">
																<a
																		style="color: #005ae0; font-weight: normal; text-decoration: underline;"
																		href="<?php echo esc_attr( $item['url'] ?: '#' ) ?>"
																>
																	<img
																			src="<?php echo esc_attr( $item['image_url'] ); ?>"
																			alt="<?php echo esc_attr( aioseo()->helpers->stripEmoji( $item['title'] ) ); ?>"
																			style="box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; position: absolute; width: 100%; height: 100%; top: 0; right: 0; bottom: 0; left: 0; object-position: center; object-fit: cover; border-width: 1px; border-style: solid; border-color: #e5e5e5;"
																	/>
																</a>
															</div>
														</td>

														<td style="padding: 0; word-break: break-word;">
															<div style="padding: 6px; display: inline-block; vertical-align: middle; font-size: 14px;">
																<a
																		style="color: #141b38; font-weight: normal; text-decoration: none;"
																		href="<?php echo esc_attr( $item['url'] ?: '#' ) ?>"
																>
																	<?php echo $item['title']; ?>
																</a>
															</div>
														</td>
													</tr>
													</tbody>
												</table>
											</td>

											<td style="padding: 0; word-break: break-word;">
												<?php if ( ! empty( $item['tru_seo'] ) ) { ?>
													<div style="padding: 12px;">
														<div style="width: 45px; padding: 6px; margin-left: auto; margin-right: auto; font-size: 12px; text-align: center; line-height: 1; border-radius: 4px; border-width: 1px; border-style: solid; <?php echo "color: {$item['tru_seo']['color']}"; ?>">
															<?php echo $item['tru_seo']['text']; ?>
														</div>
													</div>
												<?php } ?>
											</td>

											<td style="padding: 0; word-break: break-word;">
												<?php if ( ! empty( $item['stats'] ) ) { ?>
													<div style="padding: 12px; font-size: 14px;">
														<?php foreach ( $item['stats'] as $k => $stat ) { ?>
															<div style="line-height: 1;<?php echo $k > 0 ? ' margin-top: 6px;' : '' ?>">
																<img
																		style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
																		width="19"
																		height="19"
																		src="<?php echo esc_attr( 'https://static.aioseo.io/report/ste/icon-' . $stat['icon'] . '.png' ) ?>"
																		alt=""
																/>

																<span style="vertical-align: middle;"><?php echo $stat['label']; ?>:</span>

																<span style="vertical-align: middle; font-weight: 700;"><?php echo $stat['value']; ?></span>
															</div>
														<?php } ?>
													</div>
												<?php } ?>
											</td>
										</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>

					<?php if ( ! empty( $posts['optimize']['items'] ) ) { ?>
						<?php if ( ! empty( $posts['publish']['items'] ) ) { ?>
							<div style="margin-top: 30px; margin-bottom: 30px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
						<?php } ?>
						<div>
							<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
								<tbody>
								<tr>
									<td style="padding: 0; word-break: break-word;">
										<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
											<?php esc_html_e( 'Posts to Optimize', 'all-in-one-seo-pack' ); ?>
										</p>
									</td>

									<td style="text-align: right; word-break: break-word;">
										<a
												href="<?php echo esc_attr( $posts['optimize']['url'] ) ?>"
												style="color: #005ae0; font-weight: 700; text-decoration: underline;"
										>
											<?php esc_html_e( 'View All', 'all-in-one-seo-pack' ); ?>
										</a>
									</td>
								</tr>
								</tbody>
							</table>

							<div style="margin-top: 16px; overflow-x: auto;">
								<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
									<thead>
									<tr>
										<th style="width: 319px; background-color: #f0f6ff; padding: 0; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
											<div style="padding: 12px;"><?php esc_html_e( 'Post', 'all-in-one-seo-pack' ); ?></div>
										</th>

										<th style="width: <?php echo $posts['optimize']['show_tru_seo'] ? '159px' : '0' ?>; text-align: center; background-color: #f0f6ff; padding: 0; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
											<?php if ( $posts['optimize']['show_tru_seo'] ) { ?>
												<div style="padding: 12px;">TruSEO</div>
											<?php } ?>
										</th>

										<th style="width: 159px; text-align: center; background-color: #f0f6ff; padding: 0; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
											<div style="padding: 12px;"><?php esc_html_e( 'Content Drop', 'all-in-one-seo-pack' ); ?></div>
										</th>
									</tr>
									</thead>

									<tbody>
									<?php foreach ( $posts['optimize']['items'] as $item ) { ?>
										<tr>
											<td
													colspan="3"
													style="padding-bottom: 8px; padding-top: 8px; word-break: break-word;"
											></td>
										</tr>

										<tr style="border-width: 1px; border-style: solid; border-color: #e5e5e5;">
											<td
													colspan="3"
													style="padding: 12px; word-break: break-word;"
											>
												<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
													<thead>
													<tr>
														<th style="width: 311px; padding: 0; line-height: 1;"></th>
														<th style="width: <?php echo $posts['optimize']['show_tru_seo'] ? '151px' : '0' ?>; padding: 0; line-height: 1;"></th>
														<th style="width: 151px; padding: 0; line-height: 1;"></th>
													</tr>
													</thead>

													<tbody>
													<tr>
														<td style="padding: 0; background-color: #f3f4f5; word-break: break-word;">
															<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
																<thead>
																<tr>
																	<th style="width: 35%; padding: 0; line-height: 1;"></th>
																	<th style="width: 65%; padding: 0; line-height: 1;"></th>
																</tr>
																</thead>

																<tbody>
																<tr>
																	<td style="padding: 0; word-break: break-word;">
																		<div style="width: 100%; height: 65px; position: relative; overflow: hidden;">
																			<a style="color: #005ae0; font-weight: normal; text-decoration: underline;"<?php echo ! empty( $item['url'] ) ? ' href="' . esc_attr( $item['url'] ) . '"' : '' ?>>
																				<img
																						src="<?php echo esc_attr( $item['image_url'] ); ?>"
																						alt="<?php echo esc_attr( aioseo()->helpers->stripEmoji( $item['title'] ) ); ?>"
																						style="box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; position: absolute; width: 100%; height: 100%; top: 0; right: 0; bottom: 0; left: 0; object-position: center; object-fit: cover; border-width: 1px; border-style: solid; border-color: #e5e5e5;"
																				/>
																			</a>
																		</div>
																	</td>

																	<td style="padding: 0; word-break: break-word;">
																		<div style="padding: 6px; display: inline-block; vertical-align: middle; font-size: 14px;">
																			<a style="color: #141b38; font-weight: normal; text-decoration: none;"<?php echo ! empty( $item['url'] ) ? ' href="' . esc_attr( $item['url'] ) . '"' : '' ?>>
																				<?php echo $item['title']; ?>
																			</a>
																		</div>
																	</td>
																</tr>
																</tbody>
															</table>
														</td>

														<td style="padding: 0; background-color: #f3f4f5; word-break: break-word;">
															<?php if ( ! empty( $item['tru_seo'] ) ) { ?>
																<div style="width: 45px; padding: 6px; margin-left: auto; margin-right: auto; font-size: 12px; text-align: center; line-height: 1; border-radius: 4px; border-width: 1px; border-style: solid; <?php echo "color: {$item['tru_seo']['color']}"; ?>">
																	<?php echo $item['tru_seo']['text']; ?>
																</div>
															<?php } ?>
														</td>

														<td style="padding: 0; background-color: #f3f4f5; word-break: break-word;">
															<div style="width: 50px; padding: 6px; margin-left: auto; margin-right: auto; font-size: 0; text-align: center; line-height: 1; border-radius: 4px; border-width: 1px; border-style: solid; <?php echo "color: {$item['decay_percent']['color']}"; ?>">
																<?php if ( '#00aa63' === $item['decay_percent']['color'] ) { ?>
																	<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>
																<?php } ?>

																<?php if ( '#df2a4a' === $item['decay_percent']['color'] ) { ?>
																	<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>
																<?php } ?>

																<span style="display: inline-block; vertical-align: middle; font-size: 12px;"><?php echo $item['decay_percent']['text']; ?></span>
															</div>
														</td>
													</tr>

													<?php if ( ! empty( $item['issues']['items'] ) ) { ?>
														<tr>
															<td
																	colspan="2"
																	style="padding: 10px 0 0; word-break: break-word;"
															>
																<div style="font-size: 14px; font-weight: 700;"><?php esc_html_e( 'Issues', 'all-in-one-seo-pack' ); ?></div>
															</td>

															<td style="padding: 10px 0 0; text-align: right; word-break: break-word;">
																<?php if ( ! empty( $item['issues']['url'] ) ) { ?>
																	<a
																			href="<?php echo esc_attr( $item['issues']['url'] ) ?>"
																			style="color: #005ae0; font-weight: normal; text-decoration: underline; font-size: 12px;"
																	>
																		<?php esc_html_e( 'View All', 'all-in-one-seo-pack' ); ?>
																	</a>
																<?php } ?>
															</td>
														</tr>

														<tr>
															<td
																	colspan="3"
																	style="padding: 0; word-break: break-word;"
															>
																<?php foreach ( $item['issues']['items'] as $issue ) { ?>
																	<div style="padding-top: 0; padding-left: 0; padding-right: 0; pt-6 font-size: 14px;">
																		<img
																				style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 3px;"
																				width="15"
																				height="15"
																				src="https://static.aioseo.io/report/ste/icon-remove.png"
																				alt="x"
																		/>

																		<span style="width: 94%; vertical-align: middle; display: inline-block;"><?php echo $issue['text'] ?></span>
																	</div>
																<?php } ?>
															</td>
														</tr>
													<?php } ?>
													</tbody>
												</table>
											</td>
										</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>

					<?php if ( empty( $posts['publish']['items'] ) && empty( $posts['optimize']['items'] ) ) { ?>
						<div style="font-size: 16px; font-weight: 400; text-align: center;">
							<?php echo esc_html__( 'It seems there is no content yet to be displayed.', 'all-in-one-seo-pack' ) ?>
						</div>
					<?php } ?>

					<div style="margin-top: 20px; text-align: center;">
						<a
								href="<?php echo esc_attr( $posts['cta']['url'] ); ?>"
								style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
						>
							<?php echo $posts['cta']['text']; ?>
						</a>
					</div>
				</div>
			</div>
		<?php } ?>

		<?php if ( ! empty( $statisticsReport['milestones'] ) ) { ?>
			<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
				<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
					<img
							style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
							width="35"
							height="35"
							src="https://static.aioseo.io/report/ste/icon-flag.png"
							alt=""
					/>

					<h2 style="font-size: 20px; font-weight: 700; line-height: 24px; margin-bottom: 0; margin-top: 0; vertical-align: middle; display: inline-block;"><?php esc_html_e( 'SEO Milestones', 'all-in-one-seo-pack' ); ?></h2>
				</div>

				<div style="padding: 10px; overflow-x: auto;">
					<table style="min-width: 400px; width: 100%;">
						<tbody>
						<?php for ( $i = 0; $i < count( $statisticsReport['milestones'] ) / 2; $i++ ) { ?>
							<tr>
								<?php for ( $j = $i; $j < ( 2 + $i ); $j++ ) { ?>
									<?php $milestone = $statisticsReport['milestones'][ $i + $j ] ?? null ?>
									<?php if ( ! $milestone ) { ?>
										<?php continue; ?>
									<?php } ?>
									<td style="padding: 16px; word-break: break-word; vertical-align: top; border: 10px solid #ffff; text-align: center; border-radius: 4px; background-color: <?php echo $milestone['background'] ?>; color: <?php echo $milestone['color'] ?>; width: <?php echo max( 50, 100 / count( $statisticsReport['milestones'] ) ), '%' ?>">
										<img
												style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-left: auto; margin-right: auto;"
												width="35"
												height="35"
												src="<?php echo 'https://static.aioseo.io/report/ste/', $milestone['icon'], '.png' ?>"
												alt=""
										/>

										<p style="font-size: 16px; margin-bottom: 0; margin-top: 12px;">
											<?php echo $milestone['message']; ?>
										</p>
									</td>
								<?php } ?>
							</tr>
						<?php } ?>
						</tbody>
					</table>

					<?php if ( ! empty( $statisticsReport['cta'] ) ) { ?>
						<div style="margin: 10px 10px 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>

						<div style="padding-bottom: 10px; text-align: center;">
							<a
									href="<?php echo esc_attr( $statisticsReport['cta']['url'] ) ?>"
									style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
							>
								<?php echo $statisticsReport['cta']['text']; ?>
							</a>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<?php if ( ! empty( $resources['posts'] ) ) { ?>
			<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
				<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
					<img
							style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
							width="35"
							height="35"
							src="https://static.aioseo.io/report/ste/icon-star.png"
							alt=""
					/>

					<h2 style="font-size: 20px; font-weight: 700; line-height: 24px; margin-bottom: 0; margin-top: 0; vertical-align: middle; display: inline-block;"><?php esc_html_e( "What's New", 'all-in-one-seo-pack' ); ?></h2>
				</div>

				<div style="padding: 20px;">
					<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
						<tbody>
						<?php foreach ( $resources['posts'] as $k => $post ) { ?>
							<?php if ( $k > 0 ) { ?>
								<tr>
									<td
											colspan="3"
											style="padding-bottom: 8px; padding-top: 8px; word-break: break-word;"
									>
										<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
									</td>
								</tr>
							<?php } ?>

							<tr>
								<td style="padding: 0; word-break: break-word;">
									<div style="width: 147px; height: 82px; margin-top: 6px; margin-bottom: 6px; margin-right: 6px; display: inline-block; vertical-align: top; position: relative; overflow: hidden;">
										<a
												style="color: #005ae0; font-weight: normal; text-decoration: underline;"
												href="<?php echo esc_attr( $post['url'] ?: '#' ); ?>"
										>
											<img
													src="<?php echo esc_attr( $post['image']['url'] ) ?>"
													alt="<?php echo esc_attr( aioseo()->helpers->stripEmoji( $post['title'] ) ) ?>"
													style="box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; position: absolute; width: 100%; height: 100%; top: 0; right: 0; bottom: 0; left: 0; object-position: center; object-fit: cover; border-width: 1px; border-style: solid; border-color: #e5e5e5;"
											/>
										</a>
									</div>

									<div style="max-width: 448px; padding-bottom: 3px; padding-top: 3px; display: inline-block; vertical-align: top;">
										<a
												style="color: #141b38; font-weight: 700; text-decoration: none; font-size: 16px;"
												href="<?php echo esc_attr( $post['url'] ?: '#' ); ?>"
										>
											<?php echo $post['title']; ?>
										</a>

										<div style="margin-top: 6px; font-size: 14px;">
											<span style="display: block;"><?php echo $post['content'] ?? ''; ?></span>

											<a
													style="color: #005ae0; font-weight: normal; text-decoration: underline;"
													href="<?php echo esc_attr( $post['url'] ?: '#' ); ?>"
											>
												<?php esc_html_e( 'Continue Reading', 'all-in-one-seo-pack' ) ?>
											</a>
										</div>
									</div>
								</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>

					<div style="padding-top: 8px;">
						<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
					</div>

					<div style="padding-top: 20px; text-align: center;">
						<a
								href="<?php echo esc_attr( $resources['cta']['url'] ); ?>"
								style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
						>
							<?php echo $resources['cta']['text']; ?>
						</a>
					</div>
				</div>
			</div>
		<?php } ?>

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
					printf( esc_html__( 'This email was auto-generated and sent from %1$s.', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME )
					?>
				</p>

				<p style="font-size: 14px; margin-bottom: 0; margin-top: 0; text-align: center; color: #434960;">
					<?php
					printf(
						// Translators: 1 - Opening link tag, 2 - Closing link tag.
						esc_html__( 'Learn how to %1$sdisable%2$s it.', 'all-in-one-seo-pack' ),
						'<a href="' . ( $links['disable'] ?? '#' ) . '" style="color: #141b38; font-weight: normal; text-decoration: underline;">', '</a>'
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
									href="<?php echo esc_attr( $links['marketing-site'] ?? '#' ) ?>"
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
									href="<?php echo esc_attr( $links['facebook'] ?? '#' ) ?>"
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
									href="<?php echo esc_attr( $links['linkedin'] ?? '#' ) ?>"
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
									href="<?php echo esc_attr( $links['youtube'] ?? '#' ) ?>"
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
									href="<?php echo esc_attr( $links['twitter'] ?? '#' ) ?>"
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