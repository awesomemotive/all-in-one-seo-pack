<template>
	<core-modal
		v-if="searchStatisticsStore.fetching"
		:classes="[ 'aioseo-fetching-data' ]"
		noHeader
	>
		<template #body>
			<svg-circle-information
				width="90"
				height="90"
			/>

			<div class="aioseo-fetching-data-title">{{ strings.title }}</div>

			<div class="aioseo-fetching-data-description">{{ strings.description }}</div>

			<core-loader dark />
		</template>
	</core-modal>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgCircleInformation from '@/vue/components/common/svg/circle/Information'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreLoader,
		CoreModal,
		SvgCircleInformation
	},
	data () {
		return {
			strings : {
				title       : this.$t.__('Refreshing data', this.$td),
				description : this.$t.__('Loading new report data...', this.$td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-fetching-data {
	text-align: center;

	.modal-mask .modal-wrapper .modal-container {
		max-width: 400px;

		.modal-body {
			padding: 20px;
		}
	}

	.aioseo-circle-information {
		color: $blue2;
	}

	&-title {
		font-size: 24px;
		font-weight: 600;
		margin: 20px 0 10px;
	}

	&-description {
		font-size: 16px;
		margin-bottom: 20px;
	}

	.aioseo-loading-spinner {
		position: relative;
		margin: 0 auto;
	}
}
</style>