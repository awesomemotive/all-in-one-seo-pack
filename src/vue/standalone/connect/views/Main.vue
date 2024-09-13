<template>
	<div>
		<core-header
			:upgradeBar="false"
			:actions="false"
		/>

		<grid-container>
			<core-card
				no-slide
				:toggles="false"
				slug="connect"
				:header-text="strings.connecting"
			>
				<div class="animated-dannie">
					<core-animated-dannie
						mascot="lab"
					/>
				</div>
			</core-card>
		</grid-container>
	</div>
</template>

<script>
import CoreAnimatedDannie from '@/vue/components/common/core/AnimatedDannie'
import CoreCard from '@/vue/components/common/core/Card'
import CoreHeader from '@/vue/components/common/core/Header'
import GridContainer from '@/vue/components/common/grid/Container'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreAnimatedDannie,
		CoreCard,
		CoreHeader,
		GridContainer
	},
	data () {
		return {
			strings : {
				connecting : __('Connecting...', td)
			}
		}
	}
}
</script>

<style lang="scss">
html,
body {
	margin: 0;
	padding: 0;
	background: $background;
}

.aioseo-header {
	position: initial;
}

.aioseo-container.main-content {
	min-height: calc(100vh - 263px);

	.aioseo-card {
		.animated-dannie {
			display: flex;
			align-items: center;
			justify-content: center;
			height: 388px;
		}
	}
}

footer {
	border-top: 1px solid $border;
	color: #999;
	font-size: 13px;
	margin-top: 50px;
	padding: 30px 20px;
	text-align: center;

	p {
		margin: 0 0 14px;
		font-size: 13px !important;
	}

	ul {
		display: inline-block;
		list-style: none;
		margin: 0;
		padding: 0;

		li {
			display: inline-block;
			margin: 0 13px;
		}
	}
}
</style>