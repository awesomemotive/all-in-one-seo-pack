<template>
	<div class="aioseo-search-appearance-global">
		<core-card
			slug="searchTitleSeparator"
			:header-text="strings.titleSeparator"
		>
			<core-settings-row
				:name="GLOBAL_STRINGS.preview"
			>
				<template #content>
					<core-google-search-preview
						:title="parseTags(`#site_title ${optionsStore.options.searchAppearance.global.separator} #tagline`)"
						:description="parseTags('#tagline')"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.separatorCharacter"
			>
				<template #content>
					<core-settings-separator
						:options-separator="optionsStore.options.searchAppearance.global.separator"
						@update:separator="value => optionsStore.options.searchAppearance.global.separator = value"
						show-more-slug="searchShowMoreSeparators"
					/>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			slug="searchHomePage"
			:header-text="strings.homePage"
			id="home-page-settings"
		>
			<div
				v-if="rootStore.aioseo.data.staticHomePage"
				class="aioseo-settings-row aioseo-section-description"
			>
				<span v-html="strings.homePageDisabledDescription" />
				&nbsp;
				<span
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'staticHomePage', true)"
				/>
			</div>

			<core-settings-row
				:name="GLOBAL_STRINGS.preview"
			>
				<template #content>
					<core-google-search-preview
						v-if="rootStore.aioseo.data.staticHomePage"
						:title="homePageTitle"
						:description="parseTags(rootStore.aioseo.data.staticHomePageDescription || '#tagline')"
					/>

					<core-google-search-preview
						v-if="!rootStore.aioseo.data.staticHomePage"
						:title="homePageTitle"
						:description="parseTags(optionsStore.options.searchAppearance.global.metaDescription || '#tagline')"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				id="aioseo-home-page-site-title"
				v-if="!rootStore.aioseo.data.staticHomePage"
				:name="strings.siteTitle"
			>
				<template #content>
					<core-html-tags-editor
						v-model="optionsStore.options.searchAppearance.global.siteTitle"
						:line-numbers="false"
						single
						@counter="count => titleCount = count.length"
						tags-context="homePage"
						:default-tags="[
							'site_title',
							'separator_sa',
							'tagline'
						]"
					>
						<template #tags-description>
							{{ strings.clickToAddSiteTitle }}
						</template>
					</core-html-tags-editor>

					<div
						class="max-recommended-count"
						v-html="maxRecommendedCount(titleCount, 60)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				id="aioseo-home-page-meta-description"
				v-if="!rootStore.aioseo.data.staticHomePage"
				:name="strings.metaDescription"
			>
				<template #content>
					<core-html-tags-editor
						v-model="optionsStore.options.searchAppearance.global.metaDescription"
						:line-numbers="false"
						description
						@counter="count => descriptionCount = count.length"
						tags-context="homePage"
						:default-tags="[
							'site_title',
							'separator_sa',
							'tagline'
						]"
					>
						<template #tags-description>
							{{ strings.clickToAddSiteDescription }}
						</template>
					</core-html-tags-editor>

					<div
						class="max-recommended-count"
						v-html="maxRecommendedCount(descriptionCount, 160)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.searchAppearance.advanced.useKeywords && !rootStore.aioseo.data.staticHomePage"
				:name="strings.keywords"
				align
			>
				<template #content>
					<base-select
						multiple
						taggable
						:options="getJsonValue(optionsStore.options.searchAppearance.global.keywords, []) || []"
						:modelValue="getJsonValue(optionsStore.options.searchAppearance.global.keywords, []) || []"
						@update:modelValue="values => optionsStore.options.searchAppearance.global.keywords = setJsonValue(values)"
						:tag-placeholder="strings.tagPlaceholder"
					/>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			slug="searchSchema"
			:header-text="strings.knowledgeGraph"
		>
			<template #tooltip>
				{{ strings.knowledgeGraphDescription }}
			</template>

			<core-settings-row
				v-if="optionsStore.internalOptions.internal.deprecatedOptions.includes('enableSchemaMarkup')"
				:name="strings.enableSchemaMarkup"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.deprecated.searchAppearance.global.schema.enableSchemaMarkup"
						name="enableSchemaMarkup"
						:options="[
							{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.on, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.websiteName"
			>
				<template #content>
					<core-html-tags-editor
						v-model="optionsStore.options.searchAppearance.global.schema.websiteName"
						:line-numbers="false"
						tags-context="knowledgeGraph"
						:default-tags="[
							'site_title'
						]"
					/>

					<div class="aioseo-description">
						{{ strings.websiteNameDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.websiteAlternateName"
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.searchAppearance.global.schema.websiteAlternateName"
					/>

					<div class="aioseo-description">
						{{ strings.websiteAlternateNameDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				id="schema-graph-site-represents"
				:name="strings.personOrOrganization"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.searchAppearance.global.schema.siteRepresents"
						name="siteRepresents"
						:options="[
							{ label: strings.person, value: 'person' },
							{ label: strings.organization, value: 'organization' }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.personOrOrganizationDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="'person' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="strings.choosePerson"
			>
				<template #content>
					<base-select
						class="person-chooser"
						:options="users"
						:modelValue="getPersonOptions(optionsStore.options.searchAppearance.global.schema.person)"
						@update:modelValue="value => optionsStore.options.searchAppearance.global.schema.person = value.value"
					>
						<template #singleLabel="{ option }">
							<div class="person-label">
								<div
									v-if="option.gravatar"
									class="person-avatar"
								>
									<img
										alt="User Gravatar"
										:src="option.gravatar"
									/>
								</div>
								<div class="person-name">
									{{ option.label }}
								</div>
							</div>
						</template>

						<template #option="{ option }">
							<div class="person-label">
								<div
									v-if="option.gravatar"
									class="person-avatar"
								>
									<img
										alt="User Gravatar"
										:src="option.gravatar"
									/>
								</div>
								<div class="person-name">
									{{ option.label }}
								</div>
							</div>
						</template>
					</base-select>
				</template>
			</core-settings-row>

			<core-settings-row
				id="aioseo-organization-name"
				v-if="'organization' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="name"
			>
				<template #content>
					<core-html-tags-editor
						v-model="optionsStore.options.searchAppearance.global.schema.organizationName"
						:line-numbers="false"
						tags-context="knowledgeGraph"
						:default-tags="[
							'site_title'
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				id="aioseo-organization-description"
				v-if="'organization' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="strings.organizationDescription"
			>
				<template #content>
					<core-html-tags-editor
						v-model="optionsStore.options.searchAppearance.global.schema.organizationDescription"
						:line-numbers="false"
						description
						@counter="count => descriptionCount = count.length"
						tags-context="knowledgeGraph"
						:default-tags="[
							'tagline'
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="schema-graph-name"
				v-if="'organization' !== optionsStore.options.searchAppearance.global.schema.siteRepresents && 'manual' === optionsStore.options.searchAppearance.global.schema.person"
				:name="name"
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.searchAppearance.global.schema.personName"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="'organization' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="strings.email"
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.searchAppearance.global.schema.email"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				id="schema-graph-phone"
				class="schema-graph-phone"
				v-if="'organization' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="strings.phone"
			>
				<template #content>
					<base-phone
						v-model="optionsStore.options.searchAppearance.global.schema.phone"
					/>

					<div class="aioseo-description" v-html="strings.phoneDescription"/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="'organization' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="strings.foundingDate"
			>
				<template #content>
					<base-date-picker
						type="date"
						size="large"
						dateFormat="m/d/Y"
						:defaultValue="dateStringToLocalJs(optionsStore.options.searchAppearance.global.schema.foundingDate)"
						@change="value => optionsStore.options.searchAppearance.global.schema.foundingDate = dateJsToLocal(value, 'yyyy-MM-dd')"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="'organization' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="strings.numberOfEmployees"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.isRange"
					/>

					<label>{{ strings.useRange }}</label>

					<base-input
						class="number-of-employees-number"
						v-if="!optionsStore.options.searchAppearance.global.schema.numberOfEmployees.isRange"
						type="number"
						size="medium"
						v-model="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.number"
						:step="1"
						:min="1"
					/>

					<div
						class="number-of-employees-range"
						v-if="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.isRange"
					>
						<label>{{ strings.from }}</label>
						<base-input
							type="number"
							size="medium"
							v-model="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.from"
							:step="1"
							:min="0"
							:max="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to"
							@blur="value => updateMinimumEmployees(value)"
						/>

						<label>{{ strings.to }}</label>
						<base-input
							type="number"
							size="medium"
							v-model="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to"
							:step="1"
							:min="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.from"
							@blur="value => updateMaximumEmployees(value)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				class="schema-graph-image"
				v-if="'organization' === optionsStore.options.searchAppearance.global.schema.siteRepresents"
				:name="strings.logo"
			>
				<template #content>
					<core-image-uploader
						v-model="optionsStore.options.searchAppearance.global.schema.organizationLogo"
						:description="strings.logoDescription"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="schema-graph-image"
				v-if="'organization' !== optionsStore.options.searchAppearance.global.schema.siteRepresents && 'manual' === optionsStore.options.searchAppearance.global.schema.person"
				:name="strings.logo"
			>
				<template #content>
					<core-image-uploader
						v-model="optionsStore.options.searchAppearance.global.schema.personLogo"
					/>
				</template>
			</core-settings-row>

			<div class="aioseo-settings-row local-seo">
				<svg-local-seo />
				<div class="local-seo-text">
					<div v-html="strings.goToLocalSeo" />

					<base-button
						v-if="!licenseStore.isUnlicensed && !addons.requiresUpgrade('aioseo-local-business')"
						size="medium"
						type="blue"
						tag="a"
						:href="rootStore.aioseo.urls.aio.localSeo"
					>
						{{ strings.goToLocalSeoSettings }}
					</base-button>

					<base-button
						v-if="licenseStore.isUnlicensed || addons.requiresUpgrade('aioseo-local-business')"
						size="medium"
						type="green"
						tag="a"
						:href="rootStore.aioseo.urls.aio.localSeo"
					>
						{{ strings.unlockLocalSeo }}
					</base-button>
				</div>
			</div>
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import {
	dateJsToLocal,
	dateStringToLocalJs
} from '@/vue/utils/date'

import { useJsonValues } from '@/vue/composables/JsonValues'
import { useMaxCounts } from '@/vue/composables/MaxCounts'
import { useTags } from '@/vue/composables/Tags'

import BaseDatePicker from '@/vue/components/common/base/DatePicker'
import BasePhone from '@/vue/components/common/base/Phone'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import BaseToggle from '@/vue/components/common/base/Toggle'
import CoreCard from '@/vue/components/common/core/Card'
import CoreGoogleSearchPreview from '@/vue/components/common/core/GoogleSearchPreview'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreSettingsSeparator from '@/vue/components/common/core/SettingsSeparator'
import SvgLocalSeo from '@/vue/components/common/svg/local/Seo'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getJsonValue,
			setJsonValue
		} = useJsonValues()

		const {
			maxRecommendedCount
		} = useMaxCounts()

		const {
			parseTags
		} = useTags({
			separator : undefined
		})

		return {
			GLOBAL_STRINGS,
			addons,
			dateJsToLocal,
			dateStringToLocalJs,
			getJsonValue,
			licenseStore : useLicenseStore(),
			links,
			maxRecommendedCount,
			optionsStore : useOptionsStore(),
			parseTags,
			rootStore    : useRootStore(),
			setJsonValue
		}
	},
	components : {
		BaseDatePicker,
		BasePhone,
		BaseRadioToggle,
		BaseToggle,
		CoreCard,
		CoreGoogleSearchPreview,
		CoreHtmlTagsEditor,
		CoreImageUploader,
		CoreSettingsRow,
		CoreSettingsSeparator,
		SvgLocalSeo
	},
	data () {
		return {
			titleCount       : 0,
			descriptionCount : 0,
			strings          : {
				titleSeparator              : __('Title Separator', td),
				separatorCharacter          : __('Separator Character', td),
				homePageDisabledDescription : sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
					__('You are using a static home page which is found under Pages. You can %1$sedit your home page settings%2$s directly to change the title and description.', td),
					`<a href="${this.rootStore.aioseo.urls.staticHomePage}&aioseo-scroll=aioseo-post-settings-post-title-row&aioseo-highlight=aioseo-post-settings-post-title-row,aioseo-post-settings-meta-description-row">`,
					'</a>'
				),
				homePage                        : __('Home Page', td),
				siteTitle                       : __('Site Title', td),
				clickToAddSiteTitle             : __('Click on the tags below to insert variables into your site title.', td),
				metaDescription                 : __('Meta Description', td),
				clickToAddSiteDescription       : __('Click on the tags below to insert variables into your meta description.', td),
				knowledgeGraph                  : __('Knowledge Graph', td),
				knowledgeGraphDescription       : __('Google, Bing and other search engines use specific data from your schema markup to output data in their Knowledge Panels. This data is known as the Knowledge Graph. Use these settings to change how that data looks.', td),
				personOrOrganization            : __('Person or Organization', td),
				person                          : __('Person', td),
				organization                    : __('Organization', td),
				personOrOrganizationDescription : __('Choose whether the site represents a person or an organization.', td),
				choosePerson                    : __('Choose a Person', td),
				organizationName                : __('Organization Name', td),
				organizationDescription         : __('Organization Description', td),
				personName                      : __('Person Name', td),
				phone                           : __('Phone Number', td),
				phoneDescription                : sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
					__('Enter the primary phone number for your business. Don’t have a business phone number? %1$sSee this guide on how to get one.%2$s', td),
					`<a href="${links.getDocUrl('businessPhoneNumber')}" target="_blank">`,
					'</a>'
				),
				email           : __('Email Address', td),
				logo            : __('Logo', td),
				logoDescription : sprintf(
					'%1$s<br/>%2$s',
					__('Minimum size: 112px x 112px, The image must be in JPG, PNG, GIF, SVG, or WEBP format.', td),
					__('Google recommends you making sure that the image looks how you intend it to look on a purely white background.', td)
				),
				goToLocalSeo : sprintf(
					// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
					__('Our Local SEO addon enables you to tell Google about your business (name, address, opening hours, contact info & more) and further enhances your Knowledge Graph schema markup.', td),
					 '<strong>',
					'</strong>'
				),
				goToLocalSeoSettings            : __('Configure Local SEO', td),
				unlockLocalSeo                  : __('Unlock Local SEO', td),
				enableSchemaMarkup              : __('Enable Schema Markup', td),
				keywords                        : __('Keywords', td),
				tagPlaceholder                  : __('Press enter to create a keyword', td),
				websiteName                     : __('Website Name', td),
				websiteNameDescription          : __('A name that Google may use for your homepage in mobile search results. This will default to the WordPress site title if left blank.', td),
				websiteAlternateName            : __('Alternate Website Name', td),
				websiteAlternateNameDescription : __('An alternate name for your site. This could be an acronym or shorter version of your website name.', td),
				foundingDate                    : __('Founding Date', td),
				numberOfEmployees               : __('Number of Employees', td),
				useRange                        : __('Use Range', td),
				from                            : __('From', td),
				to                              : __('To', td)
			}
		}
	},
	computed : {
		homePageTitle () {
			if (this.rootStore.aioseo.data.staticHomePage) {
				return this.parseTags(this.parseSeparator(this.rootStore.aioseo.data.staticHomePageTitle) || '#site_title')
			}

			return this.parseTags(this.parseSeparator(this.optionsStore.options.searchAppearance.global.siteTitle) || '#site_title')
		},
		users () {
			return [ {
				label : __('Manually Enter Person', td),
				value : 'manual'
			} ].concat(this.rootStore.aioseo.users.map(u => ({
				label    : `${u.displayName} (${u.email})`,
				gravatar : u.gravatar,
				value    : u.id
			})))
		},
		name () {
			if ('organization' === this.optionsStore.options.searchAppearance.global.schema.siteRepresents) {
				return this.strings.organizationName
			}

			return this.strings.personName
		}
	},
	methods : {
		getPersonOptions (option) {
			return this.users.find(u => option && u.value.toString() === option.toString())
		},
		parseSeparator (title) {
			return title.replace('#separator_sa', this.optionsStore.options.searchAppearance.global.separator)
		},
		updateMinimumEmployees (minimumEmployeesValue) {
			minimumEmployeesValue = parseFloat(minimumEmployeesValue)
			if (minimumEmployeesValue > parseFloat(this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to)) {
				this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.from = parseFloat(this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to)
				return
			}

			this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.from = minimumEmployeesValue
		},
		updateMaximumEmployees (maximumEmployeesValue) {
			maximumEmployeesValue = parseFloat(maximumEmployeesValue)
			if (maximumEmployeesValue < parseFloat(this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.from)) {
				this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to = parseFloat(this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.from)
				return
			}

			this.optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to = maximumEmployeesValue
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-appearance-global {
	.schema-graph-name,
	.schema-graph-contact-type-manual {
		margin-bottom: 16px;

		.aioseo-input {
			max-width: 600px;
		}
	}
	.schema-graph-contact-type {
		margin-bottom: 16px;

		.aioseo-select {
			max-width: 300px;
		}
	}

	.schema-graph-image {
		margin-bottom: 16px;
	}

	.person-chooser {
		max-width: 600px;

		.person-label {
			display: flex;
			align-items: center;

			.person-avatar {
				margin-right: 16px;
				height: 30px;

				img {
					height: 30px;
					width: 30px;
					border-radius: 50%;
				}
			}
		}
	}

	.aioseo-settings-row.local-seo {
		display: flex;
		align-items: center;
		background: $box-background;

		.local-seo-text {
			line-height: 1.4;
			font-size: 15px;
			color: $black;
			max-width: 750px;

			.aioseo-button {
				margin-top: 10px;
			}
		}

		svg.aioseo-local-seo {
			width: 139px;
			height: 106px;
			margin-right: 60px;
		}

		// Override of global settings.
		&:last-child {
			padding: 30px;
		}
	}

	.aioseo-google-search-preview {
		border: 1px solid $border;
		padding: 16px;
	}

	.number-of-employees-number {
		margin-top: 12px;
		max-width: 420px;
	}

	.number-of-employees-range {
		display: flex;
		align-items: center;
		margin-top: 12px;

		label {
			margin-right: 12px;
		}

		.aioseo-input-container {
			margin-right: 12px;
		}
	}
}
</style>