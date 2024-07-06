<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with Thrive Architect Page Builder.
 *
 * @since 4.6.6
 */
class ThriveArchitect extends Base {
	/**
	 * The plugin files.
	 *
	 * @since 4.6.6
	 *
	 * @var array
	 */
	public $plugins = [
		'thrive-visual-editor/thrive-visual-editor.php'
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.6.6
	 *
	 * @var string
	 */
	public $integrationSlug = 'thrive-architect';

	/**
	 * Init the integration.
	 *
	 * @since 4.6.6
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'tcb_allowed_ajax_options', [ $this, 'makeSettingsAllowed' ] );

		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( get_post_type( $this->getPostId() ) ) ) {
			return;
		}

		add_action( 'tcb_main_frame_enqueue', [ $this, 'enqueue' ] );
		add_filter( 'tve_main_js_dependencies', [ $this, 'mainJsDependencies' ] );
		add_action( 'tcb_right_sidebar_content_settings', [ $this, 'addSettingsTab' ] );
		add_action( 'tcb_sidebar_extra_links', [ $this, 'addSidebarButton' ] );
		add_filter( 'tcb_main_frame_localize', [ $this, 'localizeData' ] );
	}

	/**
	 * Overrides the parent enqueue to add WordPress styles that we need.
	 *
	 * @since 4.6.6
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( 'common' );
		wp_enqueue_style( 'buttons' );
		wp_enqueue_style( 'forms' );
		wp_enqueue_style( 'list-tables' );
		wp_enqueue_style( 'wp-components' );

		print_admin_styles();

		parent::enqueue();
	}

	/**
	 * Add our javascript to the plugin dependencies.
	 *
	 * @since 4.6.6
	 *
	 * @param  array $dependencies The dependencies.
	 * @return array               The dependencies.
	 */
	public function mainJsDependencies( $dependencies ) {
		$dependencies[] = aioseo()->core->assets->jsHandle( "src/vue/standalone/page-builders/{$this->integrationSlug}/main.js" );

		return $dependencies;
	}

	/**
	 * Add the extra link to the sidebar.
	 *
	 * @since 4.6.6
	 *
	 * @return void
	 */
	public function addSidebarButton() {
		$tooltip = sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			esc_html__( '%1$s Settings', 'all-in-one-seo-pack' ),
			AIOSEO_PLUGIN_SHORT_NAME
		);

		// phpcs:disable Generic.Files.LineLength.MaxExceeded
		?>
		<a href="javascript:void(0)" class="mouseenter mouseleave sidebar-item tcb-sidebar-icon-aioseo" data-position="left" data-toggle="settings" data-tooltip="<?php echo esc_attr( $tooltip ); ?>">
			<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M10.0011 19C14.9722 19 19.0021 14.9706 19.0021 10C19.0021 5.02944 14.9722 1 10.0011 1C5.02991 1 1 5.02944 1 10C1 14.9706 5.02991 19 10.0011 19Z" stroke="currentColor"/>
				<path d="M9.99664 13.3228C9.99896 13.2104 9.47813 13.1752 9.37307 13.141C8.56228 12.8777 8.04027 12.3293 7.78204 11.5205C7.6493 11.1043 7.68851 10.6765 7.68163 10.2515C7.68162 10.2511 7.68134 10.2507 7.68093 10.2506V10.2506C7.68051 10.2504 7.68023 10.25 7.68023 10.2496C7.68069 9.99579 7.67884 9.74246 7.68115 9.48867C7.683 9.31099 7.74131 9.25453 7.92133 9.25222C8.07128 9.25037 8.22168 9.24482 8.37116 9.25407C8.48454 9.26101 8.86945 9.25407 8.86945 9.25407M9.99664 13.3228C9.98877 13.7037 10.0003 14.1192 10.0008 14.5M9.99664 13.3228C10.0036 13.7152 9.99664 14.1076 10.0008 14.5M9.99664 13.3228C9.99863 13.2265 10.5453 13.1946 10.6332 13.1715C11.6884 12.8929 12.42 11.955 12.4311 10.8639C12.4358 10.4137 12.433 9.96389 12.432 9.51366C12.432 9.30312 12.3788 9.25268 12.1641 9.25176C12.0197 9.25083 11.8749 9.24435 11.7314 9.25407C11.6213 9.26148 11.2793 9.25176 11.2793 9.25176M10.0008 14.5C10.0008 14.7878 9.72149 14.8117 9.50075 15C9.29018 15.1795 8.77054 15.5587 8.52758 15.6966C8.32442 15.8118 8.12033 15.8428 7.90097 15.7401C7.72373 15.6573 7.53862 15.5916 7.36276 15.5059C7.13877 15.3963 7.04344 15.1936 7.08879 14.947C7.1323 14.7091 7.17765 14.4718 7.22578 14.2348C7.27529 13.9933 7.21745 13.7897 7.02632 13.6291C6.78706 13.4283 6.5677 13.2071 6.37195 12.9637C6.21321 12.7671 6.01098 12.7102 5.76941 12.762C5.5445 12.8101 5.31866 12.8559 5.09282 12.9008C4.84339 12.9503 4.64902 12.8587 4.53425 12.6329C4.42457 12.4173 4.33433 12.1924 4.2589 11.9624C4.1793 11.719 4.24085 11.5191 4.4491 11.3683C4.64532 11.2262 4.84616 11.0911 5.04794 10.9574C5.26961 10.8107 5.34828 10.6071 5.31866 10.348C5.2858 10.0606 5.28673 9.7714 5.31635 9.48405C5.34411 9.21613 5.25711 9.01253 5.02757 8.86585C4.8383 8.74461 4.65318 8.6169 4.46853 8.4878C4.23576 8.32492 4.16541 8.12086 4.26028 7.85525C4.33803 7.6387 4.42318 7.42446 4.51852 7.21531C4.62218 6.98811 4.80822 6.89186 5.05349 6.93258C5.28025 6.97006 5.50609 7.0168 5.731 7.06585C5.99154 7.12322 6.20812 7.06631 6.3775 6.84929C6.56261 6.61238 6.77781 6.40322 7.00503 6.2061C7.18829 6.04693 7.27205 5.8549 7.21143 5.60549C7.16006 5.3931 7.12906 5.17608 7.08509 4.96184C7.01429 4.61803 7.1036 4.42924 7.4257 4.28024C7.6085 4.19603 7.79453 4.11783 7.98335 4.04842C8.22399 3.9605 8.42762 4.02574 8.57385 4.23536C8.71546 4.43849 8.84967 4.64718 8.98341 4.85587C9.12317 5.07382 9.32032 5.15896 9.57392 5.12703C9.86131 5.09094 10.1492 5.09325 10.437 5.12564C10.6763 5.15248 10.8669 5.0715 11.0002 4.86698C11.1261 4.67402 11.251 4.48014 11.3778 4.28765C11.5611 4.01001 11.7467 3.94384 12.054 4.05952C12.2479 4.13217 12.4395 4.21176 12.6264 4.3006C12.8731 4.41813 12.9684 4.6134 12.9198 4.87993C12.8763 5.11777 12.8291 5.35469 12.7828 5.59207C12.737 5.82621 12.7944 6.0261 12.9804 6.18297C13.2234 6.38842 13.4437 6.61561 13.6473 6.86086C13.8005 7.04502 13.993 7.11443 14.2337 7.05474C14.4567 6.99921 14.6839 6.95896 14.9098 6.91407C15.1648 6.86317 15.3661 6.95988 15.4822 7.19217C15.5882 7.40364 15.6761 7.6225 15.7506 7.84693C15.8335 8.09726 15.771 8.29901 15.5558 8.45495C15.3596 8.59654 15.1583 8.73166 14.9565 8.86585C14.7473 9.00466 14.6645 9.19855 14.6913 9.44425C14.7237 9.74363 14.7242 10.0435 14.6946 10.3429C14.6691 10.6038 14.7552 10.8033 14.9783 10.9467C15.1624 11.0652 15.3429 11.1897 15.523 11.3146C15.7816 11.4946 15.8506 11.6973 15.7451 11.9879C15.6766 12.1771 15.5993 12.3636 15.5174 12.5473C15.3818 12.8504 15.1907 12.9401 14.8621 12.8721C14.6423 12.8263 14.4211 12.7888 14.2017 12.7398C13.9939 12.6935 13.8213 12.7574 13.689 12.911C13.4627 13.1738 13.2234 13.4218 12.9638 13.6527C12.8088 13.7906 12.7467 13.9706 12.7958 14.1849C12.8485 14.4148 12.8874 14.6476 12.9337 14.879C12.9957 15.189 12.8999 15.3856 12.6088 15.5235C12.4478 15.5999 12.2789 15.66 12.1178 15.7364C11.911 15.8345 11.7175 15.8058 11.5236 15.7003C11.2265 15.5388 10.741 15.2332 10.5009 15C10.3403 14.8441 10.0031 14.7207 10.0008 14.5ZM11.2793 9.25176C11.2848 8.85382 11.2873 8.01509 11.2804 7.61719C11.2795 7.56905 11.2791 7.5401 11.279 7.52286C11.2788 7.50546 11.2793 7.50858 11.2794 7.52598C11.2798 7.63906 11.2816 8.09163 11.2833 8.28906C11.2796 8.687 11.2714 8.85382 11.2793 9.25176ZM11.2793 9.25176C11.2793 9.25176 10.9086 9.25685 10.7873 9.255C10.2968 9.24806 9.80624 9.24852 9.31569 9.255C9.19999 9.25638 8.86945 9.25407 8.86945 9.25407M8.86945 9.25407C8.87593 8.8677 8.87547 8.34389 8.87408 7.95752C8.87346 7.78806 8.87262 7.62829 8.87143 7.54953C8.8709 7.51441 8.86954 7.51752 8.86963 7.55263C8.86985 7.62907 8.8701 7.7811 8.86945 7.95752C8.86853 8.34435 8.86251 8.8677 8.86945 9.25407Z" stroke="currentColor"/>
			</svg>
		</a>
		<?php
		//phpcs:enable Generic.Files.LineLength.MaxExceeded
	}

	/**
	 * Adds the settings tab for AIOSEO in the Thrive Architect page builder.
	 *
	 * @since 4.6.6
	 *
	 * @return void
	 */
	public function addSettingsTab() {
		//phpcs:disable Generic.Files.LineLength.MaxExceeded
		?>
		<div class="tve-component s-item tcb-aioseo">
			<div class="dropdown-header">
				<div class="group-description s-name">
					<?php echo esc_html( AIOSEO_PLUGIN_SHORT_NAME ); ?>
				</div>
			</div>
			<div class="dropdown-content">
				<div class="tcb-aioseo-settings">
					<button class="click tcb-settings-modal-open-button s-item inside-button">
						<span class="s-name">
							<?php
								printf(
									// Translators: 1 - The plugin short name ("AIOSEO").
									esc_html__( '%1$s Settings', 'all-in-one-seo-pack' ),
									esc_html( AIOSEO_PLUGIN_SHORT_NAME )
								);
							?>
						</span>
					</button>
					<div class="mt-10 button-group">
						<div id="aioseo-score-btn-settings"></div>
						<button type="button" class="p-3 action-btn click" id="settings-action-btn">
							<svg class="when-active" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9.61433 9.94582C10.145 11.0636 10.2253 12.3535 9.45767 13.2683C9.24696 13.5194 8.89896 13.533 8.66555 13.3372L6.22379 11.2883L4.64347 13.1717C4.62842 13.1896 4.59542 13.1925 4.58036 13.2104L3.42703 13.7098C3.28287 13.7722 3.12128 13.6366 3.15772 13.4838L3.44925 12.2613C3.4643 12.2434 3.47935 12.2254 3.4944 12.2075L5.07472 10.3241L2.63295 8.27524C2.3816 8.06433 2.35256 7.73431 2.56327 7.4832C3.3158 6.58636 4.59718 6.40838 5.7901 6.73691L7.81453 4.7983L7.06045 4.16555C6.80909 3.95464 6.78006 3.62462 6.99077 3.37351L7.7132 2.51255C7.90885 2.27937 8.25395 2.23272 8.50531 2.44363L13.3888 6.54141C13.6222 6.73725 13.6542 7.10028 13.4585 7.33345L12.7361 8.19441C12.5254 8.44552 12.1774 8.45917 11.944 8.26332L11.172 7.61551L9.61433 9.94582Z" fill="#FFFFFF"/>
							</svg>

							<svg class="when-inactive" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M5.87874 6.50704C5.88995 6.50899 5.90115 6.51097 5.91236 6.513L7.23686 5.29914L6.55461 4.72665C6.3212 4.5308 6.27436 4.18554 6.48527 3.93418L7.6905 2.49785C7.88635 2.26445 8.24956 2.23267 8.48297 2.42852L13.3665 6.52629C13.6179 6.73721 13.6317 7.08535 13.4358 7.31876L12.2306 8.75509C12.0197 9.00645 11.6895 9.03534 11.4381 8.82442L10.7738 8.26701L9.80841 9.78218C9.81235 9.79286 9.81625 9.80354 9.82011 9.81424L9.23164 9.32046L10.6275 7.16519L11.7766 8.12937L12.7408 6.9803L8.14451 3.12358L7.18033 4.27264L8.3294 5.23682L6.4644 6.99846L5.87874 6.50704ZM4.72914 6.45619C3.84314 6.53709 3.0184 6.91015 2.43354 7.63253C2.31301 7.77616 2.31817 8.02525 2.47976 8.16084L5.35242 10.5713L3.54458 12.7258L3.51445 12.7617L3.176 13.4568C3.09138 13.6305 3.28888 13.7962 3.44531 13.6827L4.07103 13.2287L4.10116 13.1928L5.909 11.0383L8.78167 13.4488C8.94326 13.5844 9.18946 13.5462 9.30998 13.4025C9.90734 12.6906 10.1364 11.8178 10.0663 10.9346L9.15211 10.1675C9.42355 10.9846 9.399 11.8779 8.95854 12.6181L3.26707 7.84242C3.90443 7.26739 4.79027 7.09684 5.64573 7.2253L4.72914 6.45619Z" fill="#50565F"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M12.4242 11.9993L3.23163 4.28583L3.68158 3.7496L12.8741 11.463L12.4242 11.9993Z" fill="#50565F"/>
							</svg>
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
		//phpcs:enable Generic.Files.LineLength.MaxExceeded
	}

	/**
	 * Localizes the data by adding the 'is_aioseo_settings_enabled' option to the provided data array.
	 *
	 * @since 4.6.6
	 *
	 * @param  array $data The data array to be localized.
	 * @return array       The localized data array with the 'is_aioseo_settings_enabled' option added.
	 */
	public function localizeData( $data ) {
		// We use get_option here since it is how Thrive Architect saves the settings.
		$data['is_aioseo_settings_enabled'] = get_option( 'is_aioseo_settings_enabled', true );

		return $data;
	}

	/**
	 * Adds 'is_aioseo_settings_enabled' to the list of allowed settings.
	 *
	 * @since 4.6.6
	 *
	 * @param  array $options The array of allowed settings.
	 * @return array          The updated array of allowed settings.
	 */
	public function makeSettingsAllowed( $options ) {
		$options[] = 'is_aioseo_settings_enabled';

		return $options;
	}

	/**
	 * Returns whether or not the given Post ID was built with Thrive Architect.
	 *
	 * @since 4.6.6
	 *
	 * @param  int     $postId The Post ID.
	 * @return boolean         Whether or not the Post was built with Thrive Architect.
	 */
	public function isBuiltWith( $postId ) {
		if ( ! function_exists( 'tcb_post' ) ) {
			return false;
		}

		return tcb_post( $postId )->editor_enabled();
	}

	/**
	 * Returns whether should or not limit the modified date.
	 *
	 * @since 4.6.6
	 *
	 * @param  int     $postId The Post ID.
	 * @return boolean         Whether or not sholud limit the modified date.
	 */
	public function limitModifiedDate( $postId ) {
		if ( ! class_exists( 'TCB_Editor_Ajax' ) ) {
			return false;
		}

		// This method is supposed to be used in the `wp_ajax_tcb_editor_ajax` action.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), \TCB_Editor_Ajax::NONCE_KEY ) ) {
			return false;
		}

		$editorPostId = ! empty( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
		if ( $editorPostId !== $postId ) {
			return false;
		}

		return ! empty( $_REQUEST['aioseo_limit_modified_date'] ) && 'true' === $_REQUEST['aioseo_limit_modified_date'];
	}
}