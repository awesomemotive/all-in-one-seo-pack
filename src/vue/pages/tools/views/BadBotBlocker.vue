<template>
	<div class="aioseo-tools-bad-bot-blocker">
		<core-card
			slug="badBotBlocker"
			:header-text="strings.badBotBlocker"
		>
			<core-settings-row
				:name="strings.blockBadBotsHttp"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.deprecated.tools.blocker.blockBots"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.blockReferralSpamHttp"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.deprecated.tools.blocker.blockReferer"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.deprecated.tools.blocker.blockBots || optionsStore.options.deprecated.tools.blocker.blockReferer"
				:name="strings.useCustomBlocklists"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.deprecated.tools.blocker.custom.enable"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.deprecated.tools.blocker.blockBots && optionsStore.options.deprecated.tools.blocker.custom.enable"
				:name="strings.userAgentBlocklist"
			>
				<template #content>
					<base-textarea
						:minHeight="200"
						:maxHeight="200"
						v-model="optionsStore.options.deprecated.tools.blocker.custom.bots"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.deprecated.tools.blocker.blockReferer && optionsStore.options.deprecated.tools.blocker.custom.enable"
				:name="strings.refererBlockList"
			>
				<template #content>
					<base-textarea
						:minHeight="200"
						:maxHeight="200"
						v-model="optionsStore.options.deprecated.tools.blocker.custom.referer"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.deprecated.tools.blocker.blockBots || optionsStore.options.deprecated.tools.blocker.blockReferer"
				:name="strings.trackBlockedBots"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.deprecated.tools.blocker.track"
					/>

					<core-alert
						type="blue"
						v-html="strings.logLocation"
					/>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import BaseTextarea from '@/vue/components/common/base/Textarea'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		BaseTextarea,
		CoreAlert,
		CoreCard,
		CoreSettingsRow
	},
	data () {
		return {
			strings : {
				badBotBlocker         : __('Bad Bot Blocker', td),
				blockBadBotsHttp      : __('Block Bad Bots using HTTP', td),
				blockReferralSpamHttp : __('Block Referral Spam using HTTP', td),
				trackBlockedBots      : __('Track Blocked Bots', td),
				useCustomBlocklists   : __('Use Custom Blocklists', td),
				userAgentBlocklist    : __('User Agent Blocklist', td),
				refererBlockList      : __('Referer Blocklist', td),
				blockedBotsLog        : __('Blocked Bots Log', td),
				logLocation           : sprintf(
					// Translators: 1 - The location of the log file.
					__('The log for the blocked bots is located here: %1$s', td),
					'<br><a href="' + this.rootStore.aioseo.urls.blockedBotsLogUrl + '" target="_blank">' + this.rootStore.aioseo.urls.blockedBotsLogUrl + '</a>'
				)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-bad-bot-blocker {
	.aioseo-alert {
		margin-top: 10px;
	}
}
</style>