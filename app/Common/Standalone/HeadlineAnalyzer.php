<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the headline analysis.
 *
 * @since 4.1.2
 */
class HeadlineAnalyzer {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.2
	 */
	public function __construct() {
		if ( ! is_admin() || wp_doing_cron() ) {
			return;
		}

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue' ] );

		if ( ! aioseo()->options->advanced->headlineAnalyzer ) {
			return;
		}

		add_filter( 'monsterinsights_headline_analyzer_enabled', '__return_false' );
		add_filter( 'exactmetrics_headline_analyzer_enabled', '__return_false' );
	}

	/**
	 * Enqueues the headline analyzer.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function enqueue() {
		if (
			! aioseo()->helpers->isScreenBase( 'post' ) ||
			! aioseo()->access->hasCapability( 'aioseo_page_analysis' )
		) {
			return;
		}

		if ( ! aioseo()->options->advanced->headlineAnalyzer ) {
			return;
		}

		$path = '/vendor/jwhennessey/phpinsight/autoload.php';
		if ( ! aioseo()->core->fs->exists( AIOSEO_DIR . $path ) ) {
			return;
		}
		require AIOSEO_DIR . $path;

		aioseo()->core->assets->load( 'src/vue/standalone/headline-analyzer/main.js' );
	}

	/**
	 * Returns the result of the analsyis.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $title The title.
	 * @return array         The result.
	 */
	public function getResult( $title ) {
		$result = $this->getHeadlineScore( html_entity_decode( $title ) );

		if ( ! empty( $result->err ) ) {
			return false;
		}

		return [
			'result'   => $result,
			'analysed' => ! $result->err,
			'sentence' => ucwords( wp_unslash( sanitize_text_field( $title ) ) ),
			'score'    => ! empty( $result->score ) ? $result->score : 0
		];
	}

	/**
	 * Returns the score.
	 *
	 * @since 4.1.2
	 *
	 * @param  string    $title The title.
	 * @return \stdClass        The result.
	 */
	public function getHeadlineScore( $title ) {
		$result                           = new \stdClass();
		$result->originalExplodedHeadline = explode( ' ', wp_unslash( $title ) );

		// Strip useless characters and whitespace.
		$title = preg_replace( '/[^A-Za-z0-9 ]/', '', $title );
		$title = preg_replace( '!\s+!', ' ', $title );
		$title = strtolower( $title );

		$result->input = $title;

		// If the headline is invalid, return an error.
		if ( ! $title || ' ' === $title || trim( $title ) === '' ) {
			$result->err = true;
			$result->msg = 'The headline is invalid.';

			return $result;
		}

		$totalScore               = 0;
		$explodedHeadline         = explode( ' ', $title );
		$result->explodedHeadline = $explodedHeadline;
		$result->err              = false;

		// The optimal length is 55 characters.
		$result->length = strlen( str_replace( ' ', '', $title ) );
		$totalScore     = $totalScore + 3;

		//phpcs:disable Squiz.ControlStructures.ControlSignature
		if ( $result->length <= 19 ) { $totalScore += 5; }
		elseif ( $result->length >= 20 && $result->length <= 34 ) { $totalScore += 8; }
		elseif ( $result->length >= 35 && $result->length <= 66 ) { $totalScore += 11; }
		elseif ( $result->length >= 67 && $result->length <= 79 ) { $totalScore += 8; }
		elseif ( $result->length >= 80 ) { $totalScore += 5; }

		// The average headline is 6-7 words long.
		$result->wordCount = count( $explodedHeadline );
		$totalScore        = $totalScore + 3;

		if ( 0 === $result->wordCount ) { $totalScore = 0; }
		elseif ( $result->wordCount >= 2 && $result->wordCount <= 4 ) { $totalScore += 5; }
		elseif ( $result->wordCount >= 5 && $result->wordCount <= 9 ) { $totalScore += 11; }
		elseif ( $result->wordCount >= 10 && $result->wordCount <= 11 ) { $totalScore += 8; }
		elseif ( $result->wordCount >= 12 ) { $totalScore += 5; }

		// Check for power words, emotional words, etc.
		$result->powerWords               = $this->matchWords( $result->input, $result->explodedHeadline, $this->powerWords() );
		$result->powerWordsPercentage     = count( $result->powerWords ) / $result->wordCount;
		$result->emotionWords             = $this->matchWords( $result->input, $result->explodedHeadline, $this->emotionPowerWords() );
		$result->emotionalWordsPercentage = count( $result->emotionWords ) / $result->wordCount;
		$result->commonWords              = $this->matchWords( $result->input, $result->explodedHeadline, $this->commonWords() );
		$result->commonWordsPercentage    = count( $result->commonWords ) / $result->wordCount;
		$result->uncommonWords            = $this->matchWords( $result->input, $result->explodedHeadline, $this->uncommonWords() );
		$result->uncommonWordsPercentage  = count( $result->uncommonWords ) / $result->wordCount;
		$result->detectedWordTypes        = [];

		if ( $result->emotionalWordsPercentage < 0.1 ) {
			$result->detectedWordTypes[] = 'emotion';
		} else {
			$totalScore = $totalScore + 15;
		}

		if ( $result->commonWordsPercentage < 0.2 ) {
			$result->detectedWordTypes[] = 'common';
		} else {
			$totalScore = $totalScore + 11;
		}

		if ( $result->uncommonWordsPercentage < 0.1 ) {
			$result->detectedWordTypes[] = 'uncommon';
		} else {
			$totalScore = $totalScore + 15;
		}

		if ( count( $result->powerWords ) < 1 ) {
			$result->detectedWordTypes[] = 'power';
		} else {
			$totalScore = $totalScore + 19;
		}

		if (
			$result->emotionalWordsPercentage >= 0.1 &&
			$result->commonWordsPercentage >= 0.2 &&
			$result->uncommonWordsPercentage >= 0.1 &&
			count( $result->powerWords ) >= 1
		) {
			$totalScore = $totalScore + 3;
		}

		$sentiment         = new \PHPInsight\Sentiment();
		$sentimentClass    = $sentiment->categorise( $title );
		$result->sentiment = $sentimentClass;

		$totalScore = $totalScore + ( 'pos' === $result->sentiment ? 10 : ( 'neg' === $result->sentiment ? 10 : 7 ) );

		$headlineTypes = [];
		if ( strpos( $title, 'how to' ) !== false || strpos( $title, 'howto' ) !== false ) {
			$headlineTypes[] = __( 'How-To', 'all-in-one-seo-pack' );
			$totalScore      = $totalScore + 7;
		}

		$listWords = array_intersect( $explodedHeadline, $this->numericalIndicators() );
		if ( preg_match( '~[0-9]+~', $title ) || ! empty( $listWords ) ) {
			$headlineTypes[] = __( 'List', 'all-in-one-seo-pack' );
			$totalScore      = $totalScore + 7;
		}

		if ( in_array( $explodedHeadline[0], $this->primaryQuestionIndicators(), true ) ) {
			if ( in_array( $explodedHeadline[1], $this->secondaryQuestionIndicators(), true ) ) {
				$headlineTypes[] = __( 'Question', 'all-in-one-seo-pack' );
				$totalScore      = $totalScore + 7;
			}
		}

		if ( empty( $headlineTypes ) ) {
			$headlineTypes[] = __( 'General', 'all-in-one-seo-pack' );
			$totalScore      = $totalScore + 5;
		}

		$result->headlineTypes = $headlineTypes;
		$result->score         = $totalScore >= 93 ? 93 : $totalScore;

		return $result;
	}

	/**
	* Tries to find matches for power words, emotional words, etc. in the headline.
	*
	* @since 4.1.2
	*
	* @param  string $headline         The headline.
	* @param  array  $explodedHeadline The exploded headline.
	* @param  array  $words            The words to match.
	* @return array                    The matches that were found.
	*/
	public function matchWords( $headline, $explodedHeadline, $words ) {
		$foundMatches = [];
		foreach ( $words as $word ) {
			$strippedWord = preg_replace( '/[^A-Za-z0-9 ]/', '', $word );

			// Check if word is a phrase.
			if ( strpos( $word, ' ' ) !== false ) {
				if ( strpos( $headline, $strippedWord ) !== false ) {
					$foundMatches[] = $word;
				}
				continue;
			}
			// Check if it is a single word.
			if ( in_array( $strippedWord, $explodedHeadline, true ) ) {
				$foundMatches[] = $word;
			}
		}

		return $foundMatches;
	}

	/**
	 * Returns a list of numerical indicators.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of numerical indicators.
	 */
	private function numericalIndicators() {
		return [
			'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'eleven', 'twelve', 'thirt', 'fift', 'hundred', 'thousand' // phpcs:ignore Generic.Files.LineLength.MaxExceeded, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		];
	}

	/**
	 * Returns a list of primary question indicators.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of primary question indicators.
	 */
	private function primaryQuestionIndicators() {
		return [
			'where', 'when', 'how', 'what', 'have', 'has', 'does', 'do', 'can', 'are', 'will' // phpcs:ignore Generic.Files.LineLength.MaxExceeded, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		];
	}

	/**
	 * Returns a list of secondary question indicators.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of secondary question indicators.
	 */
	private function secondaryQuestionIndicators() {
		return [
			'you', 'they', 'he', 'she', 'your', 'it', 'they', 'my', 'have', 'has', 'does', 'do', 'can', 'are', 'will' // phpcs:ignore Generic.Files.LineLength.MaxExceeded, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		];
	}

	/**
	 * Returns a list of power words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of power words.
	 */
	private function powerWords() {
		return [
			'great', 'free', 'focus', 'remarkable', 'confidential', 'sale', 'wanted', 'obsession', 'sizable', 'new', 'absolutely lowest', 'surging', 'wonderful', 'professional', 'interesting', 'revisited', 'delivered', 'guaranteed', 'challenge', 'unique', 'secrets', 'special', 'lifetime', 'bargain', 'scarce', 'tested', 'highest', 'hurry', 'alert famous', 'improved', 'expert', 'daring', 'strong', 'immediately', 'advice', 'pioneering', 'unusual', 'limited', 'the truth about', 'destiny', 'outstanding', 'simplistic', 'compare', 'unsurpassed', 'energy', 'powerful', 'colorful', 'genuine', 'instructive', 'big', 'affordable', 'informative', 'liberal', 'popular', 'ultimate', 'mainstream', 'rare', 'exclusive', 'willpower', 'complete', 'edge', 'valuable', 'attractive', 'last chance', 'superior', 'how to', 'easily', 'exploit', 'unparalleled', 'endorsed', 'approved', 'quality', 'fascinating', 'unlimited', 'competitive', 'gigantic', 'compromise', 'discount', 'full', 'love', 'odd', 'fundamentals', 'mammoth', 'lavishly', 'bottom line', 'under priced', 'innovative', 'reliable', 'zinger', 'suddenly', 'it\'s here', 'terrific', 'simplified', 'perspective', 'just arrived', 'breakthrough', 'tremendous', 'launching', 'sure fire', 'emerging', 'helpful', 'skill', 'soar', 'profitable', 'special offer', 'reduced', 'beautiful', 'sampler', 'technology', 'better', 'crammed', 'noted', 'selected', 'shrewd', 'growth', 'luxury', 'sturdy', 'enormous', 'promising', 'unconditional', 'wealth', 'spotlight', 'astonishing', 'timely', 'successful', 'useful', 'imagination', 'bonanza', 'opportunities', 'survival', 'greatest', 'security', 'last minute', 'largest', 'high tech', 'refundable', 'monumental', 'colossal', 'latest', 'quickly', 'startling', 'now', 'important', 'revolutionary', 'quick', 'unlock', 'urgent', 'miracle', 'easy', 'fortune', 'amazing', 'magic', 'direct', 'authentic', 'exciting', 'proven', 'simple', 'announcing', 'portfolio', 'reward', 'strange', 'huge gift', 'revealing', 'weird', 'value', 'introducing', 'sensational', 'surprise', 'insider', 'practical', 'excellent', 'delighted', 'download' // phpcs:ignore Generic.Files.LineLength.MaxExceeded, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		];
	}

	/**
	 * Returns a list of common words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of common words.
	 */
	private function commonWords() {
		return [
			'a', 'for', 'about', 'from', 'after', 'get', 'all', 'has', 'an', 'have', 'and', 'he', 'are', 'her', 'as', 'his', 'at', 'how', 'be', 'I', 'but', 'if', 'by', 'in', 'can', 'is', 'did', 'it', 'do', 'just', 'ever', 'like', 'll', 'these', 'me', 'they', 'most', 'things', 'my', 'this', 'no', 'to', 'not', 'up', 'of', 'was', 'on', 'what', 're', 'when', 'she', 'who', 'sould', 'why', 'so', 'will', 'that', 'with', 'the', 'you', 'their', 'your', 'there' // phpcs:ignore Generic.Files.LineLength.MaxExceeded, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		];
	}

	/**
	 * Returns a list of uncommon words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of uncommon words.
	 */
	private function uncommonWords() {
		return [
			'actually', 'happened', 'need', 'thing', 'awesome', 'heart', 'never', 'think', 'baby', 'here', 'new', 'time', 'beautiful', 'its', 'now', 'valentines', 'being', 'know', 'old', 'video', 'best', 'life', 'one', 'want', 'better', 'little', 'out', 'watch', 'boy', 'look', 'people', 'way', 'dog', 'love', 'photos', 'ways', 'down', 'made', 'really', 'world', 'facebook', 'make', 'reasons', 'year', 'first', 'makes', 'right', 'years', 'found', 'man', 'see', 'you’ll', 'girl', 'media', 'seen', 'good', 'mind', 'social', 'guy', 'more', 'something' // phpcs:ignore Generic.Files.LineLength.MaxExceeded, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		];
	}

	/**
	 * Returns a list of emotional power words.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of emotional power words.
	 */
	private function emotionPowerWords() {
		return [
			'destroy', 'extra', 'in a', 'devastating', 'eye-opening', 'gift', 'in the world', 'devoted', 'fail', 'in the', 'faith', 'grateful', 'inexpensive', 'dirty', 'famous', 'disastrous', 'fantastic', 'greed', 'grit', 'insanely', 'disgusting', 'fearless', 'disinformation', 'feast', 'insidious', 'dollar', 'feeble', 'gullible', 'double', 'fire', 'hack', 'fleece', 'had enough', 'invasion', 'drowning', 'floundering', 'happy', 'ironclad', 'dumb', 'flush', 'hate', 'irresistibly', 'hazardous', 'is the', 'fool', 'is what happens when', 'fooled', 'helpless', 'it looks like a', 'embarrass', 'for the first time', 'help are the', 'jackpot', 'forbidden', 'hidden', 'jail', 'empower', 'force-fed', 'high', 'jaw-dropping', 'forgotten', 'jeopardy', 'energize', 'hoax', 'jubilant', 'foul', 'hope', 'killer', 'frantic', 'horrific', 'know it all', 'epic', 'how to make', 'evil', 'freebie', 'frenzy', 'hurricane', 'excited', 'fresh on the mind', 'frightening', 'hypnotic', 'lawsuit', 'frugal', 'illegal', 'fulfill', 'lick', 'explode', 'lies', 'exposed', 'gambling', 'like a normal', 'nightmare', 'results', 'line', 'no good', 'pound', 'loathsome', 'no questions asked', 'revenge', 'lonely', 'looks like a', 'obnoxious', 'preposterous', 'revolting', 'looming', 'priced', 'lost', 'prison', 'lowest', 'of the', 'privacy', 'rich', 'lunatic', 'off-limits', 'private', 'risky', 'lurking', 'offer', 'prize', 'ruthless', 'lust', 'official', 'luxurious', 'on the', 'profit', 'scary', 'lying', 'outlawed', 'protected', 'scream', 'searing', 'overcome', 'provocative', 'make you', 'painful', 'pummel', 'secure', 'pale', 'punish', 'marked down', 'panic', 'quadruple', 'seductively', 'massive', 'pay zero', 'seize', 'meltdown', 'payback', 'might look like a', 'peril', 'mind-blowing', 'shameless', 'minute', 'rave', 'shatter', 'piranha', 'reckoning', 'shellacking', 'mired', 'pitfall', 'reclaim', 'mistakes', 'plague', 'sick and tired', 'money', 'played', 'refugee', 'silly', 'money-grubbing', 'pluck', 'refund', 'moneyback', 'plummet', 'plunge', 'murder', 'pointless', 'sinful', 'myths', 'poor', 'remarkably', 'six-figure', 'never again', 'research', 'surrender', 'to the', 'varify', 'skyrocket', 'toxic', 'vibrant', 'slaughter', 'swindle', 'trap', 'victim', 'sleazy', 'taboo', 'treasure', 'victory', 'smash', 'tailspin', 'vindication', 'smug', 'tank', 'triple', 'viral', 'smuggled', 'tantalizing', 'triumph', 'volatile', 'sniveling', 'targeted', 'truth', 'vulnerable', 'snob', 'tawdry', 'try before you buy', 'tech', 'turn the tables', 'wanton', 'soaring', 'warning', 'teetering', 'unauthorized', 'spectacular', 'temporary fix', 'unbelievably', 'spine', 'tempting', 'uncommonly', 'what happened', 'spirit', 'what happens when', 'terror', 'under', 'what happens', 'staggering', 'underhanded', 'what this', 'that will make you', 'undo","when you see', 'that will make', 'unexpected', 'when you', 'strangle', 'that will', 'whip', 'the best', 'whopping', 'stuck up', 'the ranking of', 'wicked', 'stunning', 'the most', 'will make you', 'stupid', 'the reason why is', 'unscrupulous', 'thing ive ever seen', 'withheld', 'this is the', 'this is what happens', 'unusually', 'wondrous', 'this is what', 'uplifting', 'worry', 'sure', 'this is', 'wounded', 'surge', 'thrilled', 'you need to know', 'thrilling', 'valor', 'you need to', 'you see what', 'surprising', 'tired', 'you see', 'surprisingly', 'to be', 'vaporize' // phpcs:ignore Generic.Files.LineLength.MaxExceeded, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		];
	}
}