<template>
	<div class="google-serp-snippet-placeholder">
		<div class="google-serp-snippet-placeholder__main">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
</template>

<style lang="scss" scoped>
.google-serp-snippet-placeholder {
	&__main {
		column-gap: 10px;
		display: grid;
		grid-template-columns: 28px auto;
		grid-template-rows: 13px 9px 19px 8px 8px;
		grid-template-areas:
		"favicon hostname"
		"favicon url"
		"title title"
		"desc1 desc1"
		"desc2 desc2";
		row-gap: 6px;

		> div {
			border-radius: 2px;

			&:nth-child(1) {
				background-color: $gray;
				border-radius: 50%;
				grid-area: favicon;
			}

			&:nth-child(2) {
				background-color: $gray;
				grid-area: hostname;
				max-width: 77%;
			}

			&:nth-child(3) {
				background-color: $gray;
				grid-area: url;
				max-width: 87%;
			}

			&:nth-child(4) {
				background-color: #D6D4F7;
				grid-area: title;
				max-width: 82%;
			}

			&:nth-child(5) {
				background-color: $gray;
				grid-area: desc1;
			}

			&:nth-child(6) {
				background-color: $gray;
				grid-area: desc2;
				max-width: 92%;
			}
		}
	}
}
</style>