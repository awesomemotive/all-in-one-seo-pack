<template>
	<div class="aioseo-tools-import-export">
		<core-blur>
			<grid-row>
				<grid-column
					md="6"
				>
					<import-aioseo />
					<import-others />
				</grid-column>

				<grid-column
					md="6"
				>
					<export-settings />
				</grid-column>
			</grid-row>

			<grid-row>
				<grid-column>
					<backup-settings />
				</grid-column>
			</grid-row>
		</core-blur>

		<cta
			:cta-link="links.getPricingUrl('network-tools', 'import-export', null, 'liteUpgrade')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('network-tools', 'import-export', 'liteUpgrade')"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				<required-plans :core-feature="[ 'tools', 'network-tools-import-export' ]" />
				{{ strings.networkDatabaseToolsDescription }}
			</template>
		</cta>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import BackupSettings from '../partials/BackupSettings'
import CoreBlur from '@/vue/components/common/core/Blur'
import Cta from '@/vue/components/common/cta/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import ExportSettings from '../partials/ExportSettings'
import ImportAioseo from '../partials/ImportAioseo'
import ImportOthers from '../partials/ImportOthers'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		RequiredPlans,
		BackupSettings,
		CoreBlur,
		Cta,
		GridColumn,
		GridRow,
		ExportSettings,
		ImportAioseo,
		ImportOthers
	},
	data () {
		return {
			links,
			strings : {
				ctaHeader : sprintf(
					// Translators: 1 - "PRO".
					__('Network Tools is a %1$s Feature', td),
					'PRO'
				),
				ctaButtonText                   : __('Unlock Network Tools', td),
				networkDatabaseToolsDescription : __('Unlock network-level tools to manage all your sites from one easy-to-use location. Migrate data or create backups without the need to visit each dashboard.', td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-import-export {
	position: relative;

	.aioseo-row {

		.aioseo-card {
			margin: 0;
		}

		.aioseo-col {
			display: flex;
			flex-direction: column;
			gap: var(--aioseo-gutter);
		}

		+ .aioseo-row {
			margin-top: var(--aioseo-gutter);
		}
	}

	.export-post-types {
		--aioseo-gutter: 12px;
	}
}
</style>