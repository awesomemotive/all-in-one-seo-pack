<?php
namespace AIOSEO\Plugin\Common\Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BadBotBlocker {
	/**
	 * Holds the logger.
	 *
	 * @since 4.0.0
	 *
	 * @var mixed
	 */
	private $log = null;

	/**
	 * An array of bad referers.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $refererList = [
		'semalt.com',
		'kambasoft.com',
		'savetubevideo.com',
		'buttons-for-website.com',
		'sharebutton.net',
		'soundfrost.org',
		'srecorder.com',
		'softomix.com',
		'softomix.net',
		'myprintscreen.com',
		'joinandplay.me',
		'fbfreegifts.com',
		'openmediasoft.com',
		'zazagames.org',
		'extener.org',
		'openfrost.com',
		'openfrost.net',
		'googlsucks.com',
		'best-seo-offer.com',
		'buttons-for-your-website.com',
		'www.Get-Free-Traffic-Now.com',
		'best-seo-solution.com',
		'buy-cheap-online.info',
		'site3.free-share-buttons.com',
		'webmaster-traffic.com'
	];

	/**
	 * An array of bad bots.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $botList = [
		'Abonti',
		'aggregator',
		'AhrefsBot',
		'asterias',
		'BDCbot',
		'BLEXBot',
		'BuiltBotTough',
		'Bullseye',
		'BunnySlippers',
		'ca-crawler',
		'CCBot',
		'Cegbfeieh',
		'CheeseBot',
		'CherryPicker',
		'CopyRightCheck',
		'cosmos',
		'Crescent',
		'discobot',
		'DittoSpyder',
		'DotBot',
		'Download Ninja',
		'EasouSpider',
		'EmailCollector',
		'EmailSiphon',
		'EmailWolf',
		'EroCrawler',
		'ExtractorPro',
		'Fasterfox',
		'FeedBooster',
		'Foobot',
		'Genieo',
		'grub-client',
		'Harvest',
		'hloader',
		'httplib',
		'HTTrack',
		'humanlinks',
		'ieautodiscovery',
		'InfoNaviRobot',
		'IstellaBot',
		'Java/1.',
		'JennyBot',
		'k2spider',
		'Kenjin Spider',
		'Keyword Density/0.9',
		'larbin',
		'LexiBot',
		'libWeb',
		'libwww',
		'LinkextractorPro',
		'linko',
		'LinkScan/8.1a Unix',
		'LinkWalker',
		'LNSpiderguy',
		'lwp-trivial',
		'magpie',
		'Mata Hari',
		'MaxPointCrawler',
		'MegaIndex',
		'Microsoft URL Control',
		'MIIxpc',
		'Mippin',
		'Missigua Locator',
		'Mister PiX',
		'MJ12bot',
		'moget',
		'MSIECrawler',
		'NetAnts',
		'NICErsPRO',
		'Niki-Bot',
		'NPBot',
		'Nutch',
		'Offline Explorer',
		'Openfind',
		'panscient.com',
		'PHP/5.{',
		'ProPowerBot/2.14',
		'ProWebWalker',
		'Python-urllib',
		'QueryN Metasearch',
		'RepoMonkey',
		'SISTRIX',
		'sitecheck.Internetseer.com',
		'SiteSnagger',
		'SnapPreviewBot',
		'Sogou',
		'SpankBot',
		'spanner',
		'spbot',
		'Spinn3r',
		'suzuran',
		'Szukacz/1.4',
		'Teleport',
		'Telesoft',
		'The Intraformant',
		'TheNomad',
		'TightTwatBot',
		'Titan',
		'toCrawl/UrlDispatcher',
		'True_Robot',
		'turingos',
		'TurnitinBot',
		'UbiCrawler',
		'UnisterBot',
		'URLy Warning',
		'VCI',
		'WBSearchBot',
		'Web Downloader/6.9',
		'Web Image Collector',
		'WebAuto',
		'WebBandit',
		'WebCopier',
		'WebEnhancer',
		'WebmasterWorldForumBot',
		'WebReaper',
		'WebSauger',
		'Website Quester',
		'Webster Pro',
		'WebStripper',
		'WebZip',
		'Wotbox',
		'wsr-agent',
		'WWW-Collector-E',
		'Xenu',
		'Zao',
		'Zeus',
		'ZyBORG',
		'coccoc',
		'Incutio',
		'lmspider',
		'memoryBot',
		'serf',
		'Unknown',
		'uptime files',
	];

	/**
	 * Initialize the blocker.
	 *
	 * @since 4.0.0
	 */
	public function init() {
		if ( aioseo()->options->deprecated->tools->blocker->blockBots ) {
			$uploadDirectory = wp_upload_dir();
			$logDirectory    = $uploadDirectory['basedir'] . '/aioseo/logs/';
			if ( wp_mkdir_p( $logDirectory ) ) {
				$fs       = aioseo()->core->fs;
				$filePath = $logDirectory . 'aioseo-bad-bot-blocker.log';
				if ( ! $fs->exists( $filePath ) ) {
					$fs->touch( $filePath );
				}

				if ( $fs->exists( $filePath ) ) {
					$this->log = new \AIOSEO\Vendor\Monolog\Logger( 'aioseo-bad-bot-blocker' );
					$this->log->pushHandler( new \AIOSEO\Vendor\Monolog\Handler\StreamHandler( $filePath ) );
				}
			}

			$blockReferer = aioseo()->options->deprecated->tools->blocker->blockReferer;
			$track        = aioseo()->options->deprecated->tools->blocker->track;
			$ip           = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			$ip           = aioseo()->helpers->validateIp( $ip ) ? $ip : __( '(Invalid IP)', 'all-in-one-seo-pack' );
			if ( ! $this->allowBot() ) {
				if ( $track ) {
					$userAgent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : __( 'Unknown User Agent', 'all-in-one-seo-pack' );
					// Translators: 1 - The IP address. 2 - The user agent.
					$this->track( sprintf( __( 'Blocked bot with IP %1$s -- matched user agent %2$s found in blocklist.', 'all-in-one-seo-pack' ), $ip, $userAgent ) );
				}
				status_header( 503 );
				exit;
			} elseif ( $blockReferer && $this->isBadReferer() ) {
				status_header( 503 );
				if ( $track ) {
					$referer = ! empty( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
					// Translators: 1 - The IP address. 2 - The referer.
					$this->track( sprintf( __( 'Blocked bot with IP %1$s -- matched referer %2$s found in blocklist.', 'all-in-one-seo-pack' ), $ip, $referer ) );
				}
				status_header( 503 );
				exit;
			}
		}
	}

	/**
	 * Get the size of the log file.
	 *
	 * @since 4.0.0
	 *
	 * @return integer The size of the log file.
	 */
	public function getLogSize() {
		$uploadDirectory = wp_upload_dir();
		$logDirectory    = $uploadDirectory['basedir'] . '/aioseo/logs/';
		$filePath        = $logDirectory . 'aioseo-bad-bot-blocker.log';
		$fs              = aioseo()->core->fs;
		if ( $fs->exists( $filePath ) ) {
			return $fs->size( $filePath );
		}

		return 0;
	}

	/**
	 * Clears the log for the bad bot blocker.
	 *
	 * @since 4.0.0
	 *
	 * @return integer The file size.
	 */
	public function clearLog() {
		$uploadDirectory = wp_upload_dir();
		$logDirectory    = $uploadDirectory['basedir'] . '/aioseo/logs/';
		$filePath        = $logDirectory . 'aioseo-bad-bot-blocker.log';
		$fs              = aioseo()->core->fs;
		if ( $fs->exists( $filePath ) ) {
			$fs->putContents( $filePath, '' );
		}

		return $this->getLogSize();
	}

	/**
	 * Returns the bot list.
	 *
	 * @since 4.0.0
	 *
	 * @return array The bot list.
	 */
	public function getBotList() {
		return $this->botList;
	}

	/**
	 * Returns the referer list.
	 *
	 * @since 4.0.0
	 *
	 * @return array The referer list.
	 */
	public function getRefererList() {
		return $this->refererList;
	}

	/**
	 * Whether or not to allow the bot through.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if this is a good bot and we can allow it through.
	 */
	private function allowBot() {
		$allow = true;
		if ( ! $this->isGoodBot() && $this->isBadBot() && ! is_user_logged_in() ) {
			$allow = false;
		}

		return apply_filters( 'aioseo_allow_bot', $allow );
	}

	/**
	 * Is this a good bot?
	 *
	 * @see  Original code, thanks to Sean M. Brown.
	 * @link http://smbrown.wordpress.com/2009/04/29/verify-googlebot-forward-reverse-dns/
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if this is a good bot.
	 */
	private function isGoodBot() {
		$botList = [
			'Yahoo! Slurp' => 'crawl.yahoo.net',
			'googlebot'    => '.googlebot.com',
			'msnbot'       => 'search.msn.com',
		];
		$botList = apply_filters( 'aioseo_good_bot_list', $botList );
		if ( ! empty( $botList ) ) {
			if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				return false;
			}
			$ua  = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			$uas = $this->prepareList( $botList );
			if ( preg_match( '/' . $uas . '/i', $ua ) ) {
				$ip           = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				$hostname     = gethostbyaddr( $ip );
				$ipByHostName = gethostbyname( $hostname );
				if ( $ipByHostName === $ip ) {
					$hosts = array_values( $botList );
					foreach ( $hosts as $k => $h ) {
						$hosts[ $k ] = preg_quote( $h ) . '$';
					}
					$hosts = join( '|', $hosts );
					if ( preg_match( '/' . $hosts . '/i', $hostname ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Is this a bad bot?
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if it is a bad bot.
	 */
	private function isBadBot() {
		$botList = aioseo()->options->deprecated->tools->blocker->custom->enable
			? explode( "\n", aioseo()->options->deprecated->tools->blocker->custom->bots )
			: $this->botList;
		$botList = apply_filters( 'aioseo_bad_bot_list', $botList );
		if ( ! empty( $botList ) ) {
			if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				return false;
			}
			$ua  = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			$uas = $this->prepareList( $botList );
			if ( preg_match( '/' . $uas . '/i', $ua ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is this a bad referer?
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if this is a bad referer.
	 */
	private function isBadReferer() {
		$refererList = aioseo()->options->deprecated->tools->blocker->custom->enable
			? explode( "\n", aioseo()->options->deprecated->tools->blocker->custom->referer )
			: $this->refererList;
		$refererList = apply_filters( 'aioseo_bad_referer_list', $refererList );

		if ( ! empty( $refererList ) && ! empty( $_SERVER ) && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$referer = esc_url_raw( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
			$regex   = $this->prepareList( $refererList );
			if ( preg_match( '/' . $regex . '/i', $referer ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Quote List for Regex
	 *
	 * @since 4.0.0
	 *
	 * @param        $list
	 * @param string $quote
	 * @return string
	 */
	private function prepareList( $list, $quote = '/' ) {
		$regex = '';
		$cont  = 0;
		foreach ( $list as $l ) {
			$trim_l = trim( $l );
			if ( ! empty( $trim_l ) ) {
				if ( $cont ) {
					$regex .= '|';
				}
				$cont   = 1;
				$regex .= preg_quote( trim( $l ), $quote );
			}
		}

		return $regex;
	}

	/**
	 * Tracks the bad bot that was blocked.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $message The message to log.
	 * @return void
	 */
	public function track( $message ) {
		if ( $this->log ) {
			$this->log->info( $message );
		}
	}
}