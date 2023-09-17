<template>
  <div
      class="tru-seo-highlight-popover"
      data-arrow-placement="right"
  >
    <div
        class="tru-seo-highlight-popover__bullet"
        :class="{ 'tru-seo-highlight-popover__bullet--error' : error }"
    >
      <svg-ellipse width="8"/>
    </div>

    <div class="tru-seo-highlight-popover__count">
      <span>{{ order }}/{{ total }}</span>
    </div>

    <div class="tru-seo-highlight-popover__pipe"/>

    <div class="tru-seo-highlight-popover__nav">
      <button
          type="button"
          class="caret-wrapper caret-wrapper--previous"
          tabindex="0"
          :disabled="1 === order"
          @click.stop.exact="$emit('previous')"
      >
        <svg-caret width="20"/>
      </button>

      <button
          type="button"
          class="caret-wrapper"
          tabindex="0"
          :disabled="order === total"
          @click.stop.exact="$emit('next')"
      >
        <svg-caret width="20"/>
      </button>
    </div>

    <div class="tru-seo-highlight-popover__close">
      <button
          type="button"
          class="close-wrapper"
          tabindex="0"
          @click.stop.exact.once="close"
      >
        <svg-close width="10"/>
      </button>
    </div>
  </div>
</template>

<script>
import {
	useTruSeoHighlighterStore
} from '@/vue/stores'

import SvgEllipse from '@/vue/components/common/svg/Ellipse'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgClose from '@/vue/components/common/svg/Close'

export default {
	setup () {
		return {
			truSeoHighlighterStore : useTruSeoHighlighterStore()
		}
	},
	emits      : [ 'next', 'previous' ],
	components : {
		SvgEllipse,
		SvgCaret,
		SvgClose
	},
	data () {
		return {
			error : this.truSeoHighlighterStore.highlightAnalyzerHasError,
			order : this.truSeoHighlighterStore.highlightMarks.findIndex(hm => hm.active) + 1,
			total : this.truSeoHighlighterStore.highlightMarks.length
		}
	},
	methods : {
		close () {
			this.truSeoHighlighterStore.toggleHighlightAnalyzer(null)
		}
	}
}
</script>

<style lang="scss" scoped>
.tru-seo-highlight-popover {
  @mixin size($width, $height) {
    height: $height;
    line-height: $height;
    width: $width;
  }

  align-items: center;
  background-color: #2C324C;
  border-radius: 4px;
  color: #fff;
  display: inline-flex;
  font-family: $font-family;
  font-size: 12px;
  line-height: normal;
  padding: 4px 5px 4px 10px;
  position: relative;
  user-select: none;

  * {
    font-size: inherit;
  }

  &::before {
    border-style: solid;
    content: '';
    height: 0;
    position: absolute;
    width: 0;
  }

  &[data-arrow-placement="right"] {
    &::before {
      border-color: transparent transparent transparent #2C324C;
      border-width: 6px 0 6px 6px;
      left: 100%;
      top: 50%;
      transform: translate(-20%, -50%);
    }
  }

  &[data-arrow-placement="bottomleft"] {
    &::before {
      border-color: #2C324C transparent transparent transparent;
      border-width: 8px 8px 0 8px;
      left: 0;
      top: 100%;
      transform: translate(0, -50%);
    }
  }

  &__bullet {
    @include size(8px, 8px);

    color: #34D399;
    margin-right: 5px;

    &--error {
      color: #F87171;
    }
  }

  &__count {
    margin-right: 8px;
  }

  &__pipe {
    @include size(1px, 12px);

    background-color: $placeholder-color;
    margin-right: 8px;
  }

  &__nav,
  &__close {
    @include size(auto, 20px);

    .caret-wrapper,
    .close-wrapper {
      @include size(20px, 20px);

      align-items: center;
      border-radius: 2px;
      display: inline-flex;
      justify-content: center;
    }

    .caret-wrapper {
      margin-right: 4px;

      &--previous {
        transform: rotate(180deg);
      }
    }
  }

  &__close {
    @include size(auto, 20px);
  }

  &__nav {
    @include size(50px, 20px);
  }

  button {
    background-color: transparent;
    border: none;
    box-shadow: none;
    color: #fff;
    cursor: pointer;
    outline: none;
    opacity: 1;
    padding: 0;
    position: relative;

    &:disabled {
      cursor: not-allowed;
      opacity: 0.5;
    }

    &:hover,
    &:focus {
      background-color: #434960;
    }
  }
}
</style>