<template>
	<span
		class="aioseo-writing-assistant__keyword-heading-presence"
		:title="headingPresenceTitle"
	>
		<span
			class="aioseo-writing-assistant__keyword-heading-presence-exists"
			v-if="'n/a' !== keyword.headingPresenceLevel"
		>
			<span
				class="aioseo-writing-assistant__keyword-heading-presence-icon"
				:class="'aioseo-writing-assistant__keyword-heading-presence-icon-' + keyword.headingPresenceLevel"
			></span>{{	headingPresenceString ? headingPresenceString : strings.headingPresenceLevels[keyword.headingPresenceLevel] || strings.headingPresenceLevels.noPresence	}}
		</span>

		<span v-else>{{ strings.headingPresenceLevels['n/a'] }}</span>

	</span>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	props : {
		keyword               : Object,
		headingPresenceString : String
	},
	data () {
		return {
			strings : {
				headingPresenceLevels : {
					high  : __('High', td),
					mid   : __('Moderate', td),
					low   : __('Low', td),
					'n/a' : __('n/a', td)
				},
				headingPresence : __('Heading presence', td)
			}
		}
	},
	computed : {
		headingPresenceTitle () {
			return this.strings.headingPresence + ': ' + this.strings.headingPresenceLevels[this.keyword?.headingPresenceLevel] || this.strings.headingPresenceLevels['n/a']
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__keyword-heading-presence {
	color: $placeholder-color;

	&-exists {
		color: $black2;
		font-weight: 700;
		padding: 2px 5px;
		border-radius: 3px;
		font-size: 12px;
	}

	&-icon {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background: $blue;
		display: inline-block;
		margin-right: 9px;
		opacity: 0.25;

		&-high {
			opacity: 1
		}

		&-mid {
			opacity: 0.5;
		}
	}
}
</style>