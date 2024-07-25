<template>
	<div
		class="aioseo-loading-spinner"
		:class="{ dark: dark }"
	>
		<div class="double-bounce1"></div>
		<div class="double-bounce2"></div>
	</div>
</template>

<script>
export default {
	props : {
		dark : Boolean
	}
}
</script>

<style lang="scss">
.aioseo-loading-spinner {
	width: 35px;
	height: 35px;
	position: absolute;

	.edit-post-sidebar &,
	.editor-sidebar & {
		margin-left: auto;
		margin-right: auto;
	}

	.double-bounce1, .double-bounce2 {
		width: 100%;
		height: 100%;
		border-radius: 50%;
		background-color: #fff;
		opacity: 0.6;
		position: absolute;
		top: 0;
		left: 0;

		-webkit-animation: sk-bounce 1.3s infinite ease-in-out;
		animation: sk-bounce 1.3s infinite ease-in-out;
	}

	&.dark {
		.double-bounce1, .double-bounce2 {
			background-color: $placeholder-color;
		}
	}

	.double-bounce2 {
		-webkit-animation-delay: -0.65s;
		animation-delay: -0.65s;
	}

	@-webkit-keyframes sk-bounce {
		0%, 100% { -webkit-transform: scale(0.0) }
		50% { -webkit-transform: scale(1.0) }
	}

	@keyframes sk-bounce {
		0%, 100% {
			transform: scale(0.0);
			-webkit-transform: scale(0.0);
		} 50% {
			transform: scale(1.0);
			-webkit-transform: scale(1.0);
		}
	}
}
</style>