<template>
	<div class="aioseo-link-assistant">
		<core-main
			:page-name="strings.pageName"
			:exclude-tabs="excludedTabs"
			:showTabs="'post-report' !== $route.name"
		>
			<component :is="$route.name" />
		</core-main>
	</div>
</template>

<script>
import {
	useLinkAssistantStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { RequiresActivation } from '@/vue/mixins/RequiresActivation'
import { RequiresUpdate } from '@/vue/mixins/RequiresUpdate'
import CoreMain from '@/vue/components/common/core/main/Index'
import CoreProcessingPopup from '@/vue/components/common/core/ProcessingPopup'
import DomainsReport from './AIOSEO_VERSION/DomainsReport'
import LinksReport from './AIOSEO_VERSION/LinksReport'
import Overview from './Overview'
import PostReport from './AIOSEO_VERSION/PostReport'
import Settings from './AIOSEO_VERSION/Settings'
export default {
	setup () {
		return {
			linkAssistantStore : useLinkAssistantStore()
		}
	},
	components : {
		CoreMain,
		CoreProcessingPopup,
		DomainsReport,
		LinksReport,
		Overview,
		PostReport,
		Settings
	},
	mixins : [ RequiresActivation, RequiresUpdate ],
	data () {
		return {
			strings : {
				pageName : this.$t.__('Link Assistant', this.$td)
			}
		}
	},
	computed : {
		excludedTabs () {
			const excludedTabs = (
				!addons.isActive('aioseo-link-assistant')
					? this.getExcludedActivationTabs('aioseo-link-assistant')
					: this.getExcludedUpdateTabs('aioseo-link-assistant')
			) || []
			excludedTabs.push('post-report')
			return excludedTabs
		}
	},
	mounted () {
		window.aioseoBus.$on('changes-saved', () => {
			this.linkAssistantStore.getMenuData()
		})

		if (
			this.$isPro &&
			100 !== this.linkAssistantStore.suggestionsScan.percent &&
			addons.isActive('aioseo-link-assistant') &&
			!addons.requiresUpgrade('aioseo-link-assistant') &&
			addons.hasMinimumVersion('aioseo-link-assistant')
		) {
			this.linkAssistantStore.pollSuggestionsScan()
		}
	}
}
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

.aioseo-link-suggestions div.links-bottom {
	margin: 16px 0 0 0;
}

.aioseo-link-assistant-overview {
	position: relative;
}
</style>