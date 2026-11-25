<template>
	<div class="aioseo-writing-assistant-settings">
		<core-card
			slug="truSeoSettings"
			:header-text="strings.truSeoSettings"
		>
			<core-settings-row
				:name="strings.truSeo"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.truSeo"
					/>

					<div class="aioseo-description">
						{{ strings.truSeoDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.truSeoPostTypes"
				v-if="optionsStore.options.advanced.truSeo"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.advanced.truSeoObjects.postTypes.all"
					>
						{{ strings.includeAllTruSeoPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.advanced.truSeoObjects.postTypes.all"
						:options="optionsStore.options.advanced.truSeoObjects"
						:excluded="ineligible.postTypes"
						type="postTypes"
					/>

					<div class="aioseo-description">
						{{ strings.selectTruSeoPostTypes }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.advanced.truSeo"
			>
				<template #name>
					{{ strings.truSeoTaxonomies }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>

				<template #content>
					<base-checkbox
						v-if="licenseStore.isUnlicensed"
						disabled
						size="medium"
						:modelValue="true"
					>
						{{ strings.includeAllTruSeoTaxonomies }}
					</base-checkbox>

					<base-checkbox
						v-if="!licenseStore.isUnlicensed"
						size="medium"
						v-model="optionsStore.options.advanced.truSeoObjects.taxonomies.all"
					>
						{{ strings.includeAllTruSeoTaxonomies }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.advanced.truSeoObjects.taxonomies.all && !licenseStore.isUnlicensed"
						:options="optionsStore.options.advanced.truSeoObjects"
						:excluded="ineligible.taxonomies"
						type="taxonomies"
					/>

					<div class="aioseo-description">
						{{ strings.selectTruSeoTaxonomies }}
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-safe-html="strings.truSeoTaxonomiesUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.highlighter"
				v-if="optionsStore.options.advanced.truSeo"
			>
				<template #content>
					<base-toggle v-model="optionsStore.options.advanced.highlighter"/>

					<div class="aioseo-description">
						{{ strings.highlighterDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.highlightStyle"
				v-if="optionsStore.options.advanced.truSeo && optionsStore.options.advanced.highlighter"
			>
				<template #content>
					<div class="highlight-style-options">
						<button
							v-for="option in highlightStyleOptions"
							:key="option.value"
							type="button"
							class="highlight-style-options__option"
							:class="{ 'highlight-style-options__option--active': optionsStore.options.advanced.highlighterStyle === option.value }"
							@click="optionsStore.options.advanced.highlighterStyle = option.value"
						>
							<span
								class="highlight-style-options__preview"
								:class="`is-${option.value}`"
							>
								{{ strings.previewSample }}
							</span>

							<span class="highlight-style-options__label">
								<span
									class="highlight-style-options__radio"
									:class="{ 'highlight-style-options__radio--checked': optionsStore.options.advanced.highlighterStyle === option.value }"
								/>

								{{ option.label }}
							</span>
						</button>
					</div>

					<div class="aioseo-description">
						{{ strings.highlightStyleDescription }}
					</div>
				</template>
			</core-settings-row>

			<!-- Spelling is independent of the highlighter: it has its own tab and its own fix/jump
			actions, and the worker only reads the spellChecker option. -->
			<core-settings-row
				:name="strings.spellChecker"
				v-if="optionsStore.options.advanced.truSeo"
			>
				<template #content>
					<base-toggle v-model="optionsStore.options.advanced.spellChecker"/>

					<div class="aioseo-description">
						{{ strings.spellCheckerDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.headlineAnalyzer"
			>
				<template #content>
					<base-toggle v-model="optionsStore.options.advanced.headlineAnalyzer"/>

					<div class="aioseo-description">
						{{ strings.headlineAnalyzerDescription }}
					</div>
				</template>
			</core-settings-row>

		</core-card>

		<core-card
			slug="writingAssistantSettings"
			:header-text="strings.writingAssistant"
		>
			<template #tooltip>
				<div>{{ strings.tooltip }}</div>
			</template>

			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}

				<span
					v-safe-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'writingAssistantHowToUse', true)"
				/>

			</div>

			<core-settings-row
				:name="strings.connect"
				class="aioseo-writing-assistant-settings__connect"
			>
				<template #content>
					<div v-if="!writingAssistantSettingsStore.seoBoost.isLoggedIn">
						<div class="aioseo-writing-assistant-settings__connect-buttons">
							<base-button
								type="green"
								size="medium"
								@click="createAccount"
								v-if="!openLogin"
							>
								{{ strings.createAccount }}
							</base-button>
							<div v-if="!openLogin">{{ strings.or }}</div>
							<seo-boost-login
								:button-only="true"
								:button-text="!openLogin ? strings.connectExisting : strings.connectNow"
								:button-type="openLogin ? 'green' : 'gray'"
								:button-icons="false"
								button-size="medium"
							/>
						</div>
						<div class="aioseo-description">
							{{ strings.connectDescription }}
						</div>
					</div>
					<div
						v-if="writingAssistantSettingsStore.seoBoost.isLoggedIn"
						class="aioseo-writing-assistant-settings__connect-logout"
					>
						<div>
							<base-button
								type="blue"
								size="medium"
								@click="showDisconnectModal = true"
							>
								{{ strings.logoutButton }}
							</base-button>
						</div>
						<div>{{ strings.loggedIn }}</div>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.defaultCountry"
				v-if="writingAssistantSettingsStore.seoBoost.isLoggedIn"
			>
				<template #content>
					<base-select
						class="select-auto"
						size="medium"
						:options="writingAssistantSettingsStore.getCountriesOptions"
						:modelValue="writingAssistantSettingsStore.userCountryOption"
						@update:modelValue="option => writingAssistantSettingsStore.seoBoost.userOptions.country = option.value"
						:disabled="writingAssistantSettingsStore.loading"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.defaultLanguage"
				v-if="writingAssistantSettingsStore.seoBoost.isLoggedIn"
			>
				<template #content>
					<base-select
						class="select-auto"
						size="medium"
						:options="writingAssistantSettingsStore.getLanguagesOptions"
						:modelValue="writingAssistantSettingsStore.userLanguageOption"
						@update:modelValue="option => writingAssistantSettingsStore.seoBoost.userOptions.language = option.value"
						:disabled="writingAssistantSettingsStore.loading"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.postType"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.writingAssistant.postTypes.all"
					>
						{{ strings.includeAllPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.writingAssistant.postTypes.all"
						:options="optionsStore.options.writingAssistant"
						type="postTypes"
						:supports="['editor']"
						:excluded="['attachment']"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostTypes }}
					</div>
				</template>
			</core-settings-row>

		</core-card>

		<disconnect-modal
			v-if="writingAssistantSettingsStore.seoBoost.isLoggedIn"
			:show-modal="showDisconnectModal"
			@continue="disconnect"
			@cancel="showDisconnectModal = false"
		/>
	</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore,
	useWritingAssistantStore,
	useWritingAssistantSettingsStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __, sprintf } from '@/vue/plugins/translations'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseButton from '@/vue/components/common/base/Button'
import BaseSelect from '@/vue/components/common/base/Select'
import BaseToggle from '@/vue/components/common/base/Toggle'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import SeoBoostLogin from '@/vue/standalone/writing-assistant/views/partials/authenticate/Seoboost'
import DisconnectModal from '@/vue/standalone/writing-assistant/views/partials/authenticate/DisconnectModal'

const td = import.meta.env.VITE_TEXTDOMAIN

const licenseStore = useLicenseStore()
const optionsStore = useOptionsStore()
const rootStore    = useRootStore()
const writingAssistantStore = useWritingAssistantStore()
const writingAssistantSettingsStore = useWritingAssistantSettingsStore()
writingAssistantSettingsStore.hookSaveUserOptions()

const showDisconnectModal = ref(false)
const openLogin = ref(false)

// Object types TruSEO can never analyse, so the include-lists don't offer a checkbox that
// silently does nothing. Comes from PHP, which owns the exclusion rules, so the two can't drift.
const ineligible = computed(() => ({
	postTypes  : rootStore.aioseo.truSeoIneligible?.postTypes || [],
	taxonomies : rootStore.aioseo.truSeoIneligible?.taxonomies || []
}))

const strings = {
	truSeoSettings             : __('TruSEO & Content Settings', td),
	truSeo                     : __('TruSEO', td),
	truSeoDescription          : __('Enable TruSEO to analyze your content for basic SEO issues, keyword usage, and readability, and to flag spelling mistakes as you write — helping you optimize every post for maximum traffic.', td),
	truSeoPostTypes            : __('Post Types', td),
	includeAllTruSeoPostTypes  : __('Include all post types', td),
	selectTruSeoPostTypes      : __('Select the post types you want TruSEO to analyze.', td),
	truSeoTaxonomies           : __('Taxonomies', td),
	includeAllTruSeoTaxonomies : __('Include all taxonomies', td),
	selectTruSeoTaxonomies     : __('Select the taxonomies you want TruSEO to analyze. This is most useful for taxonomies whose descriptions act as landing page content, such as WooCommerce product categories.', td),
	truSeoTaxonomiesUpsell     : sprintf(
		// Translators: 1 - "PRO", 2 - "Learn more".
		__('TruSEO for Taxonomies is a %1$s feature. %2$s', td),
		'PRO',
		links.getUpsellLink('general-settings', 'truseo-taxonomies', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
	),
	headlineAnalyzer            : __('Headline Analyzer', td),
	headlineAnalyzerDescription : __('Enable our Headline Analyzer to help you write irresistible headlines and rank better in search results.', td),
	spellChecker                : __('Spell Checker', td),
	spellCheckerDescription     : __('Flag misspelled words in your content and suggest corrections. Requires TruSEO to be enabled.', td),
	highlighter                 : __('Highlighter', td),
	highlighterDescription      : __('Highlight sentences that need improvement directly in the editor as you write. This sets the default for all posts; you can still toggle it per post. Requires TruSEO to be enabled.', td),
	highlightStyle              : __('Highlight style', td),
	highlightStyleDescription   : __('Choose how flagged text is marked in the editor.', td),
	styleUnderline              : __('Underline', td),
	styleBackground             : __('Highlight', td),
	previewSample               : __('Highlighted text', td),
	tooltip                     : __('Integrate seamlessly with SEOBoost via AIOSEO to supercharge your WordPress content.', td),
	description                 : __('Integrate seamlessly with SEOBoost via AIOSEO to supercharge your WordPress content.', td),
	writingAssistant            : __('Writing Assistant', td),
	seoBoost                    : __('SEOBoost CTA', td),
	postType                    : __('Post Types', td),
	includeAllPostTypes         : __('Include all post types', td),
	selectPostTypes             : __('Select the post types you want the Writing Assistant to be available.', td),
	connect                     : __('Connect to SEOBoost', td),
	connectExisting             : __('Connect to an Existing Account', td),
	connectDescription          : __('Connect to SEOBoost to get access to the Writing Assistant.', td),
	loggedIn                    : __('You\'re connected to SEOBoost!', td),
	logoutButton                : __('Disconnect', td),
	reportDefaults              : __('Report Defaults', td),
	defaultCountry              : __('Default Region', td),
	defaultLanguage             : __('Default Language', td),
	or                          : __('OR', td),
	createAccount               : __('Create a Free Account', td),
	connectNow                  : __('Now Connect to Your SEOBoost Account', td)
}

const highlightStyleOptions = [
	{ value: 'underline', label: strings.styleUnderline },
	{ value: 'background', label: strings.styleBackground }
]

const disconnect = () => {
	showDisconnectModal.value = false
	writingAssistantSettingsStore.disconnect()
}

const createAccount = () => {
	// Open a new small window to authenticate.
	const url = writingAssistantStore.seoBoost.createAccountUrl || writingAssistantSettingsStore.seoBoost.createAccountUrl
	const width = 650
	const height = 800
	const left = window.innerWidth / 2 - width / 2
	const top = window.innerHeight / 2 - height / 2
	const windowFeatures = `width=${width},height=${height},resizable=no,scrollbars=no,status=no,location=no,toolbar=no,menubar=no`
	const createAccountWindow = window.open(url, '_blank', windowFeatures)
	createAccountWindow.moveBy(left, top)

	window.addEventListener('message', (event) => {
		if (event.origin !== window.location.origin) {
			return
		}

		if ('seoboost-ms-logged-in' === event.data) {
			openLogin.value = true
		}
	})
}
</script>

<style lang="scss">
.aioseo-writing-assistant-settings {
	.inline-upsell {
		display: inline-flex;
		margin-top: 12px;
	}

	.highlight-style-options {
		display: flex;
		gap: 12px;
		margin-bottom: 8px;

		&__option {
			flex: 1;
			display: flex;
			flex-direction: column;
			gap: 10px;
			padding: 12px;
			border: 1px solid $border;
			border-radius: 6px;
			background: #fff;
			cursor: pointer;
			text-align: left;

			&:hover {
				border-color: $placeholder-color;
			}

			&--active {
				border-color: $blue;
				box-shadow: 0 0 0 1px $blue;
			}
		}

		&__preview {
			font-size: 14px;
			color: $black;

			&.is-underline {
				text-decoration: underline;
				text-decoration-color: #F97316;
				text-underline-offset: 3px;
				text-decoration-thickness: 2px;
			}

			&.is-background {
				background-color: rgba(249, 115, 22, 0.3);
				border-radius: 2px;
				padding: 0 3px;
			}
		}

		&__label {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			font-size: 13px;
			font-weight: $font-bold;
			color: $black;
		}

		&__radio {
			width: 16px;
			height: 16px;
			border: 1px solid $input-border;
			border-radius: 50%;
			flex-shrink: 0;
			position: relative;

			&--checked {
				border-color: $blue;

				&::after {
					content: '';
					position: absolute;
					top: 3px;
					left: 3px;
					width: 8px;
					height: 8px;
					border-radius: 50%;
					background: $blue;
				}
			}
		}
	}

	&__connect-logout {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	&__connect {
		.aioseo-seoboost-login {
			max-width: 500px;
		}

		&-buttons {
			display: flex;
			gap: 16px;
			align-items: center;
		}
	}

	.select-auto {
		max-width: 300px;
	}
}
</style>