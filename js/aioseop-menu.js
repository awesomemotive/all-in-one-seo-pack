/**
 * AIOSEOP menu.
 *
 * Contains all functions that affect the AIOSEOP menu in the sidebar.
 *
 * @since  3.0
 * @package all-in-one-seo-pack
 */
(function($) {

    /**
     * Opens Upgrade to Pro link in AIOSEOP menu as new tab.
     */
    function upgrade_link_aioseop_menu_new_tab() {
        $('#toplevel_page_all-in-one-seo-pack-aioseop_class ul li').last().find('a').attr('target','_blank');
    }

    upgrade_link_aioseop_menu_new_tab();

}(jQuery));
