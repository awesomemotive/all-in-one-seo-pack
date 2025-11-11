<template>
	<div>
		<div class="aioseo-tab-headers">
			<div
				v-for="(tab, index) in tabs"
				:key="index"
			>
				<button
					@click="setActiveTab(index)"
					:class="[
						'aioseo-tab-header',
						{ 'aioseo-tab-header-active' : activeTab === index }
					]"
				>
					{{ tab.title }}
				</button>
				<span
					v-if="index !== tabs.length - 1"
					class="aioseo-tab-separator"
				>
					{{ separator }}
				</span>
			</div>
		</div>

		<slot name="tab-header-bottom"></slot>

		<div class="aioseo-tab-content">
			<core-tab-item
				v-for="(tab, index) in tabs"
				:key="index"
				:class="[
					'aioseo-tab-item',
					{ 'aioseo-tab-item-active' : activeTab === index }
				]"
				:active="activeTab === index"
			>
				<template #content>
					<component :is="tab.component"></component>
				</template>
			</core-tab-item>
		</div>
	</div>
  </template>
<script>
import CoreTabItem from './TabItem'

export default {
	components : {
		CoreTabItem
	},
	props : {
		tabs : {
			type     : Array,
			required : true
		},
		separator : {
			type     : String,
			required : false,
			default  : '|'
		}
	},
	data () {
		return {
			activeTab : 0
		}
	},
	methods : {
		setActiveTab (index) {
			this.activeTab = index
		}
	}
}
</script>

<style lang="scss">
.aioseo-tab-separator {
	margin: 0px 7px;
	color: $input-border;
}

.aioseo-tab-headers {
	display : flex;

	.aioseo-tab-header {
		border : none;
		outline : none;
		background : transparent;
		cursor : pointer;
		color: $blue;
		font-size: 14px;
		font-weight: 400;
		padding: 0px;

		&:not(:last-child) {
			margin-right : 5px;
		}

		&:not(:first-child) {
			margin-left : 5px;
		}

		&-active {
			color: $black;
			font-weight: 700;
		}
	}
}
</style>