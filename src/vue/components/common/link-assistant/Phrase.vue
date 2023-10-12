<template>
	<div
		class="aioseo-link-assistant-phrase"
		@mouseover="$emit('mouseOver')"
		@mouseleave="$emit('mouseLeave')"
	>
		<span class="phrase">
			<span
				class="first"
				v-if="firstPart"
			>
				{{ firstPart }}
			</span>

			<core-tooltip
				v-if="tooltip"
				type="action"
			>
				<span
					class="anchor"
					@click="maybeOpenLink"
				>{{ linkAnchor }}</span>

				<template #tooltip>
					<a class="tooltip-url" :href="url" target="_blank">{{ url }}</a>
				</template>
			</core-tooltip>

			<div v-if="!tooltip">
				<span class="anchor">{{ linkAnchor }}</span>
			</div>

			<span
				class="last"
				v-if="lastPart"
			>
				{{ lastPart }}
			</span>
		</span>

		<slot name="icons" />
	</div>
</template>

<script>
import { escapeRegex } from '@/vue/utils/regex'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
export default {
	components : {
		CoreTooltip
	},
	props : {
		phrase : {
			type     : String,
			required : true
		},
		phraseHtml : {
			type     : String,
			required : true
		},
		anchor : {
			type     : String,
			required : true
		},
		url : {
			type     : String,
			required : true
		},
		tooltip : {
			type     : Boolean,
			required : false,
			default  : true
		},
		clickableAnchor : {
			type : Boolean,
			default () {
				return false
			}
		}

	},
	computed : {
		partPattern () {
			const sanitizedAnchor = this.decodeHtmlEntities(this.anchor)
			const escapedAnchor   = escapeRegex(sanitizedAnchor)
			return new RegExp(`(.*)(<t?a[^>]*>.*${escapedAnchor}.*</t?a>)(.*)`, 'i')
		},
		firstPart () {
			return this.decodeHtmlEntities(this.stripTags(this.getPart(1)).trimStart())
		},
		linkAnchor () {
			// Returns the anchor, but with the right casing (the value on the model is always lowercase).
			return this.decodeHtmlEntities(this.stripTags(this.getPart(2)))
		},
		lastPart () {
			return this.decodeHtmlEntities(this.stripTags(this.getPart(3)).trimEnd())
		}
	},
	methods : {
		getPart (groupIndex) {
			const sanitizedPhraseHtml = this.decodeHtmlEntities(this.stripTags(this.phraseHtml, true))
			const matches             = sanitizedPhraseHtml.match(this.partPattern)
			if (!matches) {
				return ''
			}

			return matches[groupIndex]
		},
		// This method strips tags but preserves their inner text.
		stripTags (string, keepLinks = false) {
			// First, replace br tags with spaces.
			string = string.replace(/<br\s?\/?>/gi, ' ')

			if (keepLinks) {
				return string.replace(/<(?!a\s)(?!\/a)[^>]*>/gi, '')
			}
			return string.replace(/<[^>]*>/gi, '')
		},
		maybeOpenLink () {
			if (this.clickableAnchor) {
				window.open(this.url, '_blank').focus()
			}
		},
		decodeHtmlEntities (html) {
			// TODO: Figure out why we're stripping scripts inside the regular decodeHTMLEntities() helper method.
			const txt = document.createElement('textarea')
			txt.innerHTML = html
			return txt.value
		}
	}
}
</script>

<style lang="scss">
	.aioseo-app .aioseo-link-assistant-phrase {
		.aioseo-tooltip {
			display: inline;
			margin: 0;
			font-size: 0px;
			line-height: 22px;

			span {
				font-size: 14px;
				white-space: pre-wrap;
			}

			.anchor {
				text-decoration: underline;
				color: $blue;
				white-space: pre-wrap;

				&:hover {
					cursor: pointer;
				}
			}

			a.tooltip-url {
				text-decoration: underline;
				color: white;

				&:hover {
					text-decoration: none;
				}
			}
		}

		.icons {
			margin-left: 10px;
		}
	}
</style>