<template>
	<div class="aioseo-writing-assistant__sidebar-competitor">
		<div class="aioseo-writing-assistant__sidebar-competitor-upper">
			<div>
				<div class="aioseo-writing-assistant__sidebar-competitor-position">
					#{{ props.competitor.googlePosition }}
				</div>
			</div>
			<div class="aioseo-writing-assistant__sidebar-competitor-title">
				<a
					:href="props.competitor.url"
					target="_blank"
					:title="props.competitor.url"
				>
					{{ props.competitor.title || props.competitor.url }}
				</a>
			</div>
			<div class="aioseo-writing-assistant__sidebar-competitor-favicon">
				<favicon :domain="props.competitor.url" />
			</div>
		</div>
		<div class="aioseo-writing-assistant__sidebar-competitor-bottom">
			<div>
				<grade-round
					:grade="props.competitor.wordCount ? props.competitor.grade : '-'"
				/>
			</div>
			<div class="aioseo-writing-assistant__sidebar-competitor-bottom-wordCount">
				<div
					class="aioseo-writing-assistant__sidebar-competitor-bottom-label"
					style="white-space: nowrap"
				>
					{{ strings.wordCount }}
				</div>
				<div class="aioseo-writing-assistant__sidebar-competitor-bottom-value">
					{{ props.competitor.wordCount || '-' }}
				</div>
			</div>
			<div class="aioseo-writing-assistant__sidebar-competitor-bottom-readability">
				<div class="aioseo-writing-assistant__sidebar-competitor-bottom-label">
					{{ strings.readabilityGrade }}
				</div>
				<div class="aioseo-writing-assistant__sidebar-competitor-bottom-value">
					{{ props.competitor.wordCount ? props.competitor.readabilityGrade : '-' }}
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import GradeRound from '../GradeRound'
import Favicon from '../competitor/Favicon'
import { decodeSpecialChars } from '@/vue/utils/helpers.js'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	competitor : Object
})

const strings = {
	readabilityGrade : __('readability', td),
	wordCount        : __('words', td),
	grade            : __('Grade', td)
}

props.competitor.title = decodeSpecialChars(props.competitor.title)

</script>

<style lang="scss">
.aioseo-writing-assistant__sidebar-competitor {
	--letter-spacing: 3px;
	background: $white;
	border-radius: 3px;
	display: flex;
	position: relative;
	padding: 10px;
	flex-direction: column;
	gap: 10px;

	&-position {
		background: $background;
		padding: 2px 0;
		border-radius: 30px;
		font-weight: 700;
		font-size: 14px;
		width: 33px;
		text-align: center;
	}

	&-upper {
		display: flex;
		gap: 10px;
	}

	&-title {
		font-weight: 700;
		font-size: 14px;
		flex-grow: 1;

		a {
			text-decoration: none;
		}
	}

	&-favicon {
		img {
			width: 20px;
			height: auto;
			max-width: none;
		}
	}

	&-bottom {
		display: flex;
		font-size: 13px;
		gap: 10px;

		&-value {
			font-weight: 700;
		}

		&-wordCount {
			flex: 1;
		}

		&-readability {
			text-align: right;
		}
	}
}
</style>