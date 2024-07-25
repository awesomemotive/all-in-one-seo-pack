<template>
	<div class="aioseo-search-appearance-global">
		<core-card
			slug="searchTitleSeparator"
			:header-text="strings.titleSeparator"
		>
			<core-settings-row
				:name="$constants.GLOBAL_STRINGS.preview"
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
					v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'staticHomePage', true)"
				/>
			</div>

			<core-settings-row
				:name="$constants.GLOBAL_STRINGS.preview"
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
						@counter="count => updateCount(count, 'titleCount')"
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
						@counter="count => updateCount(count, 'descriptionCount')"
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
							{ label: $constants.GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.on, value: true }
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
						@counter="count => updateCount(count, 'descriptionCount')"
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
						step="1"
						min="1"
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
							step="1"
							min="0"
							:max="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to"
							@blur="value => updateMinimumEmployees(value)"
						/>

						<label>{{ strings.to }}</label>
						<base-input
							type="number"
							size="medium"
							v-model="optionsStore.options.searchAppearance.global.schema.numberOfEmployees.to"
							step="1"
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
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

import { Date as DateMixin } from '@/vue/mixins/Date'
import { JsonValues } from '@/vue/mixins/JsonValues'
import { MaxCounts } from '@/vue/mixins/MaxCounts'
import { Tags } from '@/vue/mixins/Tags'

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
export default {
	setup () {
		return {
			addons,
			licenseStore : useLicenseStore(),
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
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
	mixins : [ DateMixin, JsonValues, MaxCounts, Tags ],
	data () {
		return {
			titleCount       : 0,
			descriptionCount : 0,
			separator        : undefined,
			strings          : {
				titleSeparator              : this.$t.__('Title Separator', this.$td),
				separatorCharacter          : this.$t.__('Separator Character', this.$td),
				homePageDisabledDescription : this.$t.sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
					this.$t.__('You are using a static home page which is found under Pages. You can %1$sedit your home page settings%2$s directly to change the title and description.', this.$td),
					`<a href="${this.rootStore.aioseo.urls.staticHomePage}&aioseo-scroll=aioseo-post-settings-post-title-row&aioseo-highlight=aioseo-post-settings-post-title-row,aioseo-post-settings-meta-description-row">`,
					'</a>'
				),
				homePage                        : this.$t.__('Home Page', this.$td),
				siteTitle                       : this.$t.__('Site Title', this.$td),
				clickToAddSiteTitle             : this.$t.__('Click on the tags below to insert variables into your site title.', this.$td),
				metaDescription                 : this.$t.__('Meta Description', this.$td),
				clickToAddSiteDescription       : this.$t.__('Click on the tags below to insert variables into your meta description.', this.$td),
				knowledgeGraph                  : this.$t.__('Knowledge Graph', this.$td),
				knowledgeGraphDescription       : this.$t.__('Google, Bing and other search engines use specific data from your schema markup to output data in their Knowledge Panels. This data is known as the Knowledge Graph. Use these settings to change how that data looks.', this.$td),
				personOrOrganization            : this.$t.__('Person or Organization', this.$td),
				person                          : this.$t.__('Person', this.$td),
				organization                    : this.$t.__('Organization', this.$td),
				personOrOrganizationDescription : this.$t.__('Choose whether the site represents a person or an organization.', this.$td),
				choosePerson                    : this.$t.__('Choose a Person', this.$td),
				organizationName                : this.$t.__('Organization Name', this.$td),
				organizationDescription         : this.$t.__('Organization Description', this.$td),
				personName                      : this.$t.__('Person Name', this.$td),
				phone                           : this.$t.__('Phone Number', this.$td),
				phoneDescription                : this.$t.sprintf(
					this.$t.__('Enter the primary phone number for your business. Don’t have a business phone number? %1$sSee this guide on how to get one.%2$s', this.$td),
					`<a href="${this.$links.getDocUrl('businessPhoneNumber')}" target="_blank">`,
					'</a>'
				),
				email        : this.$t.__('Email Address', this.$td),
				logo         : this.$t.__('Logo', this.$td),
				goToLocalSeo : this.$t.sprintf(
					// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
					this.$t.__('Our Local SEO addon enables you to tell Google about your business (name, address, opening hours, contact info & more) and further enhances your Knowledge Graph schema markup.', this.$td),
					 '<strong>',
					'</strong>'
				),
				goToLocalSeoSettings            : this.$t.__('Configure Local SEO', this.$td),
				unlockLocalSeo                  : this.$t.__('Unlock Local SEO', this.$td),
				enableSchemaMarkup              : this.$t.__('Enable Schema Markup', this.$td),
				keywords                        : this.$t.__('Keywords', this.$td),
				tagPlaceholder                  : this.$t.__('Press enter to create a keyword', this.$td),
				websiteName                     : this.$t.__('Website Name', this.$td),
				websiteNameDescription          : this.$t.__('A name that Google may use for your homepage in mobile search results. This will default to the WordPress site title if left blank.', this.$td),
				websiteAlternateName            : this.$t.__('Alternate Website Name', this.$td),
				websiteAlternateNameDescription : this.$t.__('An alternate name for your site. This could be an acronym or shorter version of your website name.', this.$td),
				foundingDate                    : this.$t.__('Founding Date', this.$td),
				numberOfEmployees               : this.$t.__('Number of Employees', this.$td),
				useRange                        : this.$t.__('Use Range', this.$td),
				from                            : this.$t.__('From', this.$td),
				to                              : this.$t.__('To', this.$td)
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
				label : this.$t.__('Manually Enter Person', this.$td),
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