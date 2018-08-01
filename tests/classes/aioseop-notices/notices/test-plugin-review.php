<?php
/**
 * Class AIOSEOP_Notices_TestCase
 *
 * @package All_in_One_SEO_Pack
 * @subpackage AIOSEOP_Notices
 * @since 2.4.5.1
 *
 * @group AIOSEOP_Notices
 * @group Admin
 * @group Notices
 */

include_once AIOSEOP_UNIT_TESTING_DIR . '\base\class-aioseop-notices-testcase.php';

/**
 * Class Test_Plugin_Review
 *
 * @since 2.4.5.1
 *
 * @package classes\AIOSEOP_Notices\notices
 */
class Test_Plugin_Review extends AIOSEOP_Notices_TestCase {

	/**
	 * Mock Single Notice
	 *
	 * @since 2.4.5.1
	 *
	 * @return array
	 */
	protected function mock_notice() {
		return aioseop_notice_review_plugin();
	}
}

