<template>
	<div class="aioseo-mcp-v2">
		<!-- HERO -->
		<div class="mcp-v2-hero">
			<h2>{{ strings.hero.heading }}</h2>
			<p>{{ strings.hero.lede }}</p>
			<div class="mcp-v2-examples">
				<span class="label">{{ strings.hero.examplesLabel }}</span>
				<span
					v-for="chip in strings.hero.examplePrompts"
					:key="chip"
					class="example"
				>
					{{ chip }}
				</span>
			</div>
		</div>

		<!-- SETUP CARD -->
		<core-card
			slug="mcpSetup"
			:header-text="allReady ? strings.setup.complete : strings.setup.heading"
			:toggles="false"
			:no-slide="true"
		>
			<div
				v-if="!allReady"
				class="mcp-v2-summary"
			>
				<strong>{{ completedCount }}</strong> {{ strings.progress.of }} {{ steps.length }} {{ strings.progress.stepsComplete }} —
				<span class="next-action">{{ nextStepHint }}</span>
			</div>

			<div class="mcp-v2-steps">
				<div
					v-for="step in steps"
					:key="step.key"
					class="mcp-v2-step"
					:class="{
						done: step.done,
						blocking: step.blocking,
						expanded: step.expanded,
						toggleable: step.toggleable
					}"
				>
			<div
				class="mcp-v2-step-header"
				@click="toggleStep(step)"
			>
				<div class="mcp-v2-step-number">
					<svg-circle-check v-if="step.done" />
					<template v-else>{{ step.number }}</template>
				</div>

				<div class="mcp-v2-step-title">
					{{ step.title }}
					<span
						v-if="step.subtitle"
						class="subtle"
						v-html="step.subtitle"
					/>
				</div>

				<div
					class="mcp-v2-step-status"
					@click.stop
				>
					<!-- Step 1 inline link -->
					<button
						v-if="'abilities' === step.key && abilities.length"
						type="button"
						class="mcp-v2-link"
						@click.stop="showAbilities = !showAbilities"
					>
						{{ showAbilities ? strings.steps.hideAbilities : strings.steps.viewAbilities }}
					</button>

					<!-- Step 2 inline action -->
					<base-button
						v-if="'mcpAdapter' === step.key && !mcpAdapterDetected"
						type="blue"
						size="small"
						:loading="installingMcpAdapter"
						@click.stop="installMcpAdapter"
					>
						{{ mcpAdapterButtonLabel }}
					</base-button>

					<!-- Step 3 inline action / link -->
					<base-button
						v-if="'appPassword' === step.key && !hasAppPassword"
						type="blue"
						size="small"
						:loading="generatingAppPassword"
						@click.stop="generateAppPassword"
					>
						{{ generatingAppPassword ? strings.steps.generating : strings.steps.generateAppPassword }}
					</base-button>
					<a
						v-if="'appPassword' === step.key && hasAppPassword"
						:href="appPasswordUrl"
						target="_blank"
						rel="noopener"
						class="mcp-v2-link"
					>
						{{ strings.steps.manageInProfile }}
					</a>

					<!-- Step 4 inline action -->
					<base-button
						v-if="'test' === step.key && canRunLiveTest"
						type="blue"
						size="small"
						:loading="testing"
						@click.stop="runTest"
					>
						{{ testResult ? strings.steps.testAgain : strings.steps.testConnection }}
					</base-button>
				</div>
			</div>

			<transition-slide :active="step.expanded">
				<div class="mcp-v2-step-body">
					<!-- Step 1: abilities list (categories collapsed by default) -->
					<template v-if="'abilities' === step.key">
						<transition-slide :active="showAbilities">
							<div class="mcp-v2-abilities">
								<div
									v-for="group in abilitiesByCategory"
									:key="group.label"
									class="mcp-v2-abilities-group"
									:class="{ open: expandedCategories[group.label] }"
								>
									<button
										type="button"
										class="mcp-v2-abilities-category-row"
										@click="toggleCategory(group.label)"
									>
										<span class="label">{{ group.label }}</span>
										<span class="count">{{ group.items.length }}</span>
										<svg-caret
											class="caret"
											:class="{ rotated: expandedCategories[group.label] }"
										/>
									</button>
									<transition-slide :active="!!expandedCategories[group.label]">
										<div class="mcp-v2-abilities-list">
											<div
												v-for="ability in group.items"
												:key="ability.name"
												class="mcp-v2-ability"
											>
												<span class="label">{{ ability.label }}</span>
												<span class="description">{{ ability.description }}</span>
											</div>
										</div>
									</transition-slide>
								</div>
							</div>
						</transition-slide>
						<p v-if="!showAbilities">
							{{ strings.steps.abilitiesDescription }}
						</p>
					</template>

					<!-- Step 2: adapter detail -->
					<template v-if="'mcpAdapter' === step.key">
						<p>{{ strings.steps.mcpAdapterDescription }}</p>
						<p
							v-if="!mcpAdapterDetected && mcpAdapterInstalled"
							class="mcp-v2-hint"
						>
							{{ strings.steps.mcpAdapterActivateHint }}
						</p>
						<p
							v-if="!mcpAdapterDetected && !mcpAdapterInstalled"
							class="mcp-v2-hint"
						>
							{{ strings.steps.mcpAdapterInstallHint }}
						</p>
						<div
							v-if="mcpAdapterInstallMessage"
							class="mcp-v2-inline-message"
							:class="{ error: mcpAdapterInstallError }"
						>
							{{ mcpAdapterInstallMessage }}
						</div>
					</template>

					<!-- Step 3: app password detail -->
					<template v-if="'appPassword' === step.key">
						<p v-html="appPasswordDescription" />

						<div
							v-if="generatedAppPassword"
							class="mcp-v2-credentials"
						>
							<div class="warning">
								<svg-circle-exclamation />
								{{ strings.steps.appPasswordShownOnce }}
							</div>

							<div class="cred-row">
								<span class="label">{{ strings.steps.username }}</span>
								<code class="value">{{ generatedAppPassword.username }}</code>
								<button
									type="button"
									class="copy-btn"
									v-clipboard:copy="generatedAppPassword.username"
									v-clipboard:success="onCopyUsername"
								>
									{{ copiedUsername ? strings.steps.copied : strings.steps.copy }}
								</button>
							</div>

							<div class="cred-row">
								<span class="label">{{ strings.steps.password }}</span>
								<code class="value">{{ generatedAppPassword.password }}</code>
								<button
									type="button"
									class="copy-btn"
									v-clipboard:copy="generatedAppPassword.password"
									v-clipboard:success="onCopyAppPassword"
								>
									{{ copiedAppPassword ? strings.steps.copied : strings.steps.copy }}
								</button>
							</div>

							<p class="hint">
								{{ strings.steps.appPasswordSessionHint }}
							</p>
						</div>

						<div
							v-if="appPasswordError"
							class="mcp-v2-inline-message error"
						>
							{{ appPasswordError }}
						</div>
					</template>

					<!-- Step 4: test result -->
					<template v-if="'test' === step.key">
						<p v-if="!serverVerified && !testResult">
							{{ strings.steps.testLocked }}
						</p>
						<p
							v-else-if="serverVerified && !canRunLiveTest && !testResult"
							class="mcp-v2-hint"
						>
							{{ strings.steps.testServerVerifiedHint }}
						</p>

						<div
							v-if="testResult"
							class="mcp-v2-test-result"
							:class="{ error: !testResult.ok }"
						>
							<div class="detail">
								{{ testResult.detail }}
							</div>
						</div>
					</template>
				</div>
			</transition-slide>
		</div>
			</div>
		</core-card>

		<!-- CONNECT AI CLIENT -->
		<core-card
			slug="mcpClients"
			:header-text="strings.clients.heading"
			:toggles="false"
			:no-slide="true"
		>
			<p class="mcp-v2-clients-lede">
				{{ strings.clients.lede }}
			</p>

			<div class="mcp-v2-client-tabs">
				<button
					v-for="client in clientTabs"
					:key="client.id"
					type="button"
					class="mcp-v2-client-tab"
					:class="{ active: activeClient === client.id }"
					@click="activeClient = client.id"
				>
					{{ client.label }}
				</button>
			</div>

			<div
				v-if="activeClientObj.warning"
				class="mcp-v2-client-note"
			>
				<svg-circle-exclamation />
				<span>
					<strong>{{ strings.clients.notSupportedLabel }}</strong>
					{{ activeClientObj.warning }}
				</span>
			</div>

			<template v-if="!activeClientObj.unsupported">
				<div
					v-if="generatedAppPassword"
					class="mcp-v2-password-banner"
				>
					<svg-circle-check />
					{{ strings.clients.passwordFilled }}
				</div>

				<div
					v-if="activeSnippet.path"
					class="mcp-v2-snippet-meta"
				>
					<svg
						viewBox="0 0 24 24"
						width="14"
						height="14"
						fill="none"
						stroke="currentColor"
						stroke-width="2"
						stroke-linecap="round"
						stroke-linejoin="round"
					>
						<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
						<polyline points="14 2 14 8 20 8" />
					</svg>
					<span class="meta-label">{{ activeClientObj.metaLabel }}</span>
					<span class="meta-paths">
						<code
							v-for="(path, index) in activeSnippetPaths"
							:key="index"
						>{{ path }}</code>
					</span>
				</div>

				<div class="mcp-v2-snippet-block">
					<core-code-editor
						:key="activeClient"
						:editor-id="`aioseo-mcp-v2-snippet-${activeClient}`"
						:language="activeClientObj.lang || 'json'"
						:value="activeSnippet.body"
						:readonly="true"
					/>
					<button
						type="button"
						class="copy-corner"
						v-clipboard:copy="activeSnippet.body"
						v-clipboard:success="onCopySnippet"
					>
						<svg
							viewBox="0 0 24 24"
							width="13"
							height="13"
							fill="none"
							stroke="currentColor"
							stroke-width="2"
							stroke-linecap="round"
							stroke-linejoin="round"
						>
							<rect
								x="9"
								y="9"
								width="13"
								height="13"
								rx="2"
								ry="2"
							/>
							<path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
						</svg>
						{{ copiedSnippet ? strings.clients.copied : strings.clients.copySnippet }}
					</button>
				</div>

				<ol class="mcp-v2-instruction-steps">
					<li
						v-for="(instruction, idx) in activeClientObj.instructions"
						:key="idx"
						v-html="instruction"
					/>
				</ol>
			</template>
		</core-card>
	</div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRootStore } from '@/vue/stores'

import BaseButton from '@/vue/components/common/base/Button'
import CoreCard from '@/vue/components/common/core/Card'
import CoreCodeEditor from '@/vue/components/common/core/CodeEditor'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __, sprintf } from '@/vue/plugins/translations'

const td        = import.meta.env.VITE_TEXTDOMAIN
const rootStore = useRootStore()

const mcpData = computed(() => (rootStore.aioseo.aiInsights && rootStore.aioseo.aiInsights.mcp) || {})

const abilities             = computed(() => mcpData.value.abilities || [])
const abilitiesApiAvailable = computed(() => true === mcpData.value.abilitiesApiAvailable)
const mcpAdapterDetected    = ref(true === mcpData.value.mcpAdapterActive)
const mcpAdapterInstalled   = computed(() => true === mcpData.value.mcpAdapterInstalled)

const refreshedCount = ref(null)
const abilitiesCount = computed(() => null !== refreshedCount.value ? refreshedCount.value : abilities.value.length)

const showAbilities      = ref(false)
const expandedCategories = ref({})

const toggleCategory = label => {
	expandedCategories.value = {
		...expandedCategories.value,
		[label] : !expandedCategories.value[label]
	}
}

const abilitiesByCategory = computed(() => {
	const groups = {}
	abilities.value.forEach(ability => {
		const key = ability.category || 'general'
		if (!groups[key]) {
			groups[key] = {
				label : (ability.categoryLabel || key).replace(/^AIOSEO\s*[—–-]+\s*/, ''),
				items : []
			}
		}
		groups[key].items.push(ability)
	})
	return Object.values(groups)
})

const siteUrl = computed(() => {
	const urls = (rootStore.aioseo && rootStore.aioseo.urls) || {}
	return (urls.home || urls.mainSiteUrl || window.location.origin || '').replace(/\/+$/, '')
})

const currentUsername = computed(() => {
	const user = (rootStore.aioseo && rootStore.aioseo.user) || {}
	return user.login || '<your-username>'
})

const appPasswordUrl = computed(() => siteUrl.value + '/wp-admin/profile.php#application-passwords-section')

const generatingAppPassword = ref(false)
const generatedAppPassword  = ref(null)
const appPasswordError      = ref('')

const hasAppPassword = computed(() => true === mcpData.value.hasAppPassword || null !== generatedAppPassword.value)

const restNonce             = computed(() => (rootStore.aioseo && rootStore.aioseo.nonce) || '')
const aioseoRestBase        = computed(() => siteUrl.value + '/wp-json/aioseo/v1')
const abilitiesListEndpoint = computed(() => siteUrl.value + '/wp-json/wp-abilities/v1/abilities')

const installingMcpAdapter     = ref(false)
const mcpAdapterInstallMessage = ref('')
const mcpAdapterInstallError   = ref(false)

const buildHeaders = () => {
	const headers = { Accept: 'application/json' }
	if (restNonce.value) {
		headers['X-WP-Nonce'] = restNonce.value
	}
	return headers
}

const fetchAbilities = async () => {
	try {
		const res = await fetch(abilitiesListEndpoint.value, {
			credentials : 'same-origin',
			headers     : buildHeaders()
		})
		if (!res.ok) {
			return
		}
		const data = await res.json()
		if (!Array.isArray(data)) {
			return
		}
		refreshedCount.value = data.filter(a => {
			const name = a && (a.name || a.id || '')
			return 'string' === typeof name && 0 === name.indexOf('aioseo-')
		}).length
	} catch (e) {
		// Keep the server-rendered count.
	}
}

const installMcpAdapter = async () => {
	if (installingMcpAdapter.value) {
		return
	}
	installingMcpAdapter.value     = true
	mcpAdapterInstallMessage.value = ''
	mcpAdapterInstallError.value   = false

	try {
		const res = await fetch(aioseoRestBase.value + '/ai-agents/install-mcp-adapter', {
			method      : 'POST',
			credentials : 'same-origin',
			headers     : { ...buildHeaders(), 'Content-Type': 'application/json' },
			body        : JSON.stringify({})
		})
		const data = await res.json()
		if (res.ok && data && data.success) {
			mcpAdapterInstallMessage.value = data.message || __('MCP Adapter installed.', td)
			mcpAdapterDetected.value       = true
			await fetchAbilities()
		} else {
			mcpAdapterInstallError.value   = true
			mcpAdapterInstallMessage.value = (data && data.message) || __('Install failed.', td)
		}
	} catch (e) {
		mcpAdapterInstallError.value   = true
		mcpAdapterInstallMessage.value = __('Install failed. Check site connectivity and try again.', td)
	}

	installingMcpAdapter.value = false
}

const generateAppPassword = async () => {
	if (generatingAppPassword.value) {
		return
	}
	generatingAppPassword.value = true
	appPasswordError.value      = ''

	try {
		const res = await fetch(aioseoRestBase.value + '/ai-agents/generate-app-password', {
			method      : 'POST',
			credentials : 'same-origin',
			headers     : { ...buildHeaders(), 'Content-Type': 'application/json' },
			body        : JSON.stringify({})
		})
		const data = await res.json()
		if (res.ok && data && data.success) {
			generatedAppPassword.value = {
				username : data.username,
				password : data.password
			}
		} else {
			appPasswordError.value = (data && data.message) || __('Failed to generate an Application Password.', td)
		}
	} catch (e) {
		appPasswordError.value = __('Failed to generate an Application Password.', td)
	}

	generatingAppPassword.value = false
}

// Connection readiness is derived from the server-rendered preconditions on every page load — never persisted
// client-side. localStorage is origin-scoped, so a stored flag leaks across every subsite of a subdirectory
// multisite and survives an Application Password revocation; reading the per-site/per-load server facts avoids both.
const serverVerified = computed(() => abilitiesApiAvailable.value && 0 < abilitiesCount.value && mcpAdapterDetected.value && hasAppPassword.value)

// A live test signs in with the Application Password itself, so it can only run while we still hold the plaintext
// in memory (right after generation) — WordPress exposes the password only once and stores nothing but a hash.
const canRunLiveTest = computed(() => serverVerified.value && null !== generatedAppPassword.value)

const testing    = ref(false)
const testResult = ref(null)

// A failed live test overrides the server view for this session; otherwise the server preconditions decide.
const verified = computed(() => testResult.value ? testResult.value.ok : serverVerified.value)

const runTest = async () => {
	if (testing.value || !canRunLiveTest.value) {
		return
	}
	testing.value = true

	const start = Date.now()
	try {
		// credentials: 'omit' so no admin cookie can satisfy the request — this exercises the Application Password
		// exactly as an AI client would. A cookie/nonce request passes even after the password has been revoked.
		const credential = window.btoa(generatedAppPassword.value.username + ':' + generatedAppPassword.value.password)
		const res = await fetch(abilitiesListEndpoint.value, {
			credentials : 'omit',
			headers     : {
				Accept        : 'application/json',
				Authorization : 'Basic ' + credential
			}
		})
		const data    = res.ok ? await res.json() : null
		const elapsed = Date.now() - start

		if (res.ok && Array.isArray(data)) {
			const aioseoAbilities = data.filter(a => {
				const name = a && (a.name || a.id || '')
				return 'string' === typeof name && 0 === name.indexOf('aioseo-')
			})
			testResult.value = {
				ok     : true,
				detail : sprintf(
					// Translators: 1 - Number of abilities, 2 - Round-trip time in milliseconds.
					__('Authenticated with your Application Password and discovered %1$d AIOSEO abilities in %2$dms. Your site is ready to connect an AI client below.', td),
					aioseoAbilities.length,
					elapsed
				)
			}
		} else if (401 === res.status || 403 === res.status) {
			testResult.value = {
				ok     : false,
				detail : __('Your Application Password was rejected — it may have been revoked. Generate a new one and test again.', td)
			}
		} else {
			testResult.value = {
				ok     : false,
				detail : __('The abilities endpoint responded but the data was unexpected. Check the MCP Adapter is active and try again.', td)
			}
		}
	} catch (e) {
		testResult.value = {
			ok     : false,
			detail : __('Could not reach the abilities endpoint. Check the site is reachable and try again.', td)
		}
	}

	testing.value = false
}

const copiedAppPassword = ref(false)
const onCopyAppPassword = () => {
	copiedAppPassword.value = true
	setTimeout(() => {
		copiedAppPassword.value = false
	}, 2000)
}

const copiedUsername = ref(false)
const onCopyUsername = () => {
	copiedUsername.value = true
	setTimeout(() => {
		copiedUsername.value = false
	}, 2000)
}

const mcpAdapterButtonLabel = computed(() => {
	if (installingMcpAdapter.value) {
		return mcpAdapterInstalled.value ? strings.steps.activating : strings.steps.installing
	}
	return mcpAdapterInstalled.value ? strings.steps.activateMcpAdapter : strings.steps.installMcpAdapter
})

const appPasswordDescription = computed(() => sprintf(
	// Translators: 1 - The current user's username (bold), 2 - Opening strong tag, 3 - Closing strong tag.
	__('An Application Password lets an AI client sign in to this site as %1$s. It grants the same %2$sfull access%3$s as your user account and is generated for the currently logged-in user only. You can revoke it at any time from your profile page.', td),
	'<strong>' + currentUsername.value + '</strong>',
	'<strong>',
	'</strong>'
))

// Step machine — drives the numbered stepper, pill colours, and which one auto-expands.
const expandedKey = ref(null)

const steps = computed(() => {
	const items = [
		{
			key      : 'abilities',
			title    : strings.steps.abilitiesTitle,
			subtitle : sprintf(
				// Translators: %d - The number of registered abilities.
				__('%d SEO abilities registered with the WordPress Abilities API', td),
				abilitiesCount.value
			),
			done      : abilitiesApiAvailable.value && 0 < abilitiesCount.value,
			pillLabel : abilitiesApiAvailable.value && 0 < abilitiesCount.value
				? strings.steps.ready
				: (!abilitiesApiAvailable.value ? strings.steps.upgradeWp : strings.steps.noAbilities),
			pillClass : abilitiesApiAvailable.value && 0 < abilitiesCount.value ? 'green' : 'red',
			blocking  : !abilitiesApiAvailable.value
		},
		{
			key       : 'mcpAdapter',
			title     : strings.steps.mcpAdapterTitle,
			subtitle  : strings.steps.mcpAdapterSubtitle,
			done      : mcpAdapterDetected.value,
			pillLabel : mcpAdapterDetected.value
				? strings.steps.active
				: (mcpAdapterInstalled.value ? strings.steps.actionNeeded : strings.steps.actionNeeded),
			pillClass : mcpAdapterDetected.value ? 'green' : 'orange',
			blocking  : !mcpAdapterDetected.value
		},
		{
			key      : 'appPassword',
			title    : strings.steps.appPasswordTitle,
			subtitle : hasAppPassword.value
				? sprintf(
					// Translators: %s - The current user's username.
					__('Generated for %s', td),
					'<strong>' + currentUsername.value + '</strong>'
				)
				: strings.steps.appPasswordSubtitle,
			done      : hasAppPassword.value,
			pillLabel : hasAppPassword.value ? strings.steps.generated : strings.steps.actionNeeded,
			pillClass : hasAppPassword.value ? 'green' : 'orange',
			blocking  : !hasAppPassword.value
		},
		{
			key   : 'test',
			title : testResult.value
				? (testResult.value.ok ? strings.steps.testSuccess : strings.steps.testFailure)
				: (verified.value ? strings.steps.testReadyTitle : strings.steps.testTitle),
			subtitle : testResult.value
				? (testResult.value.ok ? strings.steps.testJustRan : strings.steps.testSubtitle)
				: (verified.value ? strings.steps.testVerifiedSubtitle : strings.steps.testSubtitle),
			done      : verified.value,
			pillLabel : verified.value ? strings.steps.verified : (testResult.value ? strings.steps.actionNeeded : strings.steps.pending),
			pillClass : verified.value ? 'green' : (testResult.value ? 'orange' : 'gray'),
			blocking  : false
		}
	]

	// First not-done step is the focus.
	const firstBlocking = items.find(step => !step.done && step.blocking)
	const firstPending  = items.find(step => !step.done)

	return items.map((step, idx) => {
		const number        = idx + 1
		const abilitiesOpen = 'abilities' === step.key && showAbilities.value
		const testShown     = 'test' === step.key && null !== testResult.value
		// Generating the password flips this step to "done", which would move focus on and collapse it — but the
		// one-time secret is only shown once, so keep it expanded for as long as we still hold the plaintext.
		const credsShown    = 'appPassword' === step.key && generatedAppPassword.value
		const isFocus       = expandedKey.value === step.key ||
			(null === expandedKey.value && step === (firstBlocking || firstPending)) ||
			abilitiesOpen ||
			credsShown ||
			testShown
		const hasBody       = !step.done ||
			credsShown ||
			('test' === step.key && serverVerified.value && !canRunLiveTest.value) ||
			abilitiesOpen ||
			testShown
		return {
			...step,
			number,
			blocking   : !step.done && step === firstBlocking,
			toggleable : hasBody,
			expanded   : isFocus && hasBody
		}
	})
})

const completedCount = computed(() => steps.value.filter(step => step.done).length)
const allReady       = computed(() => completedCount.value === steps.value.length)

const nextStepHint = computed(() => {
	const next = steps.value.find(step => !step.done)
	if (!next) {
		return ''
	}
	return sprintf(
		// Translators: %s - The title of the next setup step.
		__('next up: %s', td),
		next.title
	)
})

const toggleStep = step => {
	if (!step.toggleable) {
		return
	}
	if (expandedKey.value === step.key) {
		expandedKey.value = ''
	} else {
		expandedKey.value = step.key
	}
}

// Client snippets — payload identical to the existing Mcp.vue, with auto-substituted password when available.
const effectivePassword = computed(() => generatedAppPassword.value
	? generatedAppPassword.value.password
	: '<your-application-password>'
)

const mcpServerEntry = computed(() => ({
	command : 'npx',
	args    : [ '-y', '@automattic/mcp-wordpress-remote' ],
	env     : {
		WP_API_URL      : siteUrl.value + '/wp-json/',
		WP_API_USERNAME : currentUsername.value,
		WP_API_PASSWORD : effectivePassword.value
	}
}))

const mcpServerUrl = computed(() => siteUrl.value + '/wp-json/mcp/mcp-adapter-default-server')

const basicAuthHeader = computed(() => {
	if (!generatedAppPassword.value) {
		return '<base64-of-username:application-password>'
	}
	try {
		return window.btoa(currentUsername.value + ':' + generatedAppPassword.value.password)
	} catch (e) {
		return '<base64-of-username:application-password>'
	}
})

const claudeSnippet = computed(() => ({
	path : [
		'macOS: ~/Library/Application Support/Claude/claude_desktop_config.json',
		'Windows: %APPDATA%\\Claude\\claude_desktop_config.json'
	],
	body : JSON.stringify({
		mcpServers : {
			'aioseo-site' : mcpServerEntry.value
		}
	}, null, 2)
}))

const claudeCodeSnippet = computed(() => ({
	path : __('Terminal (Claude Code CLI)', td),
	body : [
		'claude mcp add aioseo-site \\',
		`  --transport http "${mcpServerUrl.value}" \\`,
		`  --header "Authorization: Basic ${basicAuthHeader.value}"`
	].join('\n')
}))

const cursorSnippet = computed(() => ({
	path : 'Cursor Settings → MCP → Add Server',
	body : JSON.stringify({
		name : 'AIOSEO Site',
		...mcpServerEntry.value
	}, null, 2)
}))

const geminiSnippet = computed(() => ({
	path : '~/.gemini/settings.json',
	body : JSON.stringify({
		mcpServers : {
			'aioseo-site' : mcpServerEntry.value
		}
	}, null, 2)
}))

const vscodeSnippet = computed(() => ({
	path : '.vscode/mcp.json',
	body : JSON.stringify({
		servers : {
			'aioseo-site' : {
				type    : 'http',
				url     : mcpServerUrl.value,
				headers : {
					Authorization : 'Basic ' + basicAuthHeader.value
				}
			}
		}
	}, null, 2)
}))

const chatgptSnippet = computed(() => ({
	path : '',
	body : mcpServerUrl.value
}))

const clientTabs = [
	{
		id           : 'claude-desktop',
		label        : 'Claude Desktop',
		metaLabel    : __('Save to:', td),
		instructions : [
			__('Open the file for your operating system shown above (create it if it doesn\'t exist). On Linux, use the Claude Code CLI tab instead.', td),
			__('Paste the snippet above into the file.', td),
			__('Restart Claude Desktop to load the AIOSEO abilities.', td)
		]
	},
	{
		id           : 'claude-code',
		label        : 'Claude Code CLI',
		lang         : 'shell',
		metaLabel    : __('Run in:', td),
		instructions : [
			__('Open a terminal in your project directory.', td),
			__('Paste the command above and press enter.', td),
			__('Exit Claude Code (<code>/exit</code>) and start a new session to load the AIOSEO abilities.', td)
		]
	},
	{
		id           : 'gemini-cli',
		label        : 'Gemini CLI',
		metaLabel    : __('Save to:', td),
		instructions : [
			__('Open the file shown above (create it if it doesn\'t exist).', td),
			__('Paste the snippet above into the file.', td),
			__('Restart the Gemini CLI to load the AIOSEO abilities.', td)
		]
	},
	{
		id           : 'cursor',
		label        : 'Cursor',
		metaLabel    : __('Add in:', td),
		instructions : [
			__('In Cursor, go to <strong>Settings → MCP → Add Server</strong>.', td),
			__('Paste the snippet above as a new server.', td),
			__('Enable the server in the MCP list to load the AIOSEO abilities.', td)
		]
	},
	{
		id           : 'vscode',
		label        : 'VS Code',
		metaLabel    : __('Save to:', td),
		instructions : [
			__('Create the file shown above in your workspace (or use the user-level file).', td),
			__('Paste the snippet above into the file.', td),
			__('Open the Chat view in agent mode and start the server (or trust it the first time) to load the AIOSEO abilities.', td)
		]
	},
	{
		id          : 'chatgpt',
		label       : 'ChatGPT',
		unsupported : true,
		warning     : __('ChatGPT\'s developer-mode connectors only support OAuth, which this site doesn\'t offer yet. Use Claude, Gemini, Cursor or VS Code instead.', td)
	}
]

const activeClient = ref('claude-desktop')

const activeClientObj = computed(() => clientTabs.find(c => c.id === activeClient.value) || clientTabs[0])

const activeSnippet = computed(() => {
	switch (activeClient.value) {
		case 'claude-code': return claudeCodeSnippet.value
		case 'cursor':      return cursorSnippet.value
		case 'gemini-cli':  return geminiSnippet.value
		case 'vscode':      return vscodeSnippet.value
		case 'chatgpt':     return chatgptSnippet.value
		default:            return claudeSnippet.value
	}
})

const activeSnippetPaths = computed(() => {
	const path = activeSnippet.value.path
	return Array.isArray(path) ? path : [ path ]
})

const copiedSnippet = ref(false)
const onCopySnippet = () => {
	copiedSnippet.value = true
	setTimeout(() => {
		copiedSnippet.value = false
	}, 2000)
}

const strings = {
	hero : {
		heading        : __('Let your AI assistant work on your SEO', td),
		lede           : __('Connect any MCP-compatible AI client below to read and update your SEO data from chat.', td),
		examplesLabel  : __('Try asking:', td),
		examplePrompts : [
			__('Find posts missing meta descriptions', td),
			__('Create a redirect from /old to /new', td),
			__('Summarise last month\'s Search Console performance', td),
			__('Update titles for all posts in /blog/', td)
		]
	},
	setup : {
		heading  : __('Setup', td),
		complete : __('Setup Complete', td)
	},
	progress : {
		heading       : __('Setup progress', td),
		complete      : __('Setup complete', td),
		allSet        : __('You\'re all set!', td),
		of            : __('of', td),
		stepsComplete : __('steps complete', td)
	},
	steps : {
		abilitiesTitle         : __('AIOSEO Abilities registered', td),
		abilitiesDescription   : __('SEO functions AIOSEO has registered for agents to call — read posts, audit the site, manage redirects, and more.', td),
		viewAbilities          : __('View all abilities', td),
		hideAbilities          : __('Hide abilities', td),
		showDetails            : __('Show details', td),
		hideDetails            : __('Hide details', td),
		mcpAdapterTitle        : __('MCP Adapter installed', td),
		mcpAdapterSubtitle     : __('Bridges AIOSEO abilities to the Model Context Protocol so Claude, Cursor and others can call them', td),
		mcpAdapterDescription  : __('The MCP Adapter is a free WordPress plugin that exposes registered abilities over the Model Context Protocol.', td),
		mcpAdapterInstallHint  : __('Install it to expose AIOSEO\'s SEO abilities over MCP.', td),
		mcpAdapterActivateHint : __('The plugin is installed but not active. Activate it to expose AIOSEO\'s SEO abilities over MCP.', td),
		installMcpAdapter      : __('Install MCP Adapter', td),
		activateMcpAdapter     : __('Activate MCP Adapter', td),
		installing             : __('Installing…', td),
		activating             : __('Activating…', td),
		appPasswordTitle       : __('Application password generated', td),
		appPasswordSubtitle    : __('Generate a password so an AI client can sign in to your site', td),
		appPasswordShownOnce   : __('This password will not be shown again. Copy it now.', td),
		appPasswordSessionHint : __('We\'ve also pre-filled it into the configuration snippets below for this session. Once you leave the page, the snippets will show a placeholder instead.', td),
		generateAppPassword    : __('Generate Application Password', td),
		generating             : __('Generating…', td),
		manageInProfile        : __('Manage in profile', td),
		username               : __('Username', td),
		password               : __('Password', td),
		copy                   : __('Copy', td),
		copied                 : __('Copied', td),
		testTitle              : __('Test the connection', td),
		testReadyTitle         : __('Connection ready', td),
		testSubtitle           : __('We\'ll sign in with your Application Password and confirm an AI client can reach your AIOSEO abilities.', td),
		testVerifiedSubtitle   : __('All steps are complete — your site is ready for an AI client to connect.', td),
		testJustRan            : __('Last tested just now', td),
		testLocked             : __('Available once the steps above are complete.', td),
		testServerVerifiedHint : __('Your site is set up and ready to connect an AI client. To run a live credential test, generate a new Application Password above.', td),
		testConnection         : __('Test connection', td),
		testAgain              : __('Test again', td),
		testSuccess            : __('Connection successful', td),
		testFailure            : __('Connection unsuccessful', td),
		ready                  : __('Ready', td),
		active                 : __('Active', td),
		actionNeeded           : __('Action needed', td),
		generated              : __('Generated', td),
		verified               : __('Verified', td),
		pending                : __('Pending', td),
		upgradeWp              : __('Requires WP 6.9+', td),
		noAbilities            : __('Not registered', td)
	},
	clients : {
		heading           : __('Connect to AI Client', td),
		lede              : __('Pick the AI tool you want to connect, then copy its configuration. Your application password is filled in automatically once generated.', td),
		copySnippet       : __('Copy snippet', td),
		copied            : __('Copied', td),
		passwordFilled    : __('Application password is filled in below. Just copy and paste.', td),
		notSupportedLabel : __('Not supported:', td)
	}
}
</script>

<style lang="scss">
@use 'sass:color';

.aioseo-mcp-v2 {

	// HERO — compact: one sentence + a labelled examples line
	.mcp-v2-hero {
		background: linear-gradient(135deg, #003a99 0%, #005ae0 100%);
		color: #fff;
		border-radius: 6px;
		padding: 18px 22px;
		margin-bottom: $gutter;

		h2 {
			margin: 0 0 4px;
			font-size: 17px;
			font-weight: 600;
			color: #fff;
		}

		p {
			margin: 0;
			font-size: 13px;
			opacity: 0.92;
		}
	}

	.mcp-v2-examples {
		margin-top: 10px;
		font-size: 12px;
		color: rgba(255, 255, 255, 0.85);
		display: flex;
		flex-wrap: wrap;
		align-items: baseline;
		gap: 6px 10px;

		.label {
			font-weight: $font-bold;
			letter-spacing: 0.2px;
			text-transform: uppercase;
			font-size: 11px;
			opacity: 0.7;
		}

		.example {
			font-style: italic;
			opacity: 0.9;

			&:not(:last-child)::after {
				content: ' · ';
				opacity: 0.5;
				font-style: normal;
				margin-left: 6px;
			}
		}
	}

	// SUMMARY — single line above the steps
	.mcp-v2-summary {
		padding: 4px 0 14px;
		margin-bottom: 4px;
		font-size: 13px;
		color: $placeholder-color;
		border-bottom: 1px solid $input-border;

		strong {
			color: $black;
		}

		.next-action {
			color: $black;
		}
	}

	// STEPS — rendered as rows inside the setup card, separated by hairlines
	.mcp-v2-step {
		border-bottom: 1px solid $input-border;
		transition: background 0.2s ease;

		&:last-child {
			border-bottom: none;
		}
	}

	.mcp-v2-step.blocking {
		background: rgba($orange, 0.09);
		box-shadow: inset 3px 0 0 $orange;
	}

	.mcp-v2-step-header {
		display: flex;
		align-items: center;
		gap: 16px;
		padding: 14px 20px;
	}

	.mcp-v2-step.toggleable .mcp-v2-step-header {
		cursor: pointer;
	}

	.mcp-v2-step-number {
		flex-shrink: 0;
		width: 32px;
		height: 32px;
		border-radius: 50%;
		background: $box-background;
		color: $placeholder-color;
		font-weight: $font-bold;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 13px;

		svg {
			width: 16px;
			height: 16px;
			color: #fff;
		}
	}

	.mcp-v2-step.done .mcp-v2-step-number {
		background: $green;
		color: #fff;
	}

	.mcp-v2-step.blocking .mcp-v2-step-number {
		background: $orange;
		color: #fff;
	}

	.mcp-v2-step-title {
		flex: 1;
		min-width: 0;
		font-size: 15px;
		font-weight: $font-bold;
		color: $black;
		line-height: 1.35;

		.subtle {
			display: block;
			max-width: 560px;
			font-size: 13px;
			font-weight: 400;
			color: $placeholder-color;
			margin-top: 2px;
		}
	}

	.mcp-v2-step-status {
		display: flex;
		align-items: center;
		gap: 10px;
		flex-shrink: 0;
		margin-left: auto;
	}

	.mcp-v2-pill {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		padding: 3px 10px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: $font-bold;
		line-height: 1.5;

		svg {
			width: 13px;
			height: 13px;
		}

		&.green {
			background: rgba($green, 0.12);
			color: $green;
		}
		&.orange {
			background: rgba($orange, 0.15);
			color: color.adjust($orange, $lightness: -5%);
		}
		&.red {
			background: rgba($red, 0.12);
			color: $red;
		}
		&.gray {
			background: $box-background;
			color: $placeholder-color;
		}
	}

	.mcp-v2-link {
		background: none;
		border: none;
		padding: 0;
		color: $blue;
		font-size: 13px;
		text-decoration: underline;
		cursor: pointer;
	}

	.mcp-v2-step-body {
		padding: 0 20px 18px 68px;
		font-size: 13px;
		color: $placeholder-color;
		line-height: 1.55;

		p {
			margin: 0 0 10px;

			&:last-child {
				margin-bottom: 0;
			}
		}
	}

	.mcp-v2-hint {
		color: $black;
		font-weight: $font-bold;
	}

	.mcp-v2-inline-message {
		margin-top: 8px;
		font-size: 13px;
		color: $black;

		&.error {
			color: $red;
		}
	}

	// Abilities — collapsed category rows, expand on click to reveal items
	.mcp-v2-abilities {
		margin-top: 4px;
		background: $box-background;
		border: 1px solid $input-border;
		border-radius: 6px;
		overflow: hidden;
	}

	.mcp-v2-abilities-group {
		border-bottom: 1px solid $input-border;

		&:last-child {
			border-bottom: none;
		}
	}

	.mcp-v2-abilities-category-row {
		display: flex;
		align-items: center;
		gap: 10px;
		width: 100%;
		padding: 10px 14px;
		background: none;
		border: none;
		cursor: pointer;
		text-align: left;

		&:hover {
			background: rgba($blue, 0.04);
		}

		.label {
			flex: 1;
			font-weight: $font-bold;
			font-size: 13px;
			color: $black;
		}

		.count {
			padding: 1px 8px;
			border-radius: 999px;
			background: rgba($blue, 0.1);
			color: $blue;
			font-size: 11px;
			font-weight: $font-bold;
		}

		.caret {
			width: 12px;
			height: 12px;
			color: $placeholder-color;
			transition: transform 0.15s ease;

			&.rotated {
				transform: rotate(180deg);
			}
		}
	}

	.mcp-v2-abilities-list {
		padding: 0 14px 12px;
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.mcp-v2-ability {
		display: flex;
		flex-direction: column;
		gap: 2px;
		padding: 8px 12px;
		background: #fff;
		border: 1px solid $border;
		border-radius: 4px;

		.label {
			font-weight: $font-bold;
			font-size: 13px;
			color: $black;
		}

		.description {
			color: $placeholder-color;
			font-size: 12px;
			line-height: 1.45;
		}
	}

	// Credentials
	.mcp-v2-credentials {
		margin-top: 12px;
		padding: 14px;
		background: rgba($orange, 0.08);
		border: 1px solid rgba($orange, 0.4);
		border-radius: 6px;

		.warning {
			display: flex;
			align-items: center;
			gap: 8px;
			color: color.adjust($orange, $lightness: -5%);
			font-weight: $font-bold;
			font-size: 13px;
			margin-bottom: 12px;

			svg {
				width: 15px;
				height: 15px;
				flex-shrink: 0;
			}
		}

		.cred-row {
			display: grid;
			grid-template-columns: 100px 1fr auto;
			gap: 10px;
			align-items: center;
			margin-bottom: 6px;

			&:last-of-type {
				margin-bottom: 0;
			}
		}

		.label {
			font-size: 11px;
			font-weight: $font-bold;
			color: $placeholder-color;
			text-transform: uppercase;
			letter-spacing: 0.3px;
		}

		.value {
			font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
			background: #fff;
			border: 1px solid $input-border;
			padding: 6px 10px;
			border-radius: 4px;
			font-size: 13px;
			color: $black;
			word-break: break-all;
		}

		.copy-btn {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: #fff;
			border: 1px solid $input-border;
			color: $black;
			padding: 5px 10px;
			border-radius: 4px;
			font-size: 12px;
			cursor: pointer;

			&:hover {
				border-color: $blue;
				color: $blue;
			}
		}

		.hint {
			margin: 12px 0 0;
			font-size: 12px;
			color: $placeholder-color;
		}
	}

	// Test result
	.mcp-v2-test-result {
		background: rgba($green, 0.08);
		border: 1px solid rgba($green, 0.35);
		border-radius: 6px;
		padding: 14px 18px;
		margin-top: 8px;

		&.error {
			background: rgba($red, 0.08);
			border-color: rgba($red, 0.35);
		}

		.header {
			display: flex;
			align-items: center;
			gap: 8px;
			font-weight: $font-bold;
			font-size: 14px;
			color: $green;
			margin-bottom: 6px;

			svg {
				width: 16px;
				height: 16px;
			}
		}

		&.error .header {
			color: $red;
		}

		.detail {
			font-size: 13px;
			color: $placeholder-color;
		}
	}

	// Connect AI Client
	.mcp-v2-clients-lede {
		margin: 0 0 16px;
		color: $placeholder-color;
		font-size: 13px;
	}

	.mcp-v2-client-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-bottom: 16px;
	}

	.mcp-v2-client-tab {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 7px 14px;
		border: 1px solid $input-border;
		border-radius: 999px;
		background: #fff;
		color: $black;
		font-size: 13px;
		cursor: pointer;
		position: relative;
		transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;

		&:hover {
			border-color: $blue;
		}

		&.active {
			background: $blue;
			border-color: $blue;
			color: #fff;
		}

	}

	.mcp-v2-password-banner {
		display: flex;
		align-items: center;
		gap: 10px;
		background: rgba($green, 0.08);
		border: 1px solid rgba($green, 0.3);
		border-radius: 6px;
		padding: 10px 14px;
		font-size: 13px;
		margin-bottom: 12px;
		color: $green;

		svg {
			width: 16px;
			height: 16px;
			flex-shrink: 0;
		}
	}

	.mcp-v2-client-note {
		display: flex;
		gap: 10px;
		align-items: flex-start;
		background: rgba($orange, 0.08);
		border: 1px solid rgba($orange, 0.35);
		border-radius: 6px;
		padding: 12px 14px;
		font-size: 13px;
		color: color.adjust($orange, $lightness: -10%);
		margin-bottom: 16px;

		svg {
			width: 16px;
			height: 16px;
			flex-shrink: 0;
			color: $orange;
			margin-top: 1px;
		}

		strong {
			margin-right: 4px;
		}
	}

	.mcp-v2-snippet-meta {
		display: flex;
		align-items: flex-start;
		gap: 8px;
		padding: 10px 14px;
		background: $box-background;
		border: 1px solid $input-border;
		border-bottom: none;
		border-radius: 6px 6px 0 0;
		font-size: 13px;
		color: $placeholder-color;

		svg {
			flex-shrink: 0;
		}

		.meta-label {
			font-weight: $font-bold;
		}

		.meta-paths {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		code {
			font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
			font-size: 12px;
			color: $black;
			word-break: break-all;
		}
	}

	.mcp-v2-snippet-block {
		position: relative;
		margin-bottom: 16px;

		.aioseo-code-editor {
			border-radius: 0 0 6px 6px;
			overflow: hidden;

			.CodeMirror {
				border-radius: 0 0 6px 6px;
			}
		}

		.copy-corner {
			position: absolute;
			top: 8px;
			right: 8px;
			z-index: 2;
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: rgba(255, 255, 255, 0.92);
			border: 1px solid $input-border;
			color: $black;
			padding: 5px 10px;
			border-radius: 4px;
			font-size: 12px;
			cursor: pointer;

			&:hover {
				border-color: $blue;
				color: $blue;
			}
		}
	}

	.mcp-v2-instruction-steps {
		counter-reset: mcp-step;
		list-style: none;
		padding: 0;
		margin: 0;

		li {
			counter-increment: mcp-step;
			padding-left: 30px;
			position: relative;
			margin-bottom: 6px;
			font-size: 13px;
			color: $placeholder-color;

			&:last-child {
				margin-bottom: 0;
			}

			&::before {
				content: counter(mcp-step);
				position: absolute;
				left: 0;
				top: 0;
				width: 20px;
				height: 20px;
				border-radius: 50%;
				background: rgba(0, 0, 0, 0.12);
				color: $black;
				font-weight: $font-bold;
				font-size: 11px;
				display: inline-flex;
				align-items: center;
				justify-content: center;
			}

			code, em {
				font-style: normal;
			}

			code {
				font-family: 'SF Mono', 'Monaco', monospace;
				font-size: 12px;
				background: $box-background;
				padding: 1px 5px;
				border-radius: 3px;
				color: $black;
			}

			em {
				color: $black;
			}
		}
	}
}
</style>