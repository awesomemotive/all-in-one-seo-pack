<template>
	<div
		class='components-panel__body'
		:class="[componentClass, { 'is-opened': localOpenedState }, { 'aioseo-headline-analyzer-panel-has-icon': hasIcon }, iconColor]">
		<h2 class='components-panel__body-title'>
			<button
				@click="toggleAccordion"
				type="button"
				:aria-expanded="localOpenedState ? 'true' : 'false'"
				class="components-button components-panel__body-toggle">
				<span aria-hidden="true">
					<svg
						viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg"
						width="24"
						height="24"
						class="components-panel__arrow"
						aria-hidden="true"
						focusable="false">
							<path d="M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"></path>
					</svg>
				</span>
					<div class='title-flex'>
						<span>{{ title }}</span>
						<slot name="icon" v-if="hasIcon"></slot>
						<slot name="extraTxt" v-if="hasExtraTxt"></slot>
					</div>
			</button>
		</h2>
		<div class='components-panel__row' v-if="localOpenedState">
			<slot />
		</div>
	</div>
</template>

<script>
export default {
	props : {
		title : {
			type     : String,
			default  : 'Accordion',
			required : true
		},
		openedState : {
			type    : Boolean,
			default : true
		},
		componentClass : {
			type    : String,
			default : ''
		},
		hasIcon : {
			type    : Boolean,
			default : false
		},
		hasExtraTxt : {
			type    : Boolean,
			default : false
		},
		iconColor : {
			type    : String,
			default : ''
		}
	},
	data () {
		return {
			localOpenedState : this.openedState
		}
	},
	methods : {
		toggleAccordion () {
			this.localOpenedState = !this.localOpenedState
		}
	}
}
</script>

<style scoped lang="scss">
	.components-panel__body-title {
		transition: background-color 0.2s ease;
		background-color: white;
		margin-top: 0;

		.title-flex {
			display: flex;
			justify-content: space-between;
			align-items: center;
			width: 100%;
		}
	}
	.components-panel__body-title:hover {
		background-color: #e8e8eb;
	}
	.components-panel__body {
		svg.components-panel__arrow {
			transform: rotate(-270deg);
		}

		&.is-opened {
			svg.components-panel__arrow {
				transform: rotate(0deg);
			}
			.components-panel__body-title {
				margin-bottom: 16px !important;
			}
		}

		.components-panel__body-title {
			margin-bottom: 0 !important;
		}
	}

</style>