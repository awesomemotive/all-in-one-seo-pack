<template>
	<core-display-info
		:label="strings.label"
		:options="displayOptions"
		:url="url"
	>

		<template #shortcodeAdvanced>
			<core-attributes-list
				:description="strings.shortcodeAttributesDescription"
				:attributes="attributes"
			/>
		</template>

		<template #phpAdvanced>
			<core-attributes-list
				:description="strings.phpArgumentsDescription"
				:attributes="attributes"
			/>
		</template>

		<template #extra>
			<svg-file />
			<p>{{ strings.page }}</p>
		</template>

		<template #extraBox="{ item }">
			<div class="new-page">
				<div class="aioseo-row">
					<div class="aioseo-col col-xs-12 text-xs-left">
						<div
							class="aioseo-description"
							v-if="item.desc"
							v-html="item.desc"
						/>
					</div>

					<div class="aioseo-col col-xs-12 col-md-7 text-xs-left">
						<base-input
							:disabled="dedicatedPageDisabled"
							v-model="pageUrl"
							@keyup="validateNewSlug"
							@blur="addSiteUrl"
							size="medium"
							:placeholder="strings.placeholder"
							@focus="showResults = true"
							:class="{
								'aioseo-error'  : error,
								'aioseo-active' : !error && pageUrl && !dedicatedPageDisabled
							}"
						>
							<template #append-icon>
								<div class="append-icon">
									<template
										v-if="!isLoading"
									>
										<svg-circle-close
											v-if="error"
										/>
										<svg-circle-check
											v-if="!error && pageUrl && !dedicatedPageDisabled"
										/>
									</template>

									<core-loader
										v-if="isLoading"
										dark
									/>
								</div>
							</template>
						</base-input>
					</div>

					<div class="aioseo-col col-xs-12 col-md-5 text-xs-left">
						<core-tooltip
							v-if="!pageUrl || buttonLocked || error || dedicatedPageDisabled ? true : false"
							type="action"
							tag="div"
						>
							<base-button
								:disabled="dedicatedPageDisabled"
								class="aioseo-html-sitemaps-disabled-button"
								size="medium"
								type="blue"
								tag="button"
							>
									<svg-external />
									{{ strings.pageButton }}
							</base-button>

							<template #tooltip>
								{{ sitemapButtonDisabled }}
							</template>
						</core-tooltip>

						<base-button
							v-if="pageUrl && !buttonLocked && !error && !dedicatedPageDisabled ? true : false"
							size="medium"
							type="blue"
							tag="a"
							:href="pageUrl"
							target="_blank"
						>
							<svg-external />
							{{ strings.pageButton }}
						</base-button>
					</div>

					<core-alert
						v-if="showResults && error || dedicatedPageDisabled"
						:type="dedicatedPageDisabled ? 'yellow' : 'red'"
						size="medium"
					>
						<span
							v-html="dedicatedPageDisabled ? strings.errorMessageDisabled : strings.errorMessage"
						/>
					</core-alert>
				</div>
			</div>
		</template>
	</core-display-info>
</template>

<script>
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import http from '@/vue/utils/http'
import { debounce } from '@/vue/utils/debounce'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreAttributesList from '@/vue/components/common/core/AttributesList'
import CoreDisplayInfo from '@/vue/components/common/core/DisplayInfo'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgFile from '@/vue/components/common/svg/File'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreAlert,
		CoreAttributesList,
		CoreDisplayInfo,
		CoreLoader,
		CoreTooltip,
		SvgCircleCheck,
		SvgCircleClose,
		SvgExternal,
		SvgFile
	},
	props : {
		displayOptions : {
			type     : Object,
			required : true
		},
		url : {
			type : String
		}
	},
	data () {
		return {
			pageUrl              : '',
			currentPageUrl       : '',
			buttonLocked         : true,
			error                : false,
			isLoading            : false,
			showResults          : false,
			showAdvancedSettings : false,
			strings              : {
				label       : this.$t.__('Display HTML Sitemap', this.$td),
				page        : this.$t.__('Dedicated Page', this.$td),
				placeholder : this.$t.sprintf(
					// Translators: 1 - A URL.
					this.$t.__('e.g. %1$s', this.$td),
					`${this.rootStore.aioseo.urls.home}/new-page`
				),
				pageButton           : this.$t.__('Open HTML Sitemap', this.$td),
				errorMessage         : this.$t.__('The page that you have entered is invalid or already exists. Please enter a page with a unique slug.', this.$td),
				errorMessageDisabled : this.$t.sprintf(
					// Translators: 1 - Opening link tag, 2 - Closing link tag.
					this.$t.__('Dedicated HTML Sitemaps do not work while using "plain" permalinks. Please update your %1$spermalink structure%2$s to use this option.', this.$td),
					'<a href="' + this.rootStore.aioseo.urls.home + '/wp-admin/options-permalink.php">',
					'</a>'
				),
				shortcodeAttributesDescription : this.$t.__('The following shortcode attributes can be used to override the default settings:', this.$td),
				phpArgumentsDescription        : this.$t.__('The function accepts an associative array with the following arguments that can be used to override the default settings:', this.$td),
				advancedSettings               : this.$t.__('Advanced Settings', this.$td),
				editAndSaveFirst               : this.$t.__('To view the sitemap, enter a URL and save changes.', this.$td),
				saveFirst                      : this.$t.__('To view the new sitemap, first save changes.', this.$td)
			},
			attributes : [
				{
					name        : 'post_types',
					description : this.$t.__('The post types (by slug, comma-separated) that are included in the sitemap.', this.$td)
				},
				{
					name        : 'taxonomies',
					description : this.$t.__('The taxonomies (by slug, comma-separated) that are included in the sitemap.', this.$td)
				},
				{
					name        : 'label_tag',
					description : this.$t.sprintf(
						// Translators: 1 - The default value.
						this.$t.__('The HTML tag that is used for the label of each section. Defaults to %1$s.', this.$td),
						'<code>h4</code>'
					)
				},
				{
					name        : 'show_label',
					description : this.$t.sprintf(
						// Translators: 1 - The default value.
						this.$t.__('Whether the labels should be shown or not. Defaults to %1$s.', this.$td),
						'<code>true</code>'
					)
				},
				/* {
					name        : 'nofollow_links',
					description : this.$t.__('Whether the links should be nofollowed.', this.$td)
				}, */
				{
					name        : 'publication_date',
					description : this.$t.__('Whether the publication date of posts should be shown.', this.$td)
				},
				{
					name        : 'archives',
					description : this.$t.__('Whether the regular sitemap or compact date archive sitemap is output.', this.$td)
				},
				{
					name        : 'order',
					// Translators: 1 - "ASC", 2 - "DESC".
					description : this.$t.sprintf(
						// Translators: 1 - HTML code opening tag, 2 - HTML code closing tag.
						this.$t.__('The sort direction. The supported values are %1$s and %2$s.', this.$td),
						'<code>ASC</code>',
						'<code>DESC</code>'
					)
				},
				{
					name        : 'order_by',
					description : this.$t.sprintf(
						// Translators: 1 - HTML code opening tag, 2 - HTML code closing tag.
						this.$t.__('The sort order. The supported values are %1$s, %2$s, %3$s and %4$s.', this.$td),
						'<code>publish_date</code>',
						'<code>last_updated</code>',
						'<code>alphabetical</code>',
						'<code>id</code>'
					)
				}
			]
		}
	},
	created () {
		this.pageUrl = this.dedicatedPageDisabled ?  '' : this.optionsStore.options.sitemap.html.pageUrl
		this.addSiteUrl()

		if (this.pageUrl) {
			this.buttonLocked = false
		}

		window.aioseoBus.$on('changes-saved', () => {
			this.processChangesSaved()
		})
	},
	computed : {
		sitemapButtonDisabled () {
			if (this.pageUrl) {
				return this.strings.saveFirst
			}
			return this.strings.editAndSaveFirst
		},
		dedicatedPageDisabled () {
			return '' === this.rootStore.aioseo.data.permalinkStructure
		}
	},
	methods : {
		addSiteUrl () {
			if (!this.pageUrl) {
				return
			}
			if (!this.pageUrl.startsWith(`http://${this.rootStore.aioseo.urls.domain}`) && !this.pageUrl.startsWith(`https://${this.rootStore.aioseo.urls.domain}`)) {
				this.pageUrl = this.rootStore.aioseo.data.isSsl
					? `https://${this.rootStore.aioseo.urls.domain}/${this.pageUrl}`
					: `http://${this.rootStore.aioseo.urls.domain}/${this.pageUrl}`
			}
		},
		validateNewSlug (event) {
			this.pageUrl = event.target.value

			if (!this.pageUrl) {
				this.optionsStore.options.sitemap.html.pageUrl = ''
				return
			}

			// Check if the new URL is the same as the stored one, it mustn't be validated. If not, the button must be locked if it isn't already.
			if (this.currentPageUrl === this.pageUrl) {
				this.buttonLocked = false
				this.error        = false
				return
			} else {
				this.buttonLocked = true
			}

			this.isLoading = true
			debounce(() => {
				// Throw an error if the URL contains any spaces.
				if (/\s/.test(this.pageUrl)) {
					this.error = true
					this.isLoading = false
					return
				}

				http.post(this.$links.restUrl('sitemap/validate-html-sitemap-slug'))
					.send({
						pageUrl : this.pageUrl
					})
					.then((response) => {
						if (response.body.exists) {
							this.error       = true
							this.showResults = true
						} else {
							this.error                        = false
							this.optionsStore.options.sitemap.html.pageUrl = this.pageUrl
						}
						this.isLoading = false
					})
					.catch(() => {
						this.error     = true
						this.isLoading = false
					})
			}, 500)
		},
		processChangesSaved () {
			if (!this.pageUrl) {
				this.buttonLocked = true
				return
			}
			this.currentPageUrl = this.pageUrl
			this.buttonLocked   = false
		}
	}
}
</script>

<style lang="scss">
.aioseo-display-info {
	svg.aioseo-new-page{
		width: 100%;
		height: auto;
		max-width: 45px;
	}

	.new-page {
		.aioseo-row {
			margin: 0;
		}
		svg.aioseo-external {
			width: 14px;
			height: 14px;
			margin-right: 10px;
		}

		.aioseo-description {
			color: #434960;
		}

		button.aioseo-html-sitemaps-disabled-button {
			border: 1px solid #DCDDE1;
			color: #8C8F9A;
			background-color: #F3F4F5;
			cursor: default;
		}

		.aioseo-alert {
			width: 100%;
			margin: 8px 8px 0 8px;
		}

		.aioseo-loading-spinner {
			position: relative;
		}
	}
}
</style>