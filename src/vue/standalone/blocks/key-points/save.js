import { html } from '@/vue/standalone/blocks/utils'

const { InnerBlocks } = window.wp.blockEditor

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @returns {WPElement} Element to render.
 */
export default function save ({ className }) {
	return html`
    <div className=${className}>
      <div className="aioseo-key-points-block-content">
        <${InnerBlocks.Content} />
      </div>
    </div>
  `
}