<template>
	<div
		v-if="buttonTarget"
		class="aioseo-card-footer"
	>
		<router-link :to="{ name : buttonTarget, query : query }">
			<span v-html="strings.openReport" />
		</router-link>
	</div>
</template>

<script>
import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	props : {
		buttonTarget : String,
		query        : Object
	},
	data () {
		return {
			strings : {
				openReport : sprintf(
					// Translators: 1 - Right arrow.
					__('Open Report %1$s', td),
					'<span>&rarr;</span>'
				)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-card-footer {
	margin-top: auto;
	padding-top: var(--aioseo-gutter);
	text-align: left;

	// Align it to footer.
	position: absolute;
	bottom: var(--aioseo-gutter);
	left: var(--aioseo-gutter);
	width: calc( 100% - 40px );

	a {
		text-decoration: none;
		color: $blue;
		font-weight: 700;
		font-size: 14px;
		list-style: 125%;
	}
}
</style>