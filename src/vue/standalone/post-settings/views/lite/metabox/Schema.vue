<template>
	<div class="aioseo-tab-content aioseo-post-schema">
		<core-card
			slug="postSettingsSchema"
			:noSlide="true"
			:hideHeader="true"
		>
			<core-settings-row
				:name="strings.schemaInUse"
			>
				<template #content>
					<div class="graphs">
						<graph-card
							v-if="postEditorStore.currentPost.schema.default.graphName && postEditorStore.currentPost.schema.default.isEnabled"
							:defaultGraph="postEditorStore.currentPost.schema.default.graphName"
						>
							<template #buttons>
								<core-tooltip :offset="getDefaultSchemaTooltipOffset()">
									<base-button
										class="small no-hover"
										type="gray"
									>
										<svg-eye />
									</base-button>

									<template #tooltip>
										<span v-safe-html="strings.defaultGraphTooltip" />
									</template>
								</core-tooltip>
							</template>
						</graph-card>
					</div>

					<div class="buttons">
						<core-alert
							class="no-graphs"
							type="yellow"
							v-if="!postEditorStore.currentPost.schema.default.graphName || !postEditorStore.currentPost.schema.default.isEnabled"
						>
							{{strings.noGraphs}}
						</core-alert>

						<base-button
							class="medium"
							type="blue"
							@click="schemaStore.modalOpen = true"
						>
							{{strings.generateSchema}}
						</base-button>

						<base-button
							class="medium"
							type="gray"
							@click="schemaStore.modalOpen = true"
						>
							{{strings.validateSchema}}
						</base-button>
					</div>

					<cta-modal
						:show="schemaStore.modalOpen"
						@close="schemaStore.modalOpen = false"
						modal-name="schema-cta-modal"
					/>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	usePostEditorStore,
	useSchemaStore
} from '@/vue/stores'

import { isPageBuilderEditor } from '@/vue/utils/context'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CtaModal from '../partials-schema/CtaModal'
import GraphCard from '../../partials/GraphCard'
import SvgEye from '@/vue/components/common/svg/Eye'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			schemaStore     : useSchemaStore(),
			GLOBAL_STRINGS,
			links
		}
	},
	components : {
		CoreAlert,
		CoreCard,
		CoreSettingsRow,
		CoreTooltip,
		CtaModal,
		GraphCard,
		SvgEye
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			strings : {
				noGraphs            : __('You have not added any schema yet. You can add any schema graphs you like via the Schema Generator below.', td),
				schemaInUse         : __('Schema In Use', td),
				generateSchema      : __('Generate Schema', td),
				validateSchema      : __('Validate Schema', td),
				defaultGraphTooltip : __('This is the default graph for this post type. All data for this graph will be automatically generated.', td)
			}
		}
	},
	methods : {
		getDefaultSchemaTooltipOffset () {
			if (isPageBuilderEditor()) {
				return '-120px,0'
			}

			return '0,0'
		}
	}
}
</script>