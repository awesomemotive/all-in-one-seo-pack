<template>
	<transition
		name="nps-slide-up"
		appear
	>
		<div
			v-if="visible"
			class="aioseo-nps-survey"
		>
			<button
				class="aioseo-nps-survey__close"
				@click="onDismiss"
				:aria-label="strings.close"
			>
				&#x2715;
			</button>

			<!-- Step 1: Score -->
			<div
				v-if="1 === step"
				class="aioseo-nps-survey__step"
			>
				<div class="aioseo-nps-survey__header">
					{{ strings.heading }}
				</div>

				<p class="aioseo-nps-survey__question">
					{{ strings.question }}
				</p>

				<div class="aioseo-nps-survey__scores">
					<button
						v-for="n in 10"
						:key="n"
						class="aioseo-nps-survey__score-btn"
						:class="{ selected: n === score }"
						@click="selectScore(n)"
					>
						{{ n }}
					</button>
				</div>

				<div class="aioseo-nps-survey__labels">
					<span>{{ strings.notSatisfied }}</span>
					<span>{{ strings.verySatisfied }}</span>
				</div>
			</div>

			<!-- Step 2: Feedback -->
			<div
				v-if="2 === step"
				class="aioseo-nps-survey__step"
			>
				<button
					class="aioseo-nps-survey__back"
					@click="goBack"
				>
					&#8592; {{ strings.back }}
				</button>

				<div class="aioseo-nps-survey__header">
					{{ feedbackHeading }}
				</div>

				<textarea
					v-model="feedback"
					class="aioseo-nps-survey__textarea"
					:placeholder="strings.feedbackPlaceholder"
					rows="4"
				/>

				<base-button
					type="blue"
					:loading="submitting"
					@click="onSubmit"
				>
					{{ strings.submitButton }}
				</base-button>
			</div>

			<!-- Step 3: Thank you -->
			<div
				v-if="3 === step"
				class="aioseo-nps-survey__step aioseo-nps-survey__step--thankyou"
			>
				<p class="aioseo-nps-survey__thankyou">
					{{ strings.thankYou }}
				</p>
			</div>
		</div>
	</transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import http from '@/vue/utils/http'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN_PRO

const strings = {
	heading                 : __('Feedback', td),
	question                : __('How likely are you to recommend AIOSEO to a friend or colleague?', td),
	notSatisfied            : __('Not satisfied', td),
	verySatisfied           : __('Very satisfied', td),
	feedbackHeading         : __('What could we do to improve?', td),
	feedbackHeadingPromoter : __('What do you love most about AIOSEO?', td),
	feedbackPlaceholder     : __('Your feedback...', td),
	submitButton            : __('Submit feedback', td),
	thankYou                : __('Thank you for your feedback!', td),
	back                    : __('Back', td),
	close                   : __('Close', td)
}

const visible   = ref(true)
const step      = ref(1)
const score     = ref(null)
const feedback  = ref('')
const submitting = ref(false)
const submitted  = ref(false)

// Promoters (9-10) get a positive prompt; passives and detractors get an improvement prompt.
const feedbackHeading = computed(() => 9 <= score.value ? strings.feedbackHeadingPromoter : strings.feedbackHeading)

const selectScore = (n) => {
	score.value = n
	step.value  = 2
}

const goBack = () => {
	step.value = 1
}

const onDismiss = async () => {
	visible.value = false

	// Once submitted, the submission snooze is already set — closing must not overwrite it with the shorter dismiss snooze.
	if (submitted.value) {
		return
	}

	try {
		await http.post('/nps-survey/dismiss')
	} catch (e) {
		// Non-critical — ignore errors.
	}
}

const onSubmit = async () => {
	submitting.value = true
	try {
		await http.post('/nps-survey/submit').send({
			score    : score.value,
			feedback : feedback.value
		})
		submitted.value = true
	} catch (e) {
		// Non-critical — ignore errors.
	} finally {
		submitting.value = false
	}

	step.value = 3
	setTimeout(() => {
		visible.value = false
	}, 3000)
}

const onKeydown = (e) => {
	if ('Escape' === e.key && visible.value) {
		onDismiss()
	}
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => window.removeEventListener('keydown', onKeydown))
</script>

<style lang="scss">
.aioseo-nps-survey {
	position: fixed;
	bottom: 40px;
	right: 40px;
	width: 548px;
	max-width: calc(100vw - 32px);
	box-sizing: border-box;
	z-index: 9999;

	background: $white;
	border-radius: 4px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
	padding: 24px;
	box-sizing: border-box;

	&__close {
		position: absolute;
		top: 12px;
		right: 12px;
		background: none;
		border: none;
		cursor: pointer;
		font-size: 16px;
		color: $gray3;
		line-height: 1;
		padding: 4px;

		&:hover {
			color: $font-color;
		}
	}

	&__back {
		display: inline-flex;
		align-items: center;
		background: none;
		border: none;
		cursor: pointer;
		font-size: 13px;
		color: $gray3;
		padding: 0;
		margin-bottom: 12px;

		&:hover {
			color: $blue;
		}
	}

	&__header {
		font-size: 16px;
		font-weight: 700;
		color: $black3;
		margin-bottom: 12px;
	}

	&__question {
		font-size: 14px;
		color: $font-color;
		margin-bottom: 16px;
	}

	&__scores {
		display: flex;
		gap: 6px;
		margin-bottom: 8px;
	}

	&__score-btn {
		flex: 1;
		height: 36px;
		border: 1px solid $input-border;
		border-radius: 4px;
		background: $white;
		cursor: pointer;
		font-size: 14px;
		font-weight: 600;
		color: $font-color;
		transition: background 0.15s, border-color 0.15s, color 0.15s;

		&:hover,
		&.selected {
			background: $blue;
			border-color: $blue;
			color: $white;
		}
	}

	&__labels {
		display: flex;
		justify-content: space-between;
		font-size: 12px;
		color: $gray3;
		margin-bottom: 0;
	}

	&__textarea {
		width: 100%;
		box-sizing: border-box;
		border: 1px solid $input-border;
		border-radius: 4px;
		padding: 10px 12px;
		font-size: 14px;
		resize: vertical;
		margin-bottom: 16px;
		font-family: inherit;

		&:focus {
			outline: none;
			border-color: $blue;
		}
	}

	&__step--thankyou {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 60px;
	}

	&__thankyou {
		font-size: 15px;
		font-weight: 600;
		color: $black3;
		text-align: center;
		margin: 0;
	}

	@media (max-width: 600px) {
		left: 16px;
		right: 16px;
		bottom: 16px;
		width: auto;
		max-width: none;
		padding: 16px;

		&__scores {
			gap: 4px;
		}

		&__score-btn {
			height: 32px;
			font-size: 13px;
		}
	}
}

.nps-slide-up-enter-active,
.nps-slide-up-leave-active {
	transition: opacity 0.3s ease, transform 0.3s ease;
}

.nps-slide-up-enter-from,
.nps-slide-up-leave-to {
	opacity: 0;
	transform: translateY(20px);
}
</style>