<template>
	<div class="aioseo-ai-usage-stats">
		<div v-if="loading" class="aioseo-loading">
			<base-loader />
		</div>

		<div v-else-if="stats" class="aioseo-stats-grid">
			<div class="aioseo-stat-card">
				<div class="aioseo-stat-icon">
					<svg-chart />
				</div>
				<div class="aioseo-stat-content">
					<div class="aioseo-stat-value">{{ stats.total || 0 }}</div>
					<div class="aioseo-stat-label">{{ strings.totalRequests }}</div>
				</div>
			</div>

			<div class="aioseo-stat-card">
				<div class="aioseo-stat-icon">
					<svg-check-circle />
				</div>
				<div class="aioseo-stat-content">
					<div class="aioseo-stat-value">{{ stats.successful || 0 }}</div>
					<div class="aioseo-stat-label">{{ strings.successfulRequests }}</div>
				</div>
			</div>

			<div class="aioseo-stat-card">
				<div class="aioseo-stat-icon">
					<svg-tokens />
				</div>
				<div class="aioseo-stat-content">
					<div class="aioseo-stat-value">{{ formatNumber(stats.tokens || 0) }}</div>
					<div class="aioseo-stat-label">{{ strings.tokensUsed }}</div>
				</div>
			</div>

			<div class="aioseo-stat-card">
				<div class="aioseo-stat-icon">
					<svg-calendar />
				</div>
				<div class="aioseo-stat-content">
					<div class="aioseo-stat-value">{{ stats.last30Days || 0 }}</div>
					<div class="aioseo-stat-label">{{ strings.last30Days }}</div>
				</div>
			</div>
		</div>

		<div v-if="stats && stats.byProvider" class="aioseo-provider-breakdown">
			<h4>{{ strings.byProvider }}</h4>
			<div class="aioseo-provider-stats">
				<div
					v-for="(count, provider) in stats.byProvider"
					:key="provider"
					class="aioseo-provider-stat"
				>
					<span class="aioseo-provider-name">{{ capitalizeFirst(provider) }}</span>
					<span class="aioseo-provider-count">{{ count }}</span>
				</div>
			</div>
		</div>

		<div v-if="stats && stats.byAction" class="aioseo-action-breakdown">
			<h4>{{ strings.byAction }}</h4>
			<div class="aioseo-action-stats">
				<div
					v-for="(count, action) in stats.byAction"
					:key="action"
					class="aioseo-action-stat"
				>
					<span class="aioseo-action-name">{{ formatActionName(action) }}</span>
					<span class="aioseo-action-count">{{ count }}</span>
				</div>
			</div>
		</div>

		<div class="aioseo-stats-actions">
			<base-button
				type="gray"
				size="small"
				@click="refreshStats"
				:loading="refreshing"
			>
				{{ strings.refresh }}
			</base-button>

			<base-button
				type="blue"
				size="small"
				@click="viewHistory"
			>
				{{ strings.viewHistory }}
			</base-button>
		</div>
	</div>
</template>

<script>
import { defineComponent } from 'vue'
import BaseLoader from '@/vue/components/common/base/Loader'
import BaseButton from '@/vue/components/common/base/Button'
import SvgChart from '@/vue/components/common/svg/Chart'
import SvgCheckCircle from '@/vue/components/common/svg/circle/Check'
import SvgTokens from '@/vue/components/common/svg/Tokens'
import SvgCalendar from '@/vue/components/common/svg/Calendar'

export default defineComponent({
	components: {
		BaseLoader,
		BaseButton,
		SvgChart,
		SvgCheckCircle,
		SvgTokens,
		SvgCalendar
	},
	data() {
		return {
			loading: true,
			refreshing: false,
			stats: null,
			strings: {
				totalRequests: this.$t.__('Total Requests', this.$td),
				successfulRequests: this.$t.__('Successful', this.$td),
				tokensUsed: this.$t.__('Tokens Used', this.$td),
				last30Days: this.$t.__('Last 30 Days', this.$td),
				byProvider: this.$t.__('Usage by Provider', this.$td),
				byAction: this.$t.__('Usage by Action', this.$td),
				refresh: this.$t.__('Refresh', this.$td),
				viewHistory: this.$t.__('View Full History', this.$td)
			}
		}
	},
	mounted() {
		this.loadStats()
	},
	methods: {
		async loadStats() {
			try {
				const response = await fetch(`${window.aioseo.urls.restUrl}aioseo/v1/ai/stats`, {
					headers: {
						'X-WP-Nonce': window.aioseo.nonce
					}
				})

				const data = await response.json()
				if (data.success) {
					this.stats = data.stats
				}
			} catch (error) {
				console.error('Failed to load AI stats:', error)
			} finally {
				this.loading = false
			}
		},
		async refreshStats() {
			this.refreshing = true
			await this.loadStats()
			this.refreshing = false
		},
		viewHistory() {
			// Navigate to history page or open modal
			window.location.href = window.aioseo.urls.aio.aiHistory
		},
		formatNumber(num) {
			return new Intl.NumberFormat().format(num)
		},
		capitalizeFirst(str) {
			return str.charAt(0).toUpperCase() + str.slice(1)
		},
		formatActionName(action) {
			return action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
		}
	}
})
</script>

<style lang="scss" scoped>
.aioseo-ai-usage-stats {
	.aioseo-loading {
		text-align: center;
		padding: 40px;
	}

	.aioseo-stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 16px;
		margin-bottom: 24px;
	}

	.aioseo-stat-card {
		background: #f9f9f9;
		border-radius: 8px;
		padding: 20px;
		display: flex;
		align-items: center;
		gap: 16px;
		border: 1px solid #e0e0e0;

		.aioseo-stat-icon {
			width: 48px;
			height: 48px;
			background: #0073aa;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;

			svg {
				width: 24px;
				height: 24px;
				fill: currentColor;
			}
		}

		.aioseo-stat-content {
			flex: 1;
		}

		.aioseo-stat-value {
			font-size: 24px;
			font-weight: 700;
			color: #23282d;
			margin-bottom: 4px;
		}

		.aioseo-stat-label {
			font-size: 13px;
			color: #666;
		}
	}

	.aioseo-provider-breakdown,
	.aioseo-action-breakdown {
		margin-bottom: 24px;

		h4 {
			font-size: 14px;
			font-weight: 600;
			margin-bottom: 12px;
			color: #23282d;
		}
	}

	.aioseo-provider-stats,
	.aioseo-action-stats {
		display: grid;
		gap: 8px;
	}

	.aioseo-provider-stat,
	.aioseo-action-stat {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 12px 16px;
		background: #fff;
		border: 1px solid #e0e0e0;
		border-radius: 4px;

		span:first-child {
			font-weight: 500;
			color: #23282d;
		}

		span:last-child {
			font-weight: 600;
			color: #0073aa;
		}
	}

	.aioseo-stats-actions {
		display: flex;
		gap: 12px;
		justify-content: flex-end;
		padding-top: 16px;
		border-top: 1px solid #e0e0e0;
	}
}
</style>
