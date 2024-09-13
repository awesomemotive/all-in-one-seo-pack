<template>
	<core-blur id="aioseo-author-seo-meta-data">
		<div class="aioseo-settings-row aioseo-section-description">
			{{ strings.description }}
		</div>

		<core-settings-row
			:name="strings.enable"
		>
			<template #content>
				<base-toggle />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.alumniOf"
		>
			<template #content>
				<base-input
					size="medium"
					:placeholder="strings.alumniOf"
				/>

				<div class="aioseo-description">
					{{ strings.alumniOfDescription }}
				</div>

				<br />

				<base-input
					size="medium"
					:placeholder="strings.alumniOfUrl"
				/>

				<div class="aioseo-description">
					{{ strings.alumniOfUrlDescription }}
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.worksFor"
		>
			<template #content>
				<base-input
					size="medium"
					:placeholder="strings.worksFor"
				/>

				<div class="aioseo-description">
					{{ strings.worksForDescription }}
				</div>
			</template>
		</core-settings-row>
	</core-blur>
</template>

<script setup>

import BaseInput from '@/vue/components/common/base/Input'
import BaseToggle from '@/vue/components/common/base/Toggle'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	description            : __('The fields below provide structured information for search engines about the current author. By filling out these fields, you will enhance your online presence and improve search engine visibility. This increases the chances of your author details appearing prominently in search results, making it easier for readers, publishers, and media representatives to discover and connect with you.', td),
	enable                 : __('Enable Author Info', td),
	alumniOf               : __('Alumni Of', td),
	alumniOfDescription    : __('The school, college, or university where the author studied.', td),
	alumniOfUrl            : __('School, college, or university URL', td),
	alumniOfUrlDescription : __('The URL of the school, college, or university where the author studied.', td),
	worksFor               : __('Employer', td),
	worksForDescription    : __('The organization the author works for.', td)
}
</script>

<style lang="scss">
#aioseo-author-seo-meta-data {
	.aioseo-row.aioseo-settings-row .aioseo-description {
		margin: 12px 0;
	}
}
</style>