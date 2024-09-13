<template>
	<component
		:is="tagName"
		:class="[
			...cssClasses
		]"
	>
		<transition
			:name="transition"
			:enter-active-class="enterActiveClass"
			:leave-active-class="leaveActiveClass"
			@after-leave="doDestroy"
		>
			<div
				ref="popper"
				:class="rootClass"
				v-show="!disabled && showPopper"
			>
				<div
					ref="content"
				>
					<slot>{{ content }}</slot>
				</div>
			</div>
		</transition>

		<div ref="reference">
			<slot name="reference" />
		</div>
	</component>
</template>

<script>
import Popper from 'popper.js'

const on = (element, event, handler) => {
	if (element && event && handler) {
		document.addEventListener
			? element.addEventListener(event, handler, false)
			: element.attachEvent('on' + event, handler)
	}
}
const off = (element, event, handler) => {
	if (element && event) {
		document.removeEventListener
			? element.removeEventListener(event, handler, false)
			: element.detachEvent('on' + event, handler)
	}
}

export default {
	emits : [ 'show', 'hide', 'created', 'documentClick' ],
	props : {
		tagName : {
			type    : String,
			default : 'span'
		},
		trigger : {
			type      : String,
			default   : 'hover',
			validator : value => -1 < [
				'clickToOpen',
				'click', // Same as clickToToggle, provided for backwards compatibility.
				'clickToToggle',
				'hover',
				'focus'
			].indexOf(value)
		},
		delayOnMouseOver : {
			type    : Number,
			default : 10
		},
		delayOnMouseOut : {
			type    : Number,
			default : 10
		},
		disabled           : Boolean,
		content            : String,
		enterActiveClass   : String,
		leaveActiveClass   : String,
		boundariesSelector : String,
		reference          : {},
		forceShow          : {
			type    : Boolean,
			default : false
		},
		dataValue : {
			default : null
		},
		appendToBody : Boolean,
		visibleArrow : {
			type    : Boolean,
			default : true
		},
		transition : {
			type    : String,
			default : ''
		},
		stopPropagation : Boolean,
		preventDefault  : Boolean,
		options         : {
			type : Object,
			default () {
				return {}
			}
		},
		rootClass : {
			type    : String,
			default : ''
		},
		classes : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			referenceElm     : null,
			popperJS         : null,
			showPopper       : false,
			currentPlacement : '',
			popperOptions    : {
				placement    : 'bottom',
				computeStyle : {
					gpuAcceleration : false
				}
			}
		}
	},
	watch : {
		showPopper (value) {
			if (value) {
				this.$emit('show', this)
				if (this.popperJS) {
					this.popperJS.enableEventListeners()
				}
				this.updatePopper()
			} else {
				if (this.popperJS) {
					this.popperJS.disableEventListeners()
				}
				this.$emit('hide', this)
			}
		},
		forceShow : {
			handler (value) {
				this[value ? 'doShow' : 'doClose']()
			},
			immediate : true
		},
		disabled (value) {
			if (value) {
				this.showPopper = false
			}
		}
	},
	computed : {
		cssClasses () {
			const classes = Array.isArray(this.classes) ? this.classes : []

			if (this.showPopper) {
				classes.push('popper-active')
			}

			return Array.isArray(this.classes) ? this.classes : []
		}
	},
	methods : {
		doToggle (event) {
			if (this.stopPropagation) {
				event.stopPropagation()
			}
			if (this.preventDefault) {
				event.preventDefault()
			}
			if (!this.forceShow) {
				this.showPopper = !this.showPopper
			}
		},
		doShow () {
			this.showPopper = true
		},
		doClose () {
			this.showPopper = false
		},
		doDestroy () {
			if (this.showPopper) {
				return
			}
			if (this.popperJS) {
				this.popperJS.destroy()
				this.popperJS = null
			}
			if (this.appendedToBody) {
				this.appendedToBody = false
				document.body.removeChild(this.popper.parentElement)
			}
		},
		createPopper () {
			this.$nextTick(() => {
				if (this.visibleArrow) {
					this.appendArrow(this.popper)
				}
				if (this.appendToBody && !this.appendedToBody) {
					this.appendedToBody = true
					document.body.appendChild(this.popper.parentElement)
				}
				if (this.popperJS && this.popperJS.destroy) {
					this.popperJS.destroy()
				}
				if (this.boundariesSelector) {
					const boundariesElement = document.querySelector(this.boundariesSelector)
					if (boundariesElement) {
						this.popperOptions.modifiers = Object.assign({}, this.popperOptions.modifiers)
						this.popperOptions.modifiers.preventOverflow = Object.assign({}, this.popperOptions.modifiers.preventOverflow)
						this.popperOptions.modifiers.preventOverflow.boundariesElement = boundariesElement
					}
				}
				this.popperOptions.onCreate = () => {
					this.$emit('created', this)
					this.$nextTick(this.updatePopper)
				}
				this.popperJS = new Popper(this.referenceElm, this.popper, this.popperOptions)
			})
		},
		destroyPopper () {
			off(this.referenceElm, 'click', this.doToggle)
			off(this.referenceElm, 'mouseup', this.doClose)
			off(this.referenceElm, 'mousedown', this.doShow)
			off(this.referenceElm, 'focus', this.doShow)
			off(this.referenceElm, 'blur', this.doClose)
			off(this.referenceElm, 'mouseout', this.onMouseOut)
			off(this.referenceElm, 'mouseover', this.onMouseOver)
			off(document, 'click', this.handleDocumentClick)
			this.showPopper = false
			this.doDestroy()
		},
		appendArrow (element) {
			if (this.appendedArrow) {
				return
			}
			this.appendedArrow = true
			const arrow = document.createElement('div')
			arrow.setAttribute('x-arrow', '')
			arrow.className = 'popper__arrow'
			element.appendChild(arrow)
		},
		updatePopper () {
			this.popperJS ? this.popperJS.scheduleUpdate() : this.createPopper()
		},
		onMouseOver () {
			clearTimeout(this._timer)
			this._timer = setTimeout(() => {
				this.showPopper = true
			}, this.delayOnMouseOver)
		},
		onMouseOut () {
			clearTimeout(this._timer)
			this._timer = setTimeout(() => {
				this.showPopper = false
			}, this.delayOnMouseOut)
		},
		handleDocumentClick (e) {
			if (
				!this.$el ||
				!this.referenceElm ||
				this.elementContains(this.$el, e.target) ||
				this.elementContains(this.referenceElm, e.target) ||
				!this.popper ||
				this.elementContains(this.popper, e.target)
			) {
				return
			}
			this.$emit('documentClick', this)
			if (this.forceShow) {
				return
			}
			this.showPopper = false
		},
		elementContains (elm, otherElm) {
			if ('function' === typeof elm.contains) {
				return elm.contains(otherElm)
			}
			return false
		}
	},
	created () {
		this.appendedArrow = false
		this.appendedToBody = false
		this.popperOptions = Object.assign(this.popperOptions, this.options)
	},
	mounted () {
		this.referenceElm = this.reference || this.$refs.reference.firstElementChild
		this.popper = this.$refs.content.firstElementChild
		switch (this.trigger) {
			case 'clickToOpen':
				on(this.referenceElm, 'click', this.doShow)
				on(document, 'click', this.handleDocumentClick)
				on(document, 'touchstart', this.handleDocumentClick)
				break
			case 'click': // Same as clickToToggle, provided for backwards compatibility.
			case 'clickToToggle':
				on(this.referenceElm, 'click', this.doToggle)
				on(document, 'click', this.handleDocumentClick)
				on(document, 'touchstart', this.handleDocumentClick)
				break
			case 'hover':
				on(this.referenceElm, 'mouseover', this.onMouseOver)
				on(this.popper, 'mouseover', this.onMouseOver)
				on(this.referenceElm, 'mouseout', this.onMouseOut)
				on(this.popper, 'mouseout', this.onMouseOut)
				break
			case 'focus':
				on(this.referenceElm, 'focus', this.onMouseOver)
				on(this.popper, 'focus', this.onMouseOver)
				on(this.referenceElm, 'blur', this.onMouseOut)
				on(this.popper, 'blur', this.onMouseOut)
				break
		}
	},
	unmounted () {
		this.destroyPopper()
	}
}
</script>

<style>
.popper {
	width: auto;
	background-color: #fafafa;
	color: #212121;
	text-align: center;
	padding: 2px;
	display: inline-block;
	border-radius: 3px;
	position: absolute;
	font-size: 14px;
	font-weight: normal;
	border: 1px #ebebeb solid;
	z-index: 200000;
	-moz-box-shadow: rgb(58, 58, 58) 0 0 6px 0;
	-webkit-box-shadow: rgb(58, 58, 58) 0 0 6px 0;
	box-shadow: rgb(58, 58, 58) 0 0 6px 0;
}
.popper .popper__arrow {
	width: 0;
	height: 0;
	border-style: solid;
	position: absolute;
	margin: 5px;
}
.popper[x-placement^="top"] {
	margin-bottom: 5px;
}
.popper[x-placement^="top"] .popper__arrow {
	border-width: 5px 5px 0 5px;
	border-color: #fafafa transparent transparent transparent;
	bottom: -5px;
	left: calc(50% - 5px);
	margin-top: 0;
	margin-bottom: 0;
}
.popper[x-placement^="bottom"] {
	margin-top: 5px;
}
.popper[x-placement^="bottom"] .popper__arrow {
	border-width: 0 5px 5px 5px;
	border-color: transparent transparent #fafafa transparent;
	top: -5px;
	left: calc(50% - 5px);
	margin-top: 0;
	margin-bottom: 0;
}
.popper[x-placement^="right"] {
	margin-left: 5px;
}
.popper[x-placement^="right"] .popper__arrow {
	border-width: 5px 5px 5px 0;
	border-color: transparent #fafafa transparent transparent;
	left: -5px;
	top: calc(50% - 5px);
	margin-left: 0;
	margin-right: 0;
}
.popper[x-placement^="left"] {
	margin-right: 5px;
}
.popper[x-placement^="left"] .popper__arrow {
	border-width: 5px 0 5px 5px;
	border-color: transparent transparent transparent #fafafa;
	right: -5px;
	top: calc(50% - 5px);
	margin-left: 0;
	margin-right: 0;
}
</style>