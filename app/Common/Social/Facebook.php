<?php
namespace AIOSEO\Plugin\Common\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Traits;

/**
 * Handles the Open Graph meta.
 *
 * @since 4.0.0
 */
class Facebook {
	use Traits\SocialProfiles;

	/**
	 * Returns the Open Graph image URL.
	 *
	 * @since 4.0.0
	 *
	 * @param  int    $postId The post ID (optional).
	 * @return string         The image URL.
	 */
	public function getImage( $postId = null ) {
		$post = aioseo()->helpers->getPost( $postId );
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$image = aioseo()->options->social->facebook->homePage->image;
			if ( empty( $image ) ) {
				$image = aioseo()->social->image->getImage( 'facebook', aioseo()->options->social->facebook->general->defaultImageSourcePosts, $post );
			}

			return $image;
		}

		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$image = '';
		if ( ! empty( $metaData ) ) {
			$imageSource = ! empty( $metaData->og_image_type ) && 'default' !== $metaData->og_image_type
				? $metaData->og_image_type
				: aioseo()->options->social->facebook->general->defaultImageSourcePosts;

			$image = aioseo()->social->image->getImage( 'facebook', $imageSource, $post );
		}

		// Since we could be on an archive page, let's check again for that default image.
		if ( ! $image ) {
			$image = aioseo()->social->image->getImage( 'facebook', 'default', null );
		}

		if ( ! $image ) {
			$image = aioseo()->helpers->getSiteLogoUrl();
		}

		// Allow users to control the default image per post type.
		return apply_filters(
			'aioseo_opengraph_default_image',
			$image,
			[
				$post,
				$this->getObjectType()
			]
		);
	}

	/**
	 * Returns the width of the Open Graph image.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image width.
	 */
	public function getImageWidth() {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$width = aioseo()->options->social->facebook->homePage->imageWidth;

			return $width ? $width : aioseo()->options->social->facebook->general->defaultImagePostsWidth;
		}

		$metaData = aioseo()->meta->metaData->getMetaData();
		if ( ! empty( $metaData->og_custom_image_width ) ) {
			return $metaData->og_custom_image_width;
		}

		$image = $this->getImage();
		if ( is_array( $image ) ) {
			return $image[1];
		}

		return aioseo()->options->social->facebook->general->defaultImagePostsWidth;
	}

	/**
	 * Returns the height of the Open Graph image.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image height.
	 */
	public function getImageHeight() {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$height = aioseo()->options->social->facebook->homePage->imageHeight;

			return $height ? $height : aioseo()->options->social->facebook->general->defaultImagePostsHeight;
		}

		$metaData = aioseo()->meta->metaData->getMetaData();
		if ( ! empty( $metaData->og_custom_image_height ) ) {
			return $metaData->og_custom_image_height;
		}

		$image = $this->getImage();
		if ( is_array( $image ) ) {
			return $image[2];
		}

		return aioseo()->options->social->facebook->general->defaultImagePostsHeight;
	}

	/**
	 * Returns the Open Graph video URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string The video URL.
	 */
	public function getVideo() {
		$metaData = aioseo()->meta->metaData->getMetaData();

		return ! empty( $metaData->og_video ) ? $metaData->og_video : '';
	}

	/**
	 * Returns the width of the video.
	 *
	 * @since 4.0.0
	 *
	 * @return string The video width.
	 */
	public function getVideoWidth() {
		$metaData = aioseo()->meta->metaData->getMetaData();

		return ! empty( $metaData->og_video_width ) ? $metaData->og_video_width : '';
	}

	/**
	 * Returns the height of the video.
	 *
	 * @since 4.0.0
	 *
	 * @return string The video height.
	 */
	public function getVideoHeight() {
		$metaData = aioseo()->meta->metaData->getMetaData();

		return ! empty( $metaData->og_video_height ) ? $metaData->og_video_height : '';
	}

	/**
	 * Returns the site name.
	 *
	 * @since 4.0.0
	 *
	 * @return string The site name.
	 */
	public function getSiteName() {
		$title = aioseo()->helpers->decodeHtmlEntities( aioseo()->tags->replaceTags( aioseo()->options->social->facebook->general->siteName, get_the_ID() ) );
		if ( ! $title ) {
			$title = aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		}

		return wp_strip_all_tags( $title );
	}

	/**
	 * Returns the Open Graph object type.
	 *
	 * @since 4.0.0
	 *
	 * @return string The object type.
	 */
	public function getObjectType() {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$type = aioseo()->options->social->facebook->homePage->objectType;

			return $type ? $type : 'website';
		}

		if ( is_post_type_archive() ) {
			return 'website';
		}

		$post     = aioseo()->helpers->getPost();
		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		if ( ! empty( $metaData->og_object_type ) && 'default' !== $metaData->og_object_type ) {
			return $metaData->og_object_type;
		}

		$postType          = get_post_type();
		$dynamicOptions    = aioseo()->dynamicOptions->noConflict();
		$defaultObjectType = $dynamicOptions->social->facebook->general->postTypes->has( $postType )
			? $dynamicOptions->social->facebook->general->postTypes->$postType->objectType
			: '';

		return ! empty( $defaultObjectType ) ? $defaultObjectType : 'article';
	}

	/**
	 * Returns the Open Graph title for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|integer $post The post object or ID (optional).
	 * @return string                 The Open Graph title.
	 */
	public function getTitle( $post = null ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$title = aioseo()->meta->title->helpers->prepare( aioseo()->options->social->facebook->homePage->title );

			return $title ? $title : aioseo()->meta->title->getTitle();
		}

		$post     = aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$title = '';
		if ( ! empty( $metaData->og_title ) ) {
			$title = aioseo()->meta->title->helpers->prepare( $metaData->og_title );
		}

		if ( is_post_type_archive() ) {
			$postType = get_queried_object();
			if ( is_a( $postType, 'WP_Post_Type' ) ) {
				$dynamicOptions = aioseo()->dynamicOptions->noConflict();
				if ( $dynamicOptions->searchAppearance->archives->has( $postType->name ) ) {
					$title = aioseo()->meta->title->helpers->prepare( aioseo()->dynamicOptions->searchAppearance->archives->{ $postType->name }->title );
				}
			}
		}

		return $title
			? $title
			: (
				$post
					? aioseo()->meta->title->getPostTitle( $post )
					: $title
			);
	}

	/**
	 * Returns the Open Graph description.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|integer $post The post object or ID (optional).
	 * @return string                 The Open Graph description.
	 */
	public function getDescription( $post = null ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$description = aioseo()->meta->description->helpers->prepare( aioseo()->options->social->facebook->homePage->description );

			return $description ? $description : aioseo()->meta->description->getDescription();
		}

		$post     = aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$description = '';
		if ( ! empty( $metaData->og_description ) ) {
			$description = aioseo()->meta->description->helpers->prepare( $metaData->og_description );
		}

		if ( is_post_type_archive() ) {
			$postType = get_queried_object();
			if ( is_a( $postType, 'WP_Post_Type' ) ) {
				$dynamicOptions = aioseo()->dynamicOptions->noConflict();
				if ( $dynamicOptions->searchAppearance->archives->has( $postType->name ) ) {
					$description = aioseo()->meta->description->helpers->prepare( aioseo()->dynamicOptions->searchAppearance->archives->{ $postType->name }->metaDescription );
				}
			}
		}

		return $description
			? $description
			: (
				$post
					? aioseo()->meta->description->getPostDescription( $post )
					: $description
			);
	}

	/**
	 * Returns the Open Graph article section name.
	 *
	 * @since 4.0.0
	 *
	 * @return string The article section name.
	 */
	public function getSection() {
		$metaData = aioseo()->meta->metaData->getMetaData();

		return ! empty( $metaData->og_article_section ) ? $metaData->og_article_section : '';
	}

	/**
	 * Returns the Open Graph publisher URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string The Open Graph publisher URL.
	 */
	public function getPublisher() {
		if ( ! aioseo()->options->social->profiles->sameUsername->enable ) {
			return aioseo()->options->social->profiles->urls->facebookPageUrl;
		}

		$username = aioseo()->options->social->profiles->sameUsername->username;

		return ( $username && in_array( 'facebookPageUrl', aioseo()->options->social->profiles->sameUsername->included, true ) )
			? 'https://facebook.com/' . $username
			: '';
	}

	/**
	 * Returns the published time.
	 *
	 * @since 4.0.0
	 *
	 * @return string The published time.
	 */
	public function getPublishedTime() {
		$post = aioseo()->helpers->getPost();

		return $post ? aioseo()->helpers->dateTimeToIso8601( $post->post_date_gmt ) : '';
	}

	/**
	 * Returns the last modified time.
	 *
	 * @since 4.0.0
	 *
	 * @return string The last modified time.
	 */
	public function getModifiedTime() {
		$post = aioseo()->helpers->getPost();

		return $post ? aioseo()->helpers->dateTimeToIso8601( $post->post_modified_gmt ) : '';
	}


	/**
	 * Returns the Open Graph author.
	 *
	 * @since 4.0.0
	 *
	 * @return string The Open Graph author.
	 */
	public function getAuthor() {
		$post = aioseo()->helpers->getPost();
		if ( ! is_a( $post, 'WP_Post' ) || ! aioseo()->options->social->facebook->general->showAuthor ) {
			return '';
		}

		$author       = '';
		$userProfiles = $this->getUserProfiles( $post->post_author );
		if ( ! empty( $userProfiles['facebookPageUrl'] ) ) {
			$author = $userProfiles['facebookPageUrl'];
		}

		if ( empty( $author ) ) {
			$author = aioseo()->options->social->facebook->advanced->authorUrl;
		}

		return $author;
	}

	/**
	 * Returns the Open Graph article tags.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of unique keywords.
	 */
	public function getArticleTags() {
		$post     = aioseo()->helpers->getPost();
		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		$tags     = ! empty( $metaData->og_article_tags ) ? aioseo()->meta->keywords->extractMetaKeywords( $metaData->og_article_tags ) : [];

		if (
			$post &&
			aioseo()->options->social->facebook->advanced->enable &&
			aioseo()->options->social->facebook->advanced->generateArticleTags
		) {
			if ( aioseo()->options->social->facebook->advanced->useKeywordsInTags ) {
				$keywords = aioseo()->meta->keywords->getKeywords();
				$keywords = aioseo()->tags->parseCustomFields( $keywords );
				$keywords = aioseo()->meta->keywords->keywordStringToList( $keywords );
				$tags     = array_merge( $tags, $keywords );
			}

			if ( aioseo()->options->social->facebook->advanced->useCategoriesInTags ) {
				$tags = array_merge( $tags, aioseo()->helpers->getAllCategories( $post->ID ) );
			}

			if ( aioseo()->options->social->facebook->advanced->usePostTagsInTags ) {
				$tags = array_merge( $tags, aioseo()->helpers->getAllTags( $post->ID ) );
			}
		}

		return aioseo()->meta->keywords->getUniqueKeywords( $tags, false );
	}

	/**
	 * Retreive the locale.
	 *
	 * @since 4.1.4
	 *
	 * @return string The locale.
	 */
	public function getLocale() {
		$locale = get_locale();

		// These are the locales FB supports.
		$validLocales = [
			'af_ZA', // Afrikaans.
			'ak_GH', // Akan.
			'am_ET', // Amharic.
			'ar_AR', // Arabic.
			'as_IN', // Assamese.
			'ay_BO', // Aymara.
			'az_AZ', // Azerbaijani.
			'be_BY', // Belarusian.
			'bg_BG', // Bulgarian.
			'bp_IN', // Bhojpuri.
			'bn_IN', // Bengali.
			'br_FR', // Breton.
			'bs_BA', // Bosnian.
			'ca_ES', // Catalan.
			'cb_IQ', // Sorani Kurdish.
			'ck_US', // Cherokee.
			'co_FR', // Corsican.
			'cs_CZ', // Czech.
			'cx_PH', // Cebuano.
			'cy_GB', // Welsh.
			'da_DK', // Danish.
			'de_DE', // German.
			'el_GR', // Greek.
			'en_GB', // English (UK).
			'en_PI', // English (Pirate).
			'en_UD', // English (Upside Down).
			'en_US', // English (US).
			'em_ZM',
			'eo_EO', // Esperanto.
			'es_ES', // Spanish (Spain).
			'es_LA', // Spanish.
			'es_MX', // Spanish (Mexico).
			'et_EE', // Estonian.
			'eu_ES', // Basque.
			'fa_IR', // Persian.
			'fb_LT', // Leet Speak.
			'ff_NG', // Fulah.
			'fi_FI', // Finnish.
			'fo_FO', // Faroese.
			'fr_CA', // French (Canada).
			'fr_FR', // French (France).
			'fy_NL', // Frisian.
			'ga_IE', // Irish.
			'gl_ES', // Galician.
			'gn_PY', // Guarani.
			'gu_IN', // Gujarati.
			'gx_GR', // Classical Greek.
			'ha_NG', // Hausa.
			'he_IL', // Hebrew.
			'hi_IN', // Hindi.
			'hr_HR', // Croatian.
			'hu_HU', // Hungarian.
			'ht_HT', // Haitian Creole.
			'hy_AM', // Armenian.
			'id_ID', // Indonesian.
			'ig_NG', // Igbo.
			'is_IS', // Icelandic.
			'it_IT', // Italian.
			'ik_US',
			'iu_CA',
			'ja_JP', // Japanese.
			'ja_KS', // Japanese (Kansai).
			'jv_ID', // Javanese.
			'ka_GE', // Georgian.
			'kk_KZ', // Kazakh.
			'km_KH', // Khmer.
			'kn_IN', // Kannada.
			'ko_KR', // Korean.
			'ks_IN', // Kashmiri.
			'ku_TR', // Kurdish (Kurmanji).
			'ky_KG', // Kyrgyz.
			'la_VA', // Latin.
			'lg_UG', // Ganda.
			'li_NL', // Limburgish.
			'ln_CD', // Lingala.
			'lo_LA', // Lao.
			'lt_LT', // Lithuanian.
			'lv_LV', // Latvian.
			'mg_MG', // Malagasy.
			'mi_NZ', // Maori.
			'mk_MK', // Macedonian.
			'ml_IN', // Malayalam.
			'mn_MN', // Mongolian.
			'mr_IN', // Marathi.
			'ms_MY', // Malay.
			'mt_MT', // Maltese.
			'my_MM', // Burmese.
			'nb_NO', // Norwegian (bokmal).
			'nd_ZW', // Ndebele.
			'ne_NP', // Nepali.
			'nl_BE', // Dutch (Belgie).
			'nl_NL', // Dutch.
			'nn_NO', // Norwegian (nynorsk).
			'nr_ZA', // Southern Ndebele.
			'ns_ZA', // Northern Sotho.
			'ny_MW', // Chewa.
			'om_ET', // Oromo.
			'or_IN', // Oriya.
			'pa_IN', // Punjabi.
			'pl_PL', // Polish.
			'ps_AF', // Pashto.
			'pt_BR', // Portuguese (Brazil).
			'pt_PT', // Portuguese (Portugal).
			'qc_GT', // Quiché.
			'qu_PE', // Quechua.
			'qr_GR',
			'qz_MM', // Burmese (Zawgyi).
			'rm_CH', // Romansh.
			'ro_RO', // Romanian.
			'ru_RU', // Russian.
			'rw_RW', // Kinyarwanda.
			'sa_IN', // Sanskrit.
			'sc_IT', // Sardinian.
			'se_NO', // Northern Sami.
			'si_LK', // Sinhala.
			'su_ID', // Sundanese.
			'sk_SK', // Slovak.
			'sl_SI', // Slovenian.
			'sn_ZW', // Shona.
			'so_SO', // Somali.
			'sq_AL', // Albanian.
			'sr_RS', // Serbian.
			'ss_SZ', // Swazi.
			'st_ZA', // Southern Sotho.
			'sv_SE', // Swedish.
			'sw_KE', // Swahili.
			'sy_SY', // Syriac.
			'sz_PL', // Silesian.
			'ta_IN', // Tamil.
			'te_IN', // Telugu.
			'tg_TJ', // Tajik.
			'th_TH', // Thai.
			'tk_TM', // Turkmen.
			'tl_PH', // Filipino.
			'tl_ST', // Klingon.
			'tn_BW', // Tswana.
			'tr_TR', // Turkish.
			'ts_ZA', // Tsonga.
			'tt_RU', // Tatar.
			'tz_MA', // Tamazight.
			'uk_UA', // Ukrainian.
			'ur_PK', // Urdu.
			'uz_UZ', // Uzbek.
			've_ZA', // Venda.
			'vi_VN', // Vietnamese.
			'wo_SN', // Wolof.
			'xh_ZA', // Xhosa.
			'yi_DE', // Yiddish.
			'yo_NG', // Yoruba.
			'zh_CN', // Simplified Chinese (China).
			'zh_HK', // Traditional Chinese (Hong Kong).
			'zh_TW', // Traditional Chinese (Taiwan).
			'zu_ZA', // Zulu.
			'zz_TR', // Zazaki.
		];

		// Catch some weird locales served out by WP that are not easily doubled up.
		$fixLocales = [
			'ca' => 'ca_ES',
			'en' => 'en_US',
			'el' => 'el_GR',
			'et' => 'et_EE',
			'ja' => 'ja_JP',
			'sq' => 'sq_AL',
			'uk' => 'uk_UA',
			'vi' => 'vi_VN',
			'zh' => 'zh_CN',
		];

		if ( isset( $fixLocales[ $locale ] ) ) {
			$locale = $fixLocales[ $locale ];
		}

		// Convert locales like "es" to "es_ES", in case that works for the given locale (sometimes it does).
		if ( 2 === strlen( $locale ) ) {
			$locale = strtolower( $locale ) . '_' . strtoupper( $locale );
		}

		// Check to see if the locale is a valid FB one, if not, use en_US as a fallback.
		if ( ! in_array( $locale, $validLocales, true ) ) {
			$locale = strtolower( substr( $locale, 0, 2 ) ) . '_' . strtoupper( substr( $locale, 0, 2 ) );

			if ( ! in_array( $locale, $validLocales, true ) ) {
				$locale = 'en_US';
			}
		}

		return apply_filters( 'aioseo_og_locale', $locale );
	}
}