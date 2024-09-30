<template>
	<div class="aioseo-tools-import-export">
		<grid-row>
			<grid-column
				md="6"
				:class="['import-aiseo']"
			>
				<import-aioseo />
			</grid-column>

			<grid-column
				md="6"
				:class="['export-settings']"
			>
				<export-settings />
			</grid-column>

			<grid-column
				md="6"
				:class="['import-others']"
			>
				<import-others />
			</grid-column>

			<grid-column
				md="6"
				:class="['export-contents']"
			>
				<export-contents />
			</grid-column>
		</grid-row>

		<grid-row>
			<grid-column>
				<backup-settings />
			</grid-column>
		</grid-row>
	</div>
</template>

<script>
import BackupSettings from '../partials/BackupSettings'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import ExportSettings from '../partials/ExportSettings'
import ExportContents from '../partials/ExportContents'
import ImportAioseo from '../partials/ImportAioseo'
import ImportOthers from '../partials/ImportOthers'
export default {
	components : {
		BackupSettings,
		GridColumn,
		GridRow,
		ExportSettings,
		ExportContents,
		ImportAioseo,
		ImportOthers
	}
}
</script>

<style lang="scss">
.aioseo-tools-import-export {
	position: relative;

	.aioseo-row {

		.aioseo-card {
			margin: 0;
			flex: 1;
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

	@media screen and (max-width: 911px) {
		.import-aiseo {
			order: 1;
		}

		.import-others {
			order: 2;
		}

		.export-settings {
			order: 3;
		}

		.export-contents {
			order: 4;
		}
	}
}
</style>