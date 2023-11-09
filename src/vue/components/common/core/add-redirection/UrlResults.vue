<template>
	<div class="aioseo-add-redirection-url-results" >
		<ul>
			<li
				v-for="(result, index) in results"
				:key="index"
				@click="$emit('set-url', result.link)"
			>
				<span>
					<div class="option">
						<div class="option-title">
							<div
								v-html="getOptionTitle(result.label)"
							/>

							<core-pro-badge
								v-if="'publish' !== result.status"
							>
								{{ getStatusLabel(result.status) }}
							</core-pro-badge>
						</div>

						<div class="option-details">
							<span>{{ result.link }}</span>
						</div>
					</div>

					<a
						class="option-permalink"
						:href="result.link"
						target="_blank"
						@click.stop="() => {}"
					>
						<svg-external />
					</a>
				</span>
			</li>
		</ul>
	</div>
</template>

<script>
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import SvgExternal from '@/vue/components/common/svg/External'
export default {
	emits      : [ 'set-url' ],
	components : {
		CoreProBadge,
		SvgExternal
	},
	props : {
		results : {
			type     : Array,
			required : true
		},
		url : String
	},
	data () {
		return {
			strings : {
				DRAFT   : this.$t.__('DRAFT', this.$td),
				PENDING : this.$t.__('PENDING', this.$td),
				FUTURE  : this.$t.__('FUTURE', this.$td)
			}
		}
	},
	methods : {
		getOptionTitle (title) {
			// First, sanitize the title and URL. We must do this as we'll be using the result of this method in a v-html directive.
			title     = title.replace(/<\/?[^>]+(>|$)/g, '')
			const url = this.url.replace(/<\/?[^>]+(>|$)/g, '')

			const regex = new RegExp(`(${url})`, 'gi')
			return title.replace(regex, '<span class="search-term">$1</span>')
		},
		getStatusLabel (status) {
			switch (status.toLowerCase()) {
				case 'draft':
					return this.strings.DRAFT
				case 'future':
					return this.strings.FUTURE
				case 'pending':
					return this.strings.PENDING
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-add-redirection-url-results {
	display: flex;
	position: absolute;
	background: #fff;
	width: 100%;
	max-height: 300px;
	overflow: auto;
	border: 1px solid $input-border;
	border-radius: 3px;
	z-index: 50;
	left: 1px;
	margin-top: -9px;

	ul {
		display: inline-block;
		max-width: 100%;
		list-style: none;
		padding: 0;
		margin: 0;
		min-width: 100%;
		vertical-align: top;

		li {
			margin: 0;
			border-bottom: 1px solid $border;
			display: block;

			&:last-of-type {
				border-bottom: none;
			}

			> span {
				color: $black;
				font-weight: 700;
				font-size: 16px;
				white-space: normal;
				line-height: 1.4;
				display: flex;
				padding: 12px;
				min-height: 40px;
				text-decoration: none;
				text-transform: none;
				vertical-align: middle;
				position: relative;
				cursor: pointer;

				&:hover {
					background-color: $inline-background;

					.option-title {
						color: $blue;
					}
				}
			}
		}
	}

	.option {
		flex: 1;

		.option-title {
			font-weight: 500;
			display: flex;
			font-size: 16px;
			color: $black;

			> div {
				&:first-of-type {
					display: inline-block;
				}

				margin-right: 5px;
			}

			.search-term {
				font-weight: 700;
			}
		}

		.option-details {
			display: flex;
			align-items: center;
			font-size: 14px;
			color: $placeholder-color;

			span {
				margin-right: 15px;
			}
		}
	}

	.option-permalink {
		display: flex;
		align-items: center;

		svg.aioseo-external {
			width: 15px;
			height: 15px;
			color: $black2;
		}
	}
}
</style>