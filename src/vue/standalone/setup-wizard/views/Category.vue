<template>
	<div class="aioseo-wizard-category">
		<wizard-header />
		<wizard-container>

			<wizard-body>
				<wizard-steps />

				<div class="header">
					{{ strings.category }}
				</div>

				<div class="description">{{ strings.selectCategory }}</div>

				<div class="categories">
					<grid-row>
						<grid-column
							v-for="(category, index) in categories"
							:key="index"
							md="6"
						>
							<base-highlight-toggle
								type="radio"
								size="medium"
								:active="isActive(category)"
								name="category"
								:modelValue="getValue(category)"
								@update:modelValue="checked => updateValue(checked, category)"
							>
								<component
									:is="category.icon"
									v-if="category.icon"
									class="icon"
								/>
								{{ category.name }}
							</base-highlight-toggle>
						</grid-column>
					</grid-row>

					<base-highlight-toggle
						class="other-category"
						type="radio"
						size="medium"
						:active="isActive(otherCategory)"
						name="category"
						:modelValue="getValue(otherCategory)"
						@update:modelValue="checked => updateValue(checked, otherCategory)"
					>
						<component
							:is="otherCategory.icon"
							v-if="otherCategory.icon"
							class="icon"
						/>
						{{ otherCategory.name }}

						<base-input
							v-if="selected.length"
							:disabled="'other' !== selected[0].value"
							size="medium"
							v-model="setupWizardStore.category.categoryOther"
							:placeholder="strings.enterYourAnswer"
							ref="other-category"
						/>
					</base-highlight-toggle>
				</div>

				<div v-if="loaded" class="site-info">
					<div class="site-title aioseo-settings-row no-border">
						<div class="settings-name">
							<div class="name small-margin">{{ strings.siteTitle }}</div>
						</div>

						<core-html-tags-editor
							v-model="setupWizardStore.category.siteTitle"
							:line-numbers="false"
							single
							tags-context="homePage"
							:default-tags="[
								'site_title',
								'separator_sa',
								'tagline'
							]"
						/>
					</div>

					<div class="site-description aioseo-settings-row no-border no-margin small-padding">
						<div class="settings-name">
							<div class="name small-margin">{{ strings.metaDescription }}</div>
						</div>

						<core-html-tags-editor
							v-model="setupWizardStore.category.metaDescription"
							:line-numbers="false"
							description
							tags-context="homePage"
							:default-tags="[
								'site_title',
								'separator_sa',
								'tagline'
							]"
						/>
					</div>
				</div>

				<template #footer>
					<div class="go-back">
						<router-link :to="setupWizardStore.getPrevLink" class="no-underline">&larr;</router-link>
						&nbsp;
						<router-link :to="setupWizardStore.getPrevLink">{{ strings.goBack }}</router-link>
					</div>
					<div class="spacer"></div>
					<base-button
						type="blue"
						:loading="loading"
						@click="saveAndContinue"
					>{{ strings.saveAndContinue }} &rarr;</base-button>
				</template>
			</wizard-body>

			<wizard-close-and-exit />
		</wizard-container>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useWizard } from '@/vue/composables/Wizard'

import BaseHighlightToggle from '@/vue/components/common/base/HighlightToggle'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgBlog from '@/vue/components/common/svg/Blog'
import SvgCorporation from '@/vue/components/common/svg/Corporation'
import SvgLocalBusiness from '@/vue/components/common/svg/local/Business'
import SvgImageSeo from '@/vue/components/common/svg/ImageSeo'
import SvgNewsChannel from '@/vue/components/common/svg/NewsChannel'
import SvgOnlineStore from '@/vue/components/common/svg/OnlineStore'
import SvgPencil from '@/vue/components/common/svg/Pencil'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { strings } = useWizard({
			stage : 'category'
		})

		return {
			optionsStore      : useOptionsStore(),
			rootStore         : useRootStore(),
			setupWizardStore  : useSetupWizardStore(),
			composableStrings : strings
		}
	},
	components : {
		BaseHighlightToggle,
		CoreHtmlTagsEditor,
		GridColumn,
		GridRow,
		SvgBlog,
		SvgCorporation,
		SvgLocalBusiness,
		SvgImageSeo,
		SvgNewsChannel,
		SvgOnlineStore,
		SvgPencil,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	data () {
		return {
			loaded           : false,
			titleCount       : 0,
			descriptionCount : 0,
			loading          : false,
			strings          : merge(this.composableStrings, {
				category        : __('Which category best describes your website?', td),
				selectCategory  : __('Select a category to help us narrow down the SEO options that work best for you and your site.', td),
				enterYourAnswer : __('Enter your answer', td),
				siteTitle       : __('Home Page Title', td),
				metaDescription : __('Home Page Meta Description', td)
			}),
			categories : [
				{ value: 'blog', name: __('Blog', td), icon: 'svg-blog' },
				{ value: 'news-channel', name: __('News Channel', td), icon: 'svg-news-channel' },
				{ value: 'online-store', name: __('Online Store', td), icon: 'svg-online-store' },
				{ value: 'small-offline-business', name: __('Small Offline Business', td), icon: 'svg-local-business' },
				{ value: 'corporation', name: __('Corporation', td), icon: 'svg-corporation' },
				{ value: 'portfolio', name: __('Portfolio', td), icon: 'svg-image-seo' }
			],
			otherCategory : { value: 'other', name: __('Other:', td), icon: 'svg-pencil', valueInput: null },
			selected      : []
		}
	},
	watch : {
		selected (newVal) {
			this.setupWizardStore.category.category = newVal[0].value

			this.triggerFeaturesUpdate(newVal[0].value)
		}
	},
	methods : {
		triggerFeaturesUpdate (category) {
			const features = [
				'aioseo-eeat',
				'aioseo-image-seo',
				'aioseo-redirects',
				'analytics',
				'broken-link-checker',
				'conversion-tools',
				'optimized-search-appearance',
				'sitemaps'
			]

			// Let's preselect some features based on the category set.
			switch (category) {
				case 'blog':
				case 'portfolio':
				case 'other':
					features.push('aioseo-index-now')
					features.push('aioseo-link-assistant')
					break
				case 'news-channel':
					features.push('aioseo-index-now')
					features.push('aioseo-link-assistant')
					features.push('aioseo-news-sitemap')
					features.push('aioseo-rest-api')
					features.push('aioseo-video-sitemap')
					break
				case 'online-store':
					features.push('aioseo-index-now')
					features.push('aioseo-link-assistant')
					features.push('aioseo-rest-api')
					features.push('aioseo-video-sitemap')
					break
				case 'small-offline-business':
					features.push('aioseo-local-business')
					features.push('aioseo-rest-api')
					features.push('aioseo-video-sitemap')
					break
				case 'corporation':
					features.push('aioseo-index-now')
					features.push('aioseo-link-assistant')
					features.push('aioseo-local-business')
					features.push('aioseo-news-sitemap')
					features.push('aioseo-rest-api')
					features.push('aioseo-video-sitemap')
					break
			}

			this.setupWizardStore.features = features
		},
		updateValue (checked, category) {
			this.selected = []
			if (checked) {
				this.selected.push(category)

				if ('other' === category.value) {
					this.$nextTick(() => {
						this.$refs['other-category'].$el.querySelector('input').focus()
					})
				}
			}
		},
		getValue (category) {
			return this.selected.includes(category)
		},
		isActive (category) {
			const index = this.selected.findIndex(c => c.value === category.value)
			if (-1 !== index) {
				return true
			}

			return false
		},
		saveAndContinue () {
			this.loading = true
			this.setupWizardStore.saveWizard('category')
				.then(() => {
					this.$router.push(this.setupWizardStore.getNextLink)
				})
		}
	},
	mounted () {
		this.$nextTick(() => {
			const category = this.categories.find(c => c.value === this.setupWizardStore.category.category) || this.categories[0]

			this.selected.push(category)
			this.triggerFeaturesUpdate(category)

			const siteTitle = this.rootStore.aioseo.data.staticHomePage ? this.rootStore.aioseo.data.staticHomePageTitle : this.optionsStore.options.searchAppearance.global.siteTitle
			if (siteTitle && this.setupWizardStore.category.siteTitle !== siteTitle) {
				this.setupWizardStore.category.siteTitle = siteTitle
			}

			const metaDescription = this.rootStore.aioseo.data.staticHomePage ? this.rootStore.aioseo.data.staticHomePageDescription : this.optionsStore.options.searchAppearance.global.metaDescription
			if (metaDescription && this.setupWizardStore.category.metaDescription !== metaDescription) {
				this.setupWizardStore.category.metaDescription = metaDescription
			}

			this.loaded = true
		})
	}
}
</script>

<style lang="scss">
.aioseo-wizard-category {
	font-size: 16px;

	.categories {
		--aioseo-gutter: 16px;

		color: $black2-hover;

		svg.icon {
			width: 20px;
			height: 20px;
			margin-right: 10px;
		}

		.other-category {
			padding-block: 4px;
			margin-top: var(--aioseo-gutter);

			.aioseo-input {
				margin-left: 10px;

				input {
					height: 36px;
					padding-block: 0;
					border: none;

					&:focus {
						box-shadow: none;
						outline: none;
					}
				}
			}
		}
	}

	.site-info {

		> :first-child {
			margin-top: var(--aioseo-gutter);
			padding-bottom: 0;
		}

		.settings-name .name {
			margin: 12px 0;
		}
	}

	.go-back {
		a {
			color: $black2;
			font-size: 14px;
		}
	}
}
</style>