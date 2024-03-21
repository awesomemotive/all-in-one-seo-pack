<template>
	<div class="aioseo-wizard-body">
		<div class="body-content">
			<slot />
		</div>
		<div
			v-if="$slots.cta"
			class="cta"
		>
			<slot name="cta" />
		</div>
		<div class="body-footer">
			<slot name="footer" />
		</div>
	</div>
</template>

<style lang="scss">
.aioseo-wizard-body {
	background-color: #fff;
	max-width: 900px;
	box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);

	.body-content {
		padding: 40px;

		.header {
			font-size: 24px;
			line-height: 30px;
			color: $black;
			font-weight: 600;

			+ *:not(.description) {
				margin-top: 20px;
			}
		}

		.description {
			line-height: 24px;
			margin-block: 16px 20px;
			font-size: 16px;
			color: $black2;
		}
	}

	.body-footer {
		border-top: 1px solid $border;
		padding: 20px 40px;
		display: flex;
		align-items: center;

		> * {
			margin-right: 16px;

			&:last-child {
				margin-right: 0;
			}
		}

		> .aioseo-button {
			font-size: 14px;
			line-height: 22px;
			height: 40px;

			@at-root {
				.aioseo-wizard-success & {
					margin-right: 0;
				}
			}
		}

		.spacer {
			flex: 1 0 auto;
		}
	}
}
</style>