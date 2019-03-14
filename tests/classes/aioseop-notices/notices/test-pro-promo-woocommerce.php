<?php
/**
 * Class AIOSEOP_Notices_TestCase
 *
 * @package All_in_One_SEO_Pack
 * @subpackage AIOSEOP_Notices
 * @since 3.0
 *
 * @group AIOSEOP_Notices
 * @group Admin
 * @group Notices
 */

include_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-notices-testcase.php';

/**
 * Class Test Notice - Pro Promo with WooCommerce
 *
 * Displays when SEO is used with WooCommerce.
 *
 * @since 3.0
 *
 * @package classes\AIOSEOP_Notices\notices
 */
class Test_Notice_ProPromoWooCommerce extends AIOSEOP_Notices_TestCase {

	/**
	 * Mock Single Notice
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	protected function mock_notice() {
		return aioseop_notice_pro_promo_woocommerce();
	}
}
