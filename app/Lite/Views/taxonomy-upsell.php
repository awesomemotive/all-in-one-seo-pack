<?php
// phpcs:disable Generic.Files.LineLength.MaxExceeded

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
	#poststuff.aioseo-taxonomy-upsell {
		min-width: auto;
		overflow: hidden;
	}
</style>
<div id="poststuff" class="aioseo-taxonomy-upsell" style="margin-top:30px;max-width: 800px;">
	<div id="advanced-sortables" class="meta-box-sortables">
		<div id="aioseo-tabbed" class="postbox ">
			<h2 class="hndle">
				<span><?php esc_html_e( 'AIOSEO Settings', 'all-in-one-seo-pack' ); ?></span>
			</h2>
			<div>
				<div class="aioseo-app aioseo-post-settings">
					<div class="aioseo-blur">
						<div class="aioseo-tabs internal">
							<div class="tabs-scroller">
								<div class="var-tabs var--box var-tabs--item-horizontal var-tabs--layout-horizontal-padding">
									<div class="var-tabs__tab-wrap var-tabs--layout-horizontal-scrollable var-tabs--layout-horizontal">
										<div class="var-tab var--box var-tab--active var-tab--horizontal">
											<span class="label">General</span>
										</div>
										<div class="var-tab var--box var-tab--inactive var-tab--horizontal">
											<span class="label">Social</span>
										</div>
										<div class="var-tab var--box var-tab--inactive var-tab--horizontal">
											<span class="label">Redirects</span>
										</div>
										<div class="var-tab var--box var-tab--inactive var-tab--horizontal">
											<span class="label">SEO Revisions</span>
										</div>
										<div class="var-tab var--box var-tab--inactive var-tab--horizontal">
											<span class="label">Advanced</span>
										</div>
									<div class="var-tabs__indicator var-tabs--layout-horizontal-indicator" style="width: 102px; transform: translateX(0px);"><div class="var-tabs__indicator-inner var-tabs--layout-horizontal-indicator-inner"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="tabs-extra"></div>
					</div>
						<div class="aioseo-tab-content aioseo-post-general">
							<div class="aioseo-settings-row mobile-radio-buttons aioseo-row ">
								<div class="aioseo-col col-xs-12 col-md-3 text-xs-left">
									<div class="settings-name">
										<div class="name"> </div>
									</div>
								</div>
								<div class="aioseo-col col-xs-12 col-md-9 text-xs-left">
									<div class="settings-content">
										<div class="aioseo-radio-toggle circle">
											<div><input id="id_previewGeneralIsMobile_0" name="previewGeneralIsMobile"
													type="radio"><label for="id_previewGeneralIsMobile_0" class="dark"><svg
														width="20" height="18" viewBox="0 0 20 18" fill="none"
														xmlns="http://www.w3.org/2000/svg" class="aioseo-desktop">
														<path fill-rule="evenodd" clip-rule="evenodd"
															d="M2.50004 0.666504H17.5C18.4167 0.666504 19.1667 1.4165 19.1667 2.33317V12.3332C19.1667 13.2498 18.4167 13.9998 17.5 13.9998H11.6667V15.6665H13.3334V17.3332H6.66671V15.6665H8.33337V13.9998H2.50004C1.58337 13.9998 0.833374 13.2498 0.833374 12.3332V2.33317C0.833374 1.4165 1.58337 0.666504 2.50004 0.666504ZM2.50004 12.3332H17.5V2.33317H2.50004V12.3332Z"
															fill="currentColor"></path>
													</svg></label></div>
											<div><input id="id_previewGeneralIsMobile_1" name="previewGeneralIsMobile"
													type="radio"><label for="id_previewGeneralIsMobile_1" class=""><svg
														width="12" height="20" viewBox="0 0 12 20" fill="none"
														xmlns="http://www.w3.org/2000/svg" class="aioseo-mobile">
														<path fill-rule="evenodd" clip-rule="evenodd"
															d="M1.72767 0.833496L10.061 0.841829C10.9777 0.841829 11.7277 1.5835 11.7277 2.50016V17.5002C11.7277 18.4168 10.9777 19.1668 10.061 19.1668H1.72767C0.811003 19.1668 0.0693359 18.4168 0.0693359 17.5002V2.50016C0.0693359 1.5835 0.811003 0.833496 1.72767 0.833496ZM1.72763 15.8335H10.061V4.16683H1.72763V15.8335Z"
															fill="currentColor"></path>
													</svg>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="aioseo-settings-row snippet-preview-row aioseo-row ">
								<div class="aioseo-col col-xs-12 col-md-3 text-xs-left">
									<div class="settings-name">
										<div class="name"> Snippet Preview </div>
									</div>
								</div>
								<div class="aioseo-col col-xs-12 col-md-9 text-xs-left">
									<div class="settings-content">
										<div class="aioseo-google-search-preview">
											<div class="domain"> https://aioseo.com/category/uncategorized/ </div>
											<div class="site-title">Taxonomy Title | aioseo.com</div>
											<div class="meta-description">Sample taxonomy description</div>
										</div>
									</div>
								</div>
							</div>
							<div class="aioseo-settings-row snippet-title-row aioseo-row ">
								<div class="aioseo-col col-xs-12 col-md-3 text-xs-left">
									<div class="settings-name">
										<div class="name"> Category Title </div>
									</div>
								</div>
								<div class="aioseo-col col-xs-12 col-md-9 text-xs-left">
									<div class="settings-content">
										<div class="aioseo-html-tags-editor">

											<div>
												<div class="aioseo-description tags-description"> Click on the tags below to
													insert variables into your title. </div>
												<div class="add-tags">
													<div class="aioseo-add-template-tag"><svg viewBox="0 0 10 11"
															fill="none" xmlns="http://www.w3.org/2000/svg"
															class="aioseo-plus">
															<path
																d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																fill="currentColor"></path>
														</svg> Category Title </div>
													<div class="aioseo-add-template-tag"><svg viewBox="0 0 10 11"
															fill="none" xmlns="http://www.w3.org/2000/svg"
															class="aioseo-plus">
															<path
																d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																fill="currentColor"></path>
														</svg> Separator </div>
													<div class="aioseo-add-template-tag"><svg viewBox="0 0 10 11"
															fill="none" xmlns="http://www.w3.org/2000/svg"
															class="aioseo-plus">
															<path
																d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																fill="currentColor"></path>
														</svg> Site Title </div><a href="#" class="aioseo-view-all-tags">
														View all tags&nbsp;→ </a>
												</div>
											</div>
											<div class="aioseo-editor">
												<div class="ql-toolbar ql-snow"><span class="ql-formats"></span></div>
												<div class="aioseo-editor-single ql-container ql-snow">
													<div class="ql-editor" data-gramm="false" contenteditable="true"></div>
													<div class="ql-clipboard" contenteditable="true" tabindex="-1"></div>
													<div class="ql-tooltip ql-hidden"><a class="ql-preview"
															rel="noopener noreferrer" target="_blank"
															href="about:blank"></a><input type="text" data-formula="e=mc^2"
															data-link="https://quilljs.com" data-video="Embed URL"><a
															class="ql-action"></a><a class="ql-remove"></a></div>
													<div class="ql-mention-list-container"
														style="display: none; position: absolute;">
														<div class="aioseo-tag-custom">
															<div data-v-3f0a80a7="" class="aioseo-input">

																<input data-v-3f0a80a7="" type="text"
																	placeholder="Enter a custom field name..."
																	spellcheck="true" class="small">
															</div>
														</div>
														<div class="aioseo-tag-search">
															<div data-v-3f0a80a7="" class="aioseo-input">
																<div data-v-3f0a80a7="" class="prepend-icon medium"><svg
																		data-v-3f0a80a7="" viewBox="0 0 15 16"
																		xmlns="http://www.w3.org/2000/svg"
																		class="aioseo-search">
																		<path
																			d="M14.8828 14.6152L11.3379 11.0703C11.25 11.0117 11.1621 10.9531 11.0742 10.9531H10.6934C11.6016 9.89844 12.1875 8.49219 12.1875 6.96875C12.1875 3.62891 9.43359 0.875 6.09375 0.875C2.72461 0.875 0 3.62891 0 6.96875C0 10.3379 2.72461 13.0625 6.09375 13.0625C7.61719 13.0625 8.99414 12.5059 10.0781 11.5977V11.9785C10.0781 12.0664 10.1074 12.1543 10.166 12.2422L13.7109 15.7871C13.8574 15.9336 14.0918 15.9336 14.209 15.7871L14.8828 15.1133C15.0293 14.9961 15.0293 14.7617 14.8828 14.6152ZM6.09375 11.6562C3.48633 11.6562 1.40625 9.57617 1.40625 6.96875C1.40625 4.39062 3.48633 2.28125 6.09375 2.28125C8.67188 2.28125 10.7812 4.39062 10.7812 6.96875C10.7812 9.57617 8.67188 11.6562 6.09375 11.6562Z"
																			fill="currentColor"></path>
																	</svg></div>
																<input data-v-3f0a80a7="" type="text"
																	placeholder="Search for an item..." spellcheck="true"
																	class="medium prepend">
															</div>
														</div>
														<ul class="ql-mention-list"></ul>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Category Description</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Category Description </div>
															<div class="aioseo-tag-description"> Current or first category
																description. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Category Title</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Category Title </div>
															<div class="aioseo-tag-description"> Current or first category
																title. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Date</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Date </div>
															<div class="aioseo-tag-description"> The current date,
																localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Day</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Day </div>
															<div class="aioseo-tag-description"> The current day of the
																month, localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Month</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Month </div>
															<div class="aioseo-tag-description"> The current month,
																localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Year</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Year </div>
															<div class="aioseo-tag-description"> The current year,
																localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Custom Field</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Custom Field </div>
															<div class="aioseo-tag-description"> A custom field from the
																current page/post. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Permalink</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Permalink </div>
															<div class="aioseo-tag-description"> The permalink for the
																current page/post. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Separator</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Separator </div>
															<div class="aioseo-tag-description"> The separator defined in
																the search appearance settings. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Site Title</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Site Title </div>
															<div class="aioseo-tag-description"> Your site title. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Tagline</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Tagline </div>
															<div class="aioseo-tag-description"> The tagline for your site,
																set in the general settings. </div>
														</div>
													</div>
												</div>
												<div style="display: none;">
													<div data-v-3f0a80a7="" class="aioseo-input">
														<div data-v-3f0a80a7="" class="prepend-icon medium"><svg
																data-v-3f0a80a7="" viewBox="0 0 15 16"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-search">
																<path
																	d="M14.8828 14.6152L11.3379 11.0703C11.25 11.0117 11.1621 10.9531 11.0742 10.9531H10.6934C11.6016 9.89844 12.1875 8.49219 12.1875 6.96875C12.1875 3.62891 9.43359 0.875 6.09375 0.875C2.72461 0.875 0 3.62891 0 6.96875C0 10.3379 2.72461 13.0625 6.09375 13.0625C7.61719 13.0625 8.99414 12.5059 10.0781 11.5977V11.9785C10.0781 12.0664 10.1074 12.1543 10.166 12.2422L13.7109 15.7871C13.8574 15.9336 14.0918 15.9336 14.209 15.7871L14.8828 15.1133C15.0293 14.9961 15.0293 14.7617 14.8828 14.6152ZM6.09375 11.6562C3.48633 11.6562 1.40625 9.57617 1.40625 6.96875C1.40625 4.39062 3.48633 2.28125 6.09375 2.28125C8.67188 2.28125 10.7812 4.39062 10.7812 6.96875C10.7812 9.57617 8.67188 11.6562 6.09375 11.6562Z"
																	fill="currentColor"></path>
															</svg></div>
														<input data-v-3f0a80a7="" type="text"
															placeholder="Search for an item..." spellcheck="true"
															class="medium prepend">


													</div>
												</div>
												<div style="display: none;">
													<div data-v-3f0a80a7="" class="aioseo-input">

														<input data-v-3f0a80a7="" type="text"
															placeholder="Enter a custom field name..." spellcheck="true"
															class="small">


													</div>
												</div>
											</div>
										</div>
										<div class="max-recommended-count"><strong>32</strong> out of <strong>60</strong>
											max recommended characters.</div>
									</div>
								</div>
							</div>
							<div class="aioseo-settings-row snippet-description-row aioseo-row ">
								<div class="aioseo-col col-xs-12 col-md-3 text-xs-left">
									<div class="settings-name">
										<div class="name"> Meta Description </div>
										<!---->
									</div>
								</div>
								<div class="aioseo-col col-xs-12 col-md-9 text-xs-left">
									<div class="settings-content">
										<div class="aioseo-html-tags-editor">
											<!---->
											<div>
												<div class="aioseo-description tags-description"> Click on the tags below to
													insert variables into your meta description. </div>
												<div class="add-tags">
													<div class="aioseo-add-template-tag"><svg viewBox="0 0 10 11"
															fill="none" xmlns="http://www.w3.org/2000/svg"
															class="aioseo-plus">
															<path
																d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																fill="currentColor"></path>
														</svg> Category Title </div>
													<div class="aioseo-add-template-tag"><svg viewBox="0 0 10 11"
															fill="none" xmlns="http://www.w3.org/2000/svg"
															class="aioseo-plus">
															<path
																d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																fill="currentColor"></path>
														</svg> Separator </div>
													<div class="aioseo-add-template-tag"><svg viewBox="0 0 10 11"
															fill="none" xmlns="http://www.w3.org/2000/svg"
															class="aioseo-plus">
															<path
																d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																fill="currentColor"></path>
														</svg> Category Description </div><a href="#"
														class="aioseo-view-all-tags"> View all tags&nbsp;→ </a>
												</div>
											</div>
											<div class="aioseo-editor">
												<div class="ql-toolbar ql-snow"><span class="ql-formats"></span></div>
												<div class="aioseo-editor-description ql-container ql-snow">
													<div class="ql-editor" data-gramm="false" contenteditable="true"></div>
													<div class="ql-clipboard" contenteditable="true" tabindex="-1"></div>
													<div class="ql-tooltip ql-hidden"><a class="ql-preview"
															rel="noopener noreferrer" target="_blank"
															href="about:blank"></a><input type="text" data-formula="e=mc^2"
															data-link="https://quilljs.com" data-video="Embed URL"><a
															class="ql-action"></a><a class="ql-remove"></a></div>
													<div class="ql-mention-list-container"
														style="display: none; position: absolute;">
														<div class="aioseo-tag-custom">
															<div data-v-3f0a80a7="" class="aioseo-input">

																<input data-v-3f0a80a7="" type="text"
																	placeholder="Enter a custom field name..."
																	spellcheck="true" class="small">
															</div>
														</div>
														<div class="aioseo-tag-search">
															<div data-v-3f0a80a7="" class="aioseo-input">
																<div data-v-3f0a80a7="" class="prepend-icon medium"><svg
																		data-v-3f0a80a7="" viewBox="0 0 15 16"
																		xmlns="http://www.w3.org/2000/svg"
																		class="aioseo-search">
																		<path
																			d="M14.8828 14.6152L11.3379 11.0703C11.25 11.0117 11.1621 10.9531 11.0742 10.9531H10.6934C11.6016 9.89844 12.1875 8.49219 12.1875 6.96875C12.1875 3.62891 9.43359 0.875 6.09375 0.875C2.72461 0.875 0 3.62891 0 6.96875C0 10.3379 2.72461 13.0625 6.09375 13.0625C7.61719 13.0625 8.99414 12.5059 10.0781 11.5977V11.9785C10.0781 12.0664 10.1074 12.1543 10.166 12.2422L13.7109 15.7871C13.8574 15.9336 14.0918 15.9336 14.209 15.7871L14.8828 15.1133C15.0293 14.9961 15.0293 14.7617 14.8828 14.6152ZM6.09375 11.6562C3.48633 11.6562 1.40625 9.57617 1.40625 6.96875C1.40625 4.39062 3.48633 2.28125 6.09375 2.28125C8.67188 2.28125 10.7812 4.39062 10.7812 6.96875C10.7812 9.57617 8.67188 11.6562 6.09375 11.6562Z"
																			fill="currentColor"></path>
																	</svg></div>
																<input data-v-3f0a80a7="" type="text"
																	placeholder="Search for an item..." spellcheck="true"
																	class="medium prepend">
															</div>
														</div>
														<ul class="ql-mention-list"></ul>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Category Description</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Category Description </div>
															<div class="aioseo-tag-description"> Current or first category
																description. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Category Title</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Category Title </div>
															<div class="aioseo-tag-description"> Current or first category
																title. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Date</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Date </div>
															<div class="aioseo-tag-description"> The current date,
																localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Day</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Day </div>
															<div class="aioseo-tag-description"> The current day of the
																month, localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Month</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Month </div>
															<div class="aioseo-tag-description"> The current month,
																localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Current Year</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Current Year </div>
															<div class="aioseo-tag-description"> The current year,
																localized. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Custom Field</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Custom Field </div>
															<div class="aioseo-tag-description"> A custom field from the
																current page/post. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Permalink</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Permalink </div>
															<div class="aioseo-tag-description"> The permalink for the
																current page/post. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Separator</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Separator </div>
															<div class="aioseo-tag-description"> The separator defined in
																the search appearance settings. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Site Title</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Site Title </div>
															<div class="aioseo-tag-description"> Your site title. </div>
														</div>
													</div>
												</div>
												<div style="display: none;"><span class="aioseo-tag"><span
															class="tag-name">Tagline</span>
														<span class="tag-toggle"><svg viewBox="0 0 24 24" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-caret">
																<path
																	d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z"
																	fill="currentColor"></path>
															</svg></span>
													</span></div>
												<div style="display: none;">
													<div class="aioseo-tag-item">
														<div><svg viewBox="0 0 10 11" fill="none"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-plus">
																<path
																	d="M6 0.00115967H4V4.00116H0V6.00116H4V10.0012H6V6.00116H10V4.00116H6V0.00115967Z"
																	fill="currentColor"></path>
															</svg></div>
														<div>
															<div class="aioseo-tag-title"> Tagline </div>
															<div class="aioseo-tag-description"> The tagline for your site,
																set in the general settings. </div>
														</div>
													</div>
												</div>
												<div style="display: none;">
													<div data-v-3f0a80a7="" class="aioseo-input">
														<div data-v-3f0a80a7="" class="prepend-icon medium"><svg
																data-v-3f0a80a7="" viewBox="0 0 15 16"
																xmlns="http://www.w3.org/2000/svg" class="aioseo-search">
																<path
																	d="M14.8828 14.6152L11.3379 11.0703C11.25 11.0117 11.1621 10.9531 11.0742 10.9531H10.6934C11.6016 9.89844 12.1875 8.49219 12.1875 6.96875C12.1875 3.62891 9.43359 0.875 6.09375 0.875C2.72461 0.875 0 3.62891 0 6.96875C0 10.3379 2.72461 13.0625 6.09375 13.0625C7.61719 13.0625 8.99414 12.5059 10.0781 11.5977V11.9785C10.0781 12.0664 10.1074 12.1543 10.166 12.2422L13.7109 15.7871C13.8574 15.9336 14.0918 15.9336 14.209 15.7871L14.8828 15.1133C15.0293 14.9961 15.0293 14.7617 14.8828 14.6152ZM6.09375 11.6562C3.48633 11.6562 1.40625 9.57617 1.40625 6.96875C1.40625 4.39062 3.48633 2.28125 6.09375 2.28125C8.67188 2.28125 10.7812 4.39062 10.7812 6.96875C10.7812 9.57617 8.67188 11.6562 6.09375 11.6562Z"
																	fill="currentColor"></path>
															</svg></div>
														<input data-v-3f0a80a7="" type="text"
															placeholder="Search for an item..." spellcheck="true"
															class="medium prepend">
													</div>
												</div>
												<div style="display: none;">
													<div data-v-3f0a80a7="" class="aioseo-input">

														<input data-v-3f0a80a7="" type="text"
															placeholder="Enter a custom field name..." spellcheck="true"
															class="small">
													</div>
												</div>
											</div>
										</div>
										<div class="max-recommended-count"><strong>27</strong> out of <strong>160</strong>
											max recommended characters.</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="aioseo-cta floating" style="max-width: 630px;">
						<div class="aioseo-cta-background">
							<div class="type-1">
								<div class="header-text"><?php esc_html_e( 'Custom Taxonomies are a PRO Feature', 'all-in-one-seo-pack' ); ?></div>
								<div class="description"><?php esc_html_e( 'Set custom SEO meta, social meta and more for individual terms.', 'all-in-one-seo-pack' ); ?></div>
								<div class="feature-list aioseo-row ">
									<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
										<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
										</svg> SEO Title/Description
									</div>
									<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
										<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
										</svg> Social Meta
									</div>
									<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
										<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
										</svg> SEO Revisions
									</div>
									<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
										<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
										</svg> Import/Export
									</div>
								</div>
								<div class="actions">
									<a type="" to="" class="aioseo-button green" href="<?php echo esc_attr( aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'lite-upgrade/', 'taxonomies-upsell', 'features=[]=taxonomies', false ) ); ?>" target="_blank"><?php esc_html_e( 'Unlock Custom Taxonomies', 'all-in-one-seo-pack' ); ?></a>
									<a href="https://aioseo.com/?utm_source=WordPress&amp;utm_campaign=liteplugin&amp;utm_medium=taxonomies-upsell&amp;features[]=taxonomies" target="_blank" class="learn-more"><?php esc_html_e( 'Learn more about all features', 'all-in-one-seo-pack' ); ?></a>
								</div>


								<div class="aioseo-alert yellow medium bonus-alert"> 🎁 <span>
									<strong><?php esc_html_e( 'Bonus:', 'all-in-one-seo-pack' ); ?></strong>
									<?php esc_html_e( 'You can upgrade to the Pro plan today and ', 'all-in-one-seo-pack' ); ?>
									<strong><?php esc_html_e( 'save 60% off', 'all-in-one-seo-pack' ); ?></strong>
									<?php esc_html_e( '(discount auto-applied)', 'all-in-one-seo-pack' ); ?>.</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>