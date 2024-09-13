<template>
	<div class="aioseo-tab-content aioseo-localseo-info">
		<core-settings-row
			:name="strings.name"
			class="info-name-row"
			align
		>
			<template #content>
				<local-business-name />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.businessType"
			class="info-business-type"
			align
		>
			<template #content>
				<local-business-business-type />
			</template>
		</core-settings-row>

		<core-settings-row
			class="info-business-image"
			:name="strings.image"
			align
		>
			<template #content>
				<local-business-image />
			</template>
		</core-settings-row>

		<!--core-settings-row
			:name="strings.urls"
			class="info-urls-row"
			align
		>
			<template #content>
				<local-business-urls />
			</template>
		</core-settings-row-->

		<core-settings-row
			:name="strings.businessAddress"
			class="info-businessaddress-row"
			align
		>
			<template #content>
				<local-business-business-address />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.map"
			align
		>
			<template #content>
				<local-business-map />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.businessContact"
			class="info-businesscontact-row"
			align
		>
			<template #content>
				<local-business-business-contact />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.businessIDs"
			class="info-businessids-row"
			align
		>
			<template #content>
				<local-business-business-ids />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.paymentInfo"
			class="info-paymentinfo-row"
			align
		>
			<template #content>
				<local-business-payment-info />
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.areaServed"
			class="info-area-row"
			align
		>
			<template #content>
				<local-business-area-served />
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import LocalBusinessAreaServed from '@/vue/components/AIOSEO_VERSION/local-business/AreaServed'
import LocalBusinessBusinessAddress from '@/vue/components/AIOSEO_VERSION/local-business/business/Address'
import LocalBusinessBusinessContact from '@/vue/components/AIOSEO_VERSION/local-business/business/Contact'
import LocalBusinessBusinessIds from '@/vue/components/AIOSEO_VERSION/local-business/business/Ids'
import LocalBusinessBusinessType from '@/vue/components/AIOSEO_VERSION/local-business/business/Type'
import LocalBusinessImage from '@/vue/components/AIOSEO_VERSION/local-business/Image'
import LocalBusinessMap from '@/vue/components/AIOSEO_VERSION/local-business/map/Index'
import LocalBusinessName from '@/vue/components/AIOSEO_VERSION/local-business/Name'
import LocalBusinessPaymentInfo from '@/vue/components/AIOSEO_VERSION/local-business/PaymentInfo'
// import LocalBusinessUrls from '@/vue/components/AIOSEO_VERSION/local-business/Urls'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreSettingsRow,
		LocalBusinessAreaServed,
		LocalBusinessBusinessAddress,
		LocalBusinessBusinessContact,
		LocalBusinessBusinessIds,
		LocalBusinessBusinessType,
		LocalBusinessImage,
		LocalBusinessMap,
		LocalBusinessName,
		LocalBusinessPaymentInfo
		// LocalBusinessUrls
	},
	data () {
		return {
			strings : {
				pageName        : __('Business Info', td),
				name            : __('Name', td),
				businessType    : __('Type', td),
				image           : __('Image', td),
				urls            : __('URLs', td),
				websiteDesc     : __('Website URL', td),
				aboutDesc       : __('About Page URL', td),
				contactDesc     : __('Contact Page URL', td),
				businessAddress : __('Address', td),
				businessContact : __('Contact Info', td),
				businessIDs     : __('IDs', td),
				paymentInfo     : __('Payment Info', td),
				areaServed      : __('Area Served', td),
				map             : __('Map', td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-localseo-info {
	.aioseo-input,
	.aioseo-select,
	.aioseo-multiselect {
		max-width: 480px;
	}

	.field-description {
		font-size: 14px;
	}

	.info-businessaddress-row .columns {
		display: flex;
		flex: 0 1 auto;
		flex-direction: row;
		flex-wrap: wrap;
	}

	&.aioseo-tab-content{
		svg {
			&.aioseo-circle-question-mark {
				width: 17px;
				height: 17px;
				color: #8C8F9A;
				transition: background-color 0.2s ease;
			}
		}
	}
}
</style>