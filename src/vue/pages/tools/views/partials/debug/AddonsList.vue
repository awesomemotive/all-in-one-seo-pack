<template>
	<div class="aioseo-addons-list">
		<template v-if="activeAddons.length > 0">
			<div
				v-for="(addon, index) in activeAddons"
				:key="index"
			>
				<base-checkbox
					size="medium"
					v-model="selectedAddons[addon.sku]"
					:disabled="disabled"
				>
					{{ addon.name }}
				</base-checkbox>
			</div>

			<div>
				<base-button
					type="blue"
					size="medium"
					@click="$emit('update', selectedSkus), $emit('addons-selected', selectedAddonsNames)"
					:loading="loading"
					:disabled="disabled || selectedSkus.length === 0"
				>
					{{ strings.runAction }}
				</base-button>
			</div>
		</template>

		<b v-else>
			{{ strings.noAddons }}
		</b>
	</div>
</template>

<script>
import {
	useAddonsStore,
	useRootStore
} from '@/vue/stores'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'update' ],
	setup () {
		return {
			rootStore   : useRootStore(),
			addonsStore : useAddonsStore()
		}
	},
	components : {
		BaseCheckbox
	},
	props : {
		loading  : Boolean,
		disabled : Boolean
	},
	data () {
		return {
			selectedAddons : {},
			strings        : {
				runAction : __('Run Action', td),
				noAddons  : __('There are no active addons at the moment.', td)
			}
		}
	},
	computed : {
		activeAddons () {
			return this.addonsStore.addons.filter(addon => addon.isActive)
		},
		selectedSkus () {
			return Object.keys(this.selectedAddons).filter(sku => this.selectedAddons[sku])
		},
		selectedAddonsNames () {
			return this.activeAddons.filter(addon => this.selectedSkus.includes(addon.sku)).map(addon => addon.name)
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-addons-list {
	display: grid;
	row-gap: 12px;
}
</style>