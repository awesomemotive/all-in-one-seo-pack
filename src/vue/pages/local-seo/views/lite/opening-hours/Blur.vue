<template>
	<div class="aioseo-opening-hours-blur">
		<core-blur>
			<core-settings-row
				:name="strings.showOpeningHours"
				class="info-openinghours-row"
				align
			>
				<template #content>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<base-radio-toggle
							name="openingHours"
							:modelValue="true"
							:options="[
								{ label: GLOBAL_STRINGS.no, value: false },
								{ label: GLOBAL_STRINGS.yes, value: true }
							]"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.hours"
				class="info-hours-row"
				align
			>
				<template #content>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<base-toggle>
							{{ strings.open247 }}
						</base-toggle>
					</div>
					<div class="aioseo-col col-xs-12 text-xs-left mt-16">
						<base-toggle>
							{{ strings.use24hFormat }}
						</base-toggle>
					</div>

					<div class="aioseo-col col-xs-12 text-xs-left">
						<div class="aioseo-col-flex text-xs-left">
							<div class="aioseo-col-day text-xs-left">
								{{ strings.monday }}
							</div>
							<div class="aioseo-col-hours text-xs-left">
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="09:00"
								/>
								<span class="separator">-</span>
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="17:00"
								/>
							</div>
							<div class="aioseo-col-alwaysopen text-xs-left">
								<base-checkbox
									size="medium"
								>
										{{ strings.open24h }}
								</base-checkbox>

								<base-checkbox
									size="medium"
									class="closed-label"
								>
										{{ strings.closed }}
								</base-checkbox>
							</div>
						</div>

						<div class="aioseo-col-flex text-xs-left">
							<div class="aioseo-col-day text-xs-left">
								{{ strings.tuesday }}
							</div>
							<div class="aioseo-col-hours text-xs-left">
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="09:00"
								/>
								<span class="separator">-</span>
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="17:00"
								/>
							</div>
							<div class="aioseo-col-alwaysopen text-xs-left">
								<base-checkbox
									size="medium"
								>
										{{ strings.open24h }}
								</base-checkbox>

								<base-checkbox
									size="medium"
									class="closed-label"
								>
										{{ strings.closed }}
								</base-checkbox>
							</div>
						</div>

						<div class="aioseo-col-flex text-xs-left">
							<div class="aioseo-col-day text-xs-left">
								{{ strings.wednesday }}
							</div>
							<div class="aioseo-col-hours text-xs-left">
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="09:00"
								/>
								<span class="separator">-</span>
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="17:00"
								/>
							</div>
							<div class="aioseo-col-alwaysopen text-xs-left">
								<base-checkbox
									size="medium"
								>
										{{ strings.open24h }}
								</base-checkbox>

								<base-checkbox
									size="medium"
									class="closed-label"
								>
										{{ strings.closed }}
								</base-checkbox>
							</div>
						</div>

						<div class="aioseo-col-flex text-xs-left">
							<div class="aioseo-col-day text-xs-left">
								{{ strings.thursday }}
							</div>
							<div class="aioseo-col-hours text-xs-left">
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="09:00"
								/>
								<span class="separator">-</span>
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="17:00"
								/>
							</div>
							<div class="aioseo-col-alwaysopen text-xs-left">
								<base-checkbox
									size="medium"
								>
										{{ strings.open24h }}
								</base-checkbox>

								<base-checkbox
									size="medium"
									class="closed-label"
								>
										{{ strings.closed }}
								</base-checkbox>
							</div>
						</div>

						<div class="aioseo-col-flex text-xs-left">
							<div class="aioseo-col-day text-xs-left">
								{{ strings.friday }}
							</div>
							<div class="aioseo-col-hours text-xs-left">
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="09:00"
								/>
								<span class="separator">-</span>
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="17:00"
								/>
							</div>
							<div class="aioseo-col-alwaysopen text-xs-left">
								<base-checkbox
									size="medium"
								>
										{{ strings.open24h }}
								</base-checkbox>

								<base-checkbox
									size="medium"
									class="closed-label"
								>
										{{ strings.closed }}
								</base-checkbox>
							</div>
						</div>

						<div class="aioseo-col-flex text-xs-left">
							<div class="aioseo-col-day text-xs-left">
								{{ strings.saturday }}
							</div>
							<div class="aioseo-col-hours text-xs-left">
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="09:00"
								/>
								<span class="separator">-</span>
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="17:00"
								/>
							</div>
							<div class="aioseo-col-alwaysopen text-xs-left">
								<base-checkbox
									size="medium"
								>
									{{ strings.open24h }}
								</base-checkbox>

								<base-checkbox
									size="medium"
									class="closed-label"
								>
									{{ strings.closed }}
								</base-checkbox>
							</div>
						</div>

						<div class="aioseo-col-flex text-xs-left">
							<div class="aioseo-col-day text-xs-left">
								{{ strings.sunday }}
							</div>
							<div class="aioseo-col-hours text-xs-left">
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="09:00"
								/>
								<span class="separator">-</span>
								<base-select
									size="medium"
									:options="HOURS_24H_FORMAT"
									modelValue="17:00"
								/>
							</div>
							<div class="aioseo-col-alwaysopen text-xs-left">
								<base-checkbox
									size="medium"
								>
										{{ strings.open24h }}
								</base-checkbox>

								<base-checkbox
									size="medium"
									class="closed-label"
								>
										{{ strings.closed }}
								</base-checkbox>
							</div>
						</div>
					</div>
				</template>
			</core-settings-row>
		</core-blur>
	</div>
</template>

<script>
import {
	GLOBAL_STRINGS,
	HOURS_24H_FORMAT
} from '@/vue/plugins/constants'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		BaseCheckbox,
		BaseRadioToggle,
		CoreBlur,
		CoreSettingsRow
	},
	data () {
		return {
			GLOBAL_STRINGS,
			HOURS_24H_FORMAT,
			strings : {
				showOpeningHours    : __('Show Opening Hours', td),
				displayOpeningHours : __('Display Opening Hours', td),
				labels              : __('Labels', td),
				open247             : __('Open 24/7', td),
				use24hFormat        : __('Use 24h format', td),
				timezone            : __('Timezone', td),
				selectTimeZone      : __('Select your timezone:', td),
				hours               : __('Opening Hours', td),
				monday              : __('Monday', td),
				tuesday             : __('Tuesday', td),
				wednesday           : __('Wednesday', td),
				thursday            : __('Thursday', td),
				friday              : __('Friday', td),
				saturday            : __('Saturday', td),
				sunday              : __('Sunday', td),
				open24h             : __('Open 24h', td),
				closed              : __('Closed', td)
			}
		}
	}
}
</script>