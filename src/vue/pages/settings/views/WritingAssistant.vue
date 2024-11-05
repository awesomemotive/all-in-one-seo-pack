<template>
	<div class="aioseo-writing-assistant-settings">
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
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'writingAssistantHowToUse', true)"
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
import { ref } from 'vue'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useOptionsStore,
	useWritingAssistantStore,
	useWritingAssistantSettingsStore
} from '@/vue/stores'

import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __ } from '@/vue/plugins/translations'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseButton from '@/vue/components/common/base/Button'
import BaseSelect from '@/vue/components/common/base/Select'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import SeoBoostLogin from '@/vue/standalone/writing-assistant/views/partials/authenticate/Seoboost'
import DisconnectModal from '@/vue/standalone/writing-assistant/views/partials/authenticate/DisconnectModal'

const td = import.meta.env.VITE_TEXTDOMAIN

const optionsStore = useOptionsStore()
const writingAssistantStore = useWritingAssistantStore()
const writingAssistantSettingsStore = useWritingAssistantSettingsStore()
writingAssistantSettingsStore.hookSaveUserOptions()

const showDisconnectModal = ref(false)
const openLogin = ref(false)

const strings = {
	tooltip             : __('Integrate seamlessly with SEOBoost via AIOSEO to supercharge your WordPress content.', td),
	description         : __('Integrate seamlessly with SEOBoost via AIOSEO to supercharge your WordPress content.', td),
	writingAssistant    : __('Writing Assistant', td),
	seoBoost            : __('SEOBoost CTA', td),
	postType            : __('Post Types', td),
	includeAllPostTypes : __('Include all post types', td),
	selectPostTypes     : __('Select the post types you want the Writing Assistant to be available.', td),
	connect             : __('Connect to SEOBoost', td),
	connectExisting     : __('Connect to an Existing Account', td),
	connectDescription  : __('Connect to SEOBoost to get access to the Writing Assistant.', td),
	loggedIn            : __('You\'re connected to SEOBoost!', td),
	logoutButton        : __('Disconnect', td),
	reportDefaults      : __('Report Defaults', td),
	defaultCountry      : __('Default Region', td),
	defaultLanguage     : __('Default Language', td),
	or                  : __('OR', td),
	createAccount       : __('Create a Free Account', td),
	connectNow          : __('Now Connect to Your SEOBoost Account', td)
}

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