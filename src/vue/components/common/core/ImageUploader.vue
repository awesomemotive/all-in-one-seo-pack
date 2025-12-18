<template>
	<div
		class="aioseo-image-uploader"
		:class="{'aioseo-image-uploader--has-image': !!modelValue}"
	>
		<div class="image-upload">
			<base-input
				:size="baseSize"
				:modelValue="modelValue"
				:placeholder="strings.pasteYourImageUrl"
				@change="(src) => setImgSrc(src)"
			>
				<template #append-icon>
					<base-button
						v-if="!!modelValue"
						:size="baseSize"
						class="remove-image"
						type="gray"
						@click.prevent="setImgSrc(null)"
					>
						<svg-trash :width="iconWidth"/>
					</base-button>
				</template>
			</base-input>

			<base-button
				:size="baseSize"
				class="insert-image"
				type="black"
				@click.prevent="openUploadModal()"
			>
				<svg-circle-plus width="14"/>

				{{ strings.uploadOrSelectImage }}
			</base-button>
		</div>

		<div
			class="aioseo-description"
			v-html="description || strings.description"
		/>

		<base-img
			class="image-preview"
			:src="modelValue"
			:debounce="useDebounce"
		/>
	</div>
</template>

<script>
import { useMediaUploader } from '@/vue/composables/MediaUploader'

import BaseButton from '@/vue/components/common/base/Button'
import BaseImg from '@/vue/components/common/base/Img'
import BaseInput from '@/vue/components/common/base/Input'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'update:modelValue' ],
	setup () {
		const { openMediaLibrary } = useMediaUploader()

		return {
			openMediaLibrary
		}
	},
	components : {
		BaseButton,
		BaseImg,
		BaseInput,
		SvgCirclePlus,
		SvgTrash
	},
	props : {
		baseSize : {
			type    : String,
			default : 'medium'
		},
		imgPreviewMaxHeight : {
			type    : String,
			default : '525px'
		},
		imgPreviewMaxWidth : {
			type    : String,
			default : '525px'
		},
		description : String,
		modelValue  : {
			type    : String,
			default : ''
		},
		useDebounce : {
			type    : Boolean,
			default : true
		}
	},
	data () {
		return {
			strings : {
				description         : __('Minimum size: 112px x 112px, The image must be in JPG, PNG, GIF, SVG, or WEBP format.', td),
				pasteYourImageUrl   : __('Paste your image URL or select a new image', td),
				remove              : __('Remove', td),
				uploadOrSelectImage : __('Upload or Select Image', td)
			}
		}
	},
	computed : {
		iconWidth () {
			return 'small' === this.baseSize ? '16' : '20'
		}
	},
	methods : {
		setImgSrc (src) {
			this.$emit('update:modelValue', src)
		},
		openUploadModal () {
			this.openMediaLibrary({
				title      : __('Choose Image', td),
				buttonText : __('Choose Image', td),
				type       : 'image',
				onSelect   : (attachment) => this.setImgSrc(attachment?.url || null)
			})
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-image-uploader {
	display: grid;
	gap: 8px;

	&--no-icon {
		svg.aioseo-circle-plus {
			display: none;
		}
	}

	&--has-image {
		:deep(.aioseo-input input) {
			padding-right: 45px;
			text-overflow: ellipsis;
		}
	}

	.image-upload {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;

		.aioseo-input-container {
			flex: 1 1 50%;
			min-width: 50%;
			max-width: 445px;

			:deep(.aioseo-input) {
				max-width: 100%;
			}
		}

		.insert-image {
			flex: 1 1 auto;
			max-width: 250px;

			svg.aioseo-circle-plus {
				margin-right: 8px;
			}
		}

		.remove-image {
			padding-left: 6px;
			padding-right: 6px;
			position: absolute;
			right: 4px;
			top: 50%;
			transform: translateY(-50%);
			height: calc(100% - 8px);
		}
	}

	.aioseo-description {
		margin: 0;
	}

	img.image-preview {
		margin: 0;
		max-height: v-bind(imgPreviewMaxHeight);
		max-width: v-bind(imgPreviewMaxWidth);
		height: auto;
		width: auto;
	}
}
</style>