<template>
	<div class="aioseo-writing-assistant__keyword-uses">
		<span
			:class="{
			'in-bounds' : keyword.lowerCount && keyword.contentCount >= keyword.lowerCount
		}"
		>
			{{ keyword.contentCount ?? 0 }}
		</span>
		<span
			class="aioseo-writing-assistant__keyword-uses-typical"
			v-if="keyword.lowerCount"
		>({{ strings.typical }}:
			<template v-if="keyword.lowerCount < keyword.higherCount">
				{{ keyword.lowerCount }}-{{ keyword.higherCount }})
			</template>
			<template v-if="keyword.lowerCount === keyword.higherCount">
				{{ keyword.higherCount }})
			</template>
		</span>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	props : {
		keyword : Object
	},
	data () {
		return {
			strings : {
				typical : __('Typical', td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__keyword {
	&-uses {
		font-weight: 700;

		.in-bounds {
			color: $green;
		}

		&-typical {
			font-weight: normal;
			color: $placeholder-color;
			padding-left: 3px;
		}
	}
}
</style>