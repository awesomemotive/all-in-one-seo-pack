<template>
	<div class="v3-migration-info aioseo-description">
		<ul class="info-items">
			<li
				v-for="(infoItemObject, index) in infoItems"
				:key="index"
			>
				<span v-if="infoItemObject.value">{{infoItemObject.label}}</span>
				<span v-if="infoItemObject.value">{{infoItemObject.value}}</span>
			</li>
		</ul>
	</div>
</template>

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import { DateTime } from 'luxon'

export default {
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	computed : {
		infoItems () {
			return [
				{
					label : 'Migrated Version',
					value : this.optionsStore.internalOptions.internal.migratedVersion
				},
				{
					label : 'First Activated',
					value : 0 !== this.optionsStore.internalOptions.internal.firstActivated
						? DateTime.fromMillis(this.optionsStore.internalOptions.internal.firstActivated * 1000).toFormat('MMMM d, yyyy')
						: false
				}
			]
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .v3-migration-info {
	border-top: 1px solid $border;
	padding-top: 10px;
	margin-top: 15px;

	ul.info-items {
		margin: 0;

		li {
			display: flex;

			span:first-of-type {
				flex: 0 0 130px;
			}
		}
	}
}
</style>