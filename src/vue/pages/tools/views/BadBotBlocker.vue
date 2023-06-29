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
				badBotBlocker         : this.$t.__('Bad Bot Blocker', this.$td),
				blockBadBotsHttp      : this.$t.__('Block Bad Bots using HTTP', this.$td),
				blockReferralSpamHttp : this.$t.__('Block Referral Spam using HTTP', this.$td),
				trackBlockedBots      : this.$t.__('Track Blocked Bots', this.$td),
				useCustomBlocklists   : this.$t.__('Use Custom Blocklists', this.$td),
				userAgentBlocklist    : this.$t.__('User Agent Blocklist', this.$td),
				refererBlockList      : this.$t.__('Referer Blocklist', this.$td),
				blockedBotsLog        : this.$t.__('Blocked Bots Log', this.$td),
				logLocation           : this.$t.sprintf(
					// Translators: 1 - The location of the log file.
					this.$t.__('The log for the blocked bots is located here: %1$s', this.$td),
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