<template>
	<teleport :to="teleportTo">
		<div
			v-if="shouldRender"
			:show="show"
			class="aioseo-app aioseo-modal"
			:class="[
				...cssClasses
			]"
		>
			<transition name="modal-background">
				<div
					v-if="show"
					class="modal-mask"
				/>
			</transition>

			<transition name="modal-content">
				<div
					v-if="show"
					class="modal-wrapper"
					@click.stop="maybeCloseModal"
					@contextmenu.stop
				>
					<div class="modal-container">
						<div
							v-if="!noHeader"
							class="modal-header"
						>
							<slot name="header">
								<slot name="headerTitle" />
								<button
									class="close"
									type="button"
									@click.stop="closeModal"
								>
									<svg-close
										width="14"
										height="14"
										@click="closeModal"
									/>
								</button>
							</slot>
						</div>

						<div class="modal-body">
							<slot name="body"></slot>
						</div>

						<div
							v-if="$slots.footer"
							class="modal-container__footer"
						>
							<slot name="footer"></slot>
						</div>
					</div>
				</div>
			</transition>
		</div>
	</teleport>
</template>

<script>
import {
	useRootStore
} from '$/vue/stores'

import SvgClose from '@/vue/components/common/svg/Close'
export default {
	emits : [ 'close' ],
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		SvgClose
	},
	props : {
		classes : {
			type : Array,
			default () {
				return []
			}
		},
		allowBgClose : {
			type    : Boolean,
			default : true
		},
		noHeader      : Boolean,
		allowOverflow : Boolean,
		show          : Boolean,
		modalName     : String,
		teleportTo    : {
			type : [ String, HTMLElement ],
			default () {
				return '#aioseo-modal-portal'
			}
		}
	},
	data () {
		return {
			shouldRender : true
		}
	},
	watch : {
		show (show) {
			if (show) {
				this.startListening()
				this.scrollToElement()

				this.rootStore.setActiveModal(this.modalName || this.$.uid)

				return
			}

			this.closeModal()
			this.stopListening()
		}
	},
	computed : {
		cssClasses () {
			const classes = Array.isArray(this.classes) ? [ ...this.classes ] : []

			if (this.allowOverflow) {
				classes.push('allow-overflow')
			}

			return classes
		}
	},
	methods : {
		scrollToElement () {
			const container = this.$el.getElementsByClassName ? this.$el.getElementsByClassName('component-wrapper')[0] : null
			setTimeout(() => {
				if (container) {
					container.firstChild.scrollTop = 0
				}
			}, 10)
		},
		escapeListener (event) {
			if ('Escape' === event.key && (this.modalName || this.$.uid) === this.rootStore.modals.active) {
				event.stopPropagation()
				this.closeModal()
			}
		},
		maybeCloseModal (event) {
			if (this.allowBgClose && event.target.classList.contains('modal-wrapper') && (this.modalName || this.$.uid) === this.rootStore.modals.active) {
				this.closeModal()
			}
		},
		startListening () {
			document.addEventListener('keydown', this.escapeListener, true)
		},
		stopListening () {
			document.removeEventListener('keydown', this.escapeListener)
		},
		closeModal () {
			this.$emit('close')
			this.rootStore.unsetActiveModal(this.modalName || this.$.uid)
		}
	},
	beforeMount () {
		if (!this.modalName) {
			return
		}

		this.shouldRender = !this.rootStore.modals.rendered.has(this.modalName)

		this.rootStore.modals.rendered.add(this.modalName)
	},
	beforeUnmount () {
		if (!this.modalName) {
			return
		}

		this.rootStore.modals.rendered.delete(this.modalName)
	}
}
</script>

<style lang="scss">
.aioseo-modal {
	--aioseo-modal-z-index: 9998;

	.modal-mask {
		position: fixed;
		z-index: var(--aioseo-modal-z-index);
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0, 32, 80, 0.3);
		transition: opacity 0.3s ease;
		backdrop-filter: blur(1.5px);

		@media screen and (max-width: 520px) {
			display: block;
			top: 46px;
		}
	}

	.modal-wrapper {
		position: fixed;
		z-index: var(--aioseo-modal-z-index);
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		align-content: center;
		font-family: $font-family;

		@media screen and (max-width: 520px) {
			display: block;
			height: 100%;
		}

		.modal-container {
			width: 100%;
			max-width: 840px;
			overflow-y: hidden;
			overflow-x: hidden;
			margin: 0 auto;
			background-color: #fff;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
			transition: all .3s ease;

			@media screen and (max-width: 520px) {
				width: 100%;
				max-width: 100%;
				max-height: calc(100vh - 46px);
				height: 100%;
			}

			.modal-header {
				color: $black;
				position: relative;
				top: 0;
				z-index: 15;
				padding: 0 0 0 var(--aioseo-gutter);
				height: 56px;
				font-size: 18px;
				font-weight: $font-bold;
				line-height: 1.4;
				border-bottom: 1px solid $border;
				background-color: #fff;
				display: flex;
				align-items: center;

				@media screen and (max-width: 520px) {
					padding: 15px 0 0 20px;
				}

				button.close {
					background-color: #fff;
					color: $black;
					border: none;
					cursor: pointer;
					line-height: 14px;
					margin: 0;
					opacity: 1;
					outline: none;
					padding: 0;
					position: absolute;
					right: 20px;
					top: 50%;
					transform: translate(0, -50%);
					width: 14px;
					height: 14px;

					&:focus {
						outline: 1px solid $blue;
						outline-offset: 2px;
					}

					svg.aioseo-close {
						width: inherit;
						height: inherit;
					}
				}
			}

			.modal-body {
				overflow-y: auto;
				overflow-x: hidden;
				max-height: 75vh;
				padding: 0;
				position: relative;

				.aioseo-modal-content {
					&.has-padding {
						padding: 40px;
					}
				}

				&.allow-overflow {
					overflow: visible;
				}
			}

			.modal-container__footer {
				border-top: 1px solid $border;
			}

			@media screen and (max-width: 520px) {
				.modal-body,
				.modal-body > div,
				.aioseo-modal-content,
				.aioseo-modal-content > .component-wrapper {
					height: 100%;
				}

				.aioseo-modal-content > .component-wrapper {
					display: flex;
					align-items: flex-end;
				}

				.aioseo-post-social,
				.aioseo-post-general {
					height: 100%!important;
					max-height: 100%!important;

					.mobile-radio-buttons {
						margin-bottom: 0;
					}
				}

				.aioseo-add-template-tag {
					display: none;
				}

				.tab-facebook,
				.tab-twitter {

					.aioseo-settings-row:last-of-type {
						margin-bottom: 64px!important;
						padding-bottom: 32px!important;
					}
				}
			}
		}
	}

	&.allow-overflow {
		.modal-container {
			overflow: visible;

			.modal-body {
				overflow: visible;
			}
		}
	}
}

/*
 * The following styles are auto-applied to elements with
 * transition="modal" when their visibility is toggled
 * by Vue.js.
 *
 * You can easily play with the modal transition by editing
 * these styles.
 */
.modal-background-enter-active,
.modal-background-leave-active {
	transition: opacity 0.3s ease;
}

.modal-background-enter-from,
.modal-background-leave-to {
	opacity: 0;
}

.modal-content-enter-active {
	transition: all 0.3s cubic-bezier(0.52, 0.02, 0.19, 1.02) 0.05s;
}

.modal-content-leave-active {
	transition: all 0.3s cubic-bezier(0.52, 0.02, 0.19, 1.02);
}

.modal-content-enter-from,
.modal-content-leave-to {
	opacity: 0;
	transform: scale(0.8);
}
</style>