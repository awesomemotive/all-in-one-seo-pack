<template>
	<div class="aioseo-link-assistant">
		<core-main
			:page-name="strings.pageName"
			:exclude-tabs="excludedTabs"
			:showTabs="'post-report' !== route.name"
		>
			<component :is="getRoute" />
		</core-main>
	</div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { computed, onMounted } from 'vue'

import {
	useLinkAssistantStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

import { useRequiresActivation } from '@/vue/composables/RequiresActivation'
import { useRequiresUpdate } from '@/vue/composables/RequiresUpdate'

import CoreMain from '@/vue/components/common/core/main/Index'
import DomainsReport from './AIOSEO_VERSION/DomainsReport'
import LinksReport from './AIOSEO_VERSION/LinksReport'
import Overview from './Overview'
import PostReport from './AIOSEO_VERSION/PostReport'
import Settings from './AIOSEO_VERSION/Settings'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const route  = useRoute()
const router = useRouter()
const getRoute = computed(() => {
	if ('overview' === route.name) {
		return Overview
	}
	if ('domains-report' === route.name) {
		return DomainsReport
	}
	if ('links-report' === route.name) {
		return LinksReport
	}
	if ('post-report' === route.name) {
		return PostReport
	}
	if ('settings' === route.name) {
		return Settings
	}

	return Overview
})

const {
	getExcludedActivationTabs
} = useRequiresActivation()

const {
	getExcludedUpdateTabs
} = useRequiresUpdate()

const strings = {
	pageName : __('Link Assistant', td)
}

const excludedTabs = computed(() => {
	const tabs = (
		!addons.isActive('aioseo-link-assistant')
			? getExcludedActivationTabs(router, 'aioseo-link-assistant')
			: getExcludedUpdateTabs(router, 'aioseo-link-assistant')
	) || []
	tabs.push('post-report')
	return tabs
})

const linkAssistantStore = useLinkAssistantStore()
const rootStore          = useRootStore()
onMounted(() => {
	window.aioseoBus.$on('changes-saved', () => {
		linkAssistantStore.getMenuData()
	})

	if (
		rootStore.isPro &&
		100 !== linkAssistantStore.suggestionsScan.percent &&
		addons.isActive('aioseo-link-assistant') &&
		!addons.requiresUpgrade('aioseo-link-assistant') &&
		addons.hasMinimumVersion('aioseo-link-assistant')
	) {
		linkAssistantStore.pollSuggestionsScan()
	}
})
</script>

<style lang="scss">
.aioseo-link-assistant {
	.aioseo-wp-table {

		tbody {
			th.check-column,
			td {
				font-size: 14px;
			}
		}

		tr.edit-row .edit-row-content {
			padding: 0;

			.wrapper .border {
				padding: 0;
			}

		}

		button.toggle-row-button {
			display: flex;
			width: 26px;
			height: 26px;
			padding: 0;
			justify-content: center;
			align-items: center;
			background-color: white;
			border: 1px solid $gray;
			border-radius: 3px;
			margin-left: auto;

			&:hover {
				cursor: pointer;
				background-color: #F9F9FA;
			}

			&.active {
				background-color: $blue;
				border: 1px solid $blue;

				&:hover {
					background-color: $blue2;
				}

				svg {
					&.aioseo-caret {
						color: white;
						transform: rotate(-180deg);
					}
				}
			}

			svg {
				&.aioseo-caret {
					margin: 0;
					height: 24px;
					width: 24px;
					transform: rotate(-90deg);
					color: $placeholder-color;
					transition: transform 0.3s;
				}
			}
		}

		.row-actions {
			a {
				&.delete-all-links {
					color: $red;
				}
			}
		}

		svg {
			margin-right: 11.22px;

			&.aioseo-link-external {
				color: $blue;
				width: 12px;
				height: 12px;
			}
			&.aioseo-new-page {
				color: $black2;
				width: 9.33px;
				height: 11.67px;
			}
			&.aioseo-close {
				margin: 0;
			}
		}
	}

	.aioseo-tooltip .popper.action {
		a.tooltip-url {
			white-space: normal !important;
		}
	}

	#the-list .aioseo-wp-table.link-assistant-inner-table {
		.wp-table table {
			border: 0;

			thead {
				tr:last-child {
					th.manage-column,
					td.manage-column {
						border-bottom: 1px solid #c3c4c7 !important;
					}
				}
			}

			tbody {

				tr {
					// These are set in WordPress Core but we need to reset them for the inner table; otherwise the row actions for all rows show on hover.
					.row-actions {
						position: relative;
					}

					&:hover {
						.row-actions {
							position : static;
						}
					}
				}
			}

			tbody {

				.aioseo-tooltip {
					display: inline-block;
					margin-left: 0;
				}

				svg {
					&.aioseo-trash {
						width: 18px;
						height: 22px;
						color: $placeholder-color;
						cursor: pointer;
						transition: color 0.1s ease;
						margin-top: 2px;

						&:hover {
							color: $red;
						}
					}
				}
			}
		}
	}

	.aioseo-tabs.link-tabs {
		border-bottom: 1px solid $border;

		.aioseo-mobile-tabs {
			margin-left: 10px;
		}

		.var-tab {
			display: flex;
			align-items: center;
			column-gap: 11.75px;

			svg {
				display: inline-block;
				width: 14px;
				height: 14px;
				margin-right: 0;

				&.aioseo-link-internal-inbound,
				&.aioseo-link-internal-outbound {
					width: 17px;
					height: 17px;
					color: $green;
				}
				&.aioseo-link-external {
					width: 17px;
					height: 17px;
					color: $blue;
				}
				&.aioseo-link-affiliate {
					color: $orange;
					width: 100%;
					height: auto;
					max-height: 18px;
				}
				&.aioseo-link-suggestion {
					color: $black2-hover;
					width: 100%;
					height: auto;
					max-width: 20px;
				}
			}
		}
	}

	div.links-bottom {
		display: flex;
		flex-direction: row;
		font-size: 12px;
		line-height: 18px;
		margin: 0 16px 16px 16px;

		a {
			text-decoration: underline;

			&.link-delete {
				color: $red !important;
			}

			&:hover{
				text-decoration: none;
				cursor: pointer;
			}
		}

		.links-bottom-left {
			display: flex;
			flex: 1 1 auto;
			column-gap: 28px;
			align-items: center;
			div {
				display:flex;
				align-items: center;
				svg {
					margin-right: 10px;
				}
			}

			button {
				svg {
					margin-top: 2.5px;
					margin-right: 10px;
					width: 14px;
					height: 14px;
					color: white !important;
				}
			}

			svg.aioseo-link-external {
				margin-top: 3.5px;
				width: 11px;
				height: 11px;
				color: $blue;
			}
		}

		.links-bottom-right {
			display: flex;
			align-items: center;
		}
	}

	@media (max-width: 1115px) {
		.aioseo-tabs {
			border-bottom: 0;
		}
		.aioseo-mobile-tabs {
			.tab-dropdown {
				border-bottom: 0;
			}
			svg.aioseo-caret {
				height: 20px;
				width: 20px;
			}
		}
	}
}

.aioseo-link-assistant-overview {
	position: relative;
}
</style>