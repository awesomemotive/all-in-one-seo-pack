<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Gemini provider implementation.
 *
 * @since 4.9.0
 */
class Gemini extends Provider {
	/**
	 * The provider name.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $name = 'Gemini';

	/**
	 * The API endpoint base.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/';

	/**
	 * The model to use.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $model = 'gemini-1.5-pro';

	/**
	 * Generate content using Gemini.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $prompt      The prompt for content generation.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function generateContent( $prompt, $options = [] ) {
		if ( ! $this->validateApiKey() ) {
			return [
				'success' => false,
				'error'   => 'Gemini API key is not configured.',
			];
		}

		$systemPrompt = isset( $options['system_prompt'] ) ? $options['system_prompt'] : 'You are a professional content writer specializing in SEO-optimized content.';
		$model        = isset( $options['model'] ) ? $options['model'] : $this->model;

		// Gemini combines system and user prompts
		$combinedPrompt = $systemPrompt . "\n\n" . $prompt;

		$body = [
			'contents'         => [
				[
					'parts' => [
						[
							'text' => $combinedPrompt,
						],
					],
				],
			],
			'generationConfig' => [
				'temperature'    => $this->temperature,
				'maxOutputTokens' => $this->maxTokens,
			],
		];

		$endpoint = $this->endpoint . $model . ':generateContent?key=' . $this->apiKey;

		$response = $this->makeRequest( $endpoint, $body, [] );

		if ( ! $response['success'] ) {
			return $response;
		}

		$content = $response['data']['candidates'][0]['content']['parts'][0]['text'] ?? '';

		return [
			'success' => true,
			'content' => $content,
			'usage'   => $response['data']['usageMetadata'] ?? [],
		];
	}

	/**
	 * Analyze SEO for given content.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $content     The content to analyze.
	 * @param  string $keyword     The target keyword.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function analyzeSeo( $content, $keyword = '', $options = [] ) {
		$prompt = "Analyze the following content for SEO optimization:\n\n";

		if ( ! empty( $keyword ) ) {
			$prompt .= "Target Keyword: {$keyword}\n\n";
		}

		$prompt .= "Content:\n{$content}\n\n";
		$prompt .= "Provide a comprehensive SEO analysis covering:\n";
		$prompt .= "1. Keyword usage, density, and placement\n";
		$prompt .= "2. Content structure (headings, subheadings, hierarchy)\n";
		$prompt .= "3. Readability analysis (Flesch score, sentence length, vocabulary)\n";
		$prompt .= "4. Recommended meta description (150-160 chars, engaging)\n";
		$prompt .= "5. Optimized title tag (50-60 chars, keyword-rich)\n";
		$prompt .= "6. Internal linking suggestions with anchor text\n";
		$prompt .= "7. Overall SEO score (0-100) with detailed breakdown\n";
		$prompt .= "8. Prioritized recommendations for improvement\n";
		$prompt .= "9. E-E-A-T signals (Experience, Expertise, Authority, Trust)\n";
		$prompt .= "10. Content gaps and missing elements\n\n";
		$prompt .= "Format as valid JSON:\n";
		$prompt .= '{"score": 85, "keyword_density": 2.5, "readability": {"score": 70, "level": "good"}, "meta_description": "...", "title": "...", "recommendations": ["..."], "eeat_signals": ["..."], "content_gaps": ["..."]}';

		$options['system_prompt'] = 'You are an expert SEO analyst with comprehensive knowledge of Google\'s ranking algorithms, E-E-A-T principles, and content optimization strategies. Provide actionable, data-driven SEO analysis.';

		return $this->generateContent( $prompt, $options );
	}

	/**
	 * Research keywords and topics.
	 *
	 * @since 4.9.0
	 *
	 * @param  string $topic       The topic to research.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function researchKeywords( $topic, $options = [] ) {
		$prompt = "Conduct in-depth keyword research for: {$topic}\n\n";
		$prompt .= "Provide comprehensive analysis:\n";
		$prompt .= "1. Primary keywords:\n";
		$prompt .= "   - Keyword phrase\n";
		$prompt .= "   - Estimated search volume category (high/medium/low)\n";
		$prompt .= "   - Competition level (high/medium/low)\n";
		$prompt .= "   - Search intent (informational/navigational/transactional/commercial)\n";
		$prompt .= "2. Long-tail keyword variations (3-5+ words, specific)\n";
		$prompt .= "3. Question-based keywords:\n";
		$prompt .= "   - Who/What/Where/When/Why/How questions\n";
		$prompt .= "   - Voice search optimized phrases\n";
		$prompt .= "4. Related topics and subtopics for content clusters\n";
		$prompt .= "5. LSI keywords (semantic keywords for context)\n";
		$prompt .= "6. Trending keywords and emerging topics\n";
		$prompt .= "7. Seasonal keyword opportunities\n";
		$prompt .= "8. Competitor keyword gaps\n";
		$prompt .= "9. Content structure blueprint\n";
		$prompt .= "10. Featured snippet opportunities\n\n";
		$prompt .= "Format as valid JSON:\n";
		$prompt .= '{"primary_keywords": [{"keyword": "...", "volume": "high", "competition": "medium", "intent": "informational"}], "long_tail": ["..."], "questions": ["..."], "related_topics": ["..."], "lsi_keywords": ["..."], "trending": ["..."], "content_structure": ["..."], "snippet_opportunities": ["..."]}';

		$options['system_prompt'] = 'You are an expert keyword researcher and content strategist with deep understanding of search trends, user intent, and SEO best practices. Provide comprehensive, actionable keyword research.';

		return $this->generateContent( $prompt, $options );
	}

	/**
	 * Optimize content for AEO (Answer Engine Optimization).
	 *
	 * @since 4.9.0
	 *
	 * @param  string $content     The content to optimize.
	 * @param  array  $questions   Related questions.
	 * @param  array  $options     Additional options.
	 * @return array               Response array.
	 */
	public function optimizeForAeo( $content, $questions = [], $options = [] ) {
		$prompt = "Optimize this content for Answer Engine Optimization (AEO) to maximize visibility across:\n";
		$prompt .= "- AI search engines (Google SGE, Perplexity, etc.)\n";
		$prompt .= "- Voice assistants (Google Assistant, Alexa, Siri)\n";
		$prompt .= "- Featured snippets and rich results\n";
		$prompt .= "- Conversational AI platforms\n\n";
		$prompt .= "Original Content:\n{$content}\n\n";

		if ( ! empty( $questions ) ) {
			$prompt .= "Key Questions to Address:\n";
			foreach ( $questions as $question ) {
				$prompt .= "- {$question}\n";
			}
			$prompt .= "\n";
		}

		$prompt .= "AEO Optimization Requirements:\n";
		$prompt .= "1. Featured Snippet Optimization:\n";
		$prompt .= "   - Paragraph snippets: Direct 40-60 word answers\n";
		$prompt .= "   - List snippets: Numbered or bulleted lists\n";
		$prompt .= "   - Table snippets: Comparative data tables\n";
		$prompt .= "2. Voice Search Optimization:\n";
		$prompt .= "   - Natural, conversational language\n";
		$prompt .= "   - Question-and-answer format\n";
		$prompt .= "   - Long-tail keyword phrases\n";
		$prompt .= "3. FAQ Schema Ready:\n";
		$prompt .= "   - Clear Q&A sections\n";
		$prompt .= "   - Structured as 'Question:' followed by 'Answer:'\n";
		$prompt .= "4. People Also Ask (PAA) Content:\n";
		$prompt .= "   - Related questions with concise answers\n";
		$prompt .= "   - Hierarchical question structure\n";
		$prompt .= "5. Content Structure:\n";
		$prompt .= "   - Clear H2/H3 headings (many as questions)\n";
		$prompt .= "   - Summary boxes for key information\n";
		$prompt .= "   - Scannable bullet points\n";
		$prompt .= "6. Entity Optimization:\n";
		$prompt .= "   - Clear definitions of key entities\n";
		$prompt .= "   - Proper nouns and specific terminology\n";
		$prompt .= "7. Context and Depth:\n";
		$prompt .= "   - Comprehensive but digestible answers\n";
		$prompt .= "   - Supporting data and examples\n\n";
		$prompt .= "Return the fully optimized content with all AEO enhancements. Maintain the core message while restructuring for maximum AI search visibility.";

		$options['system_prompt'] = 'You are an expert in Answer Engine Optimization (AEO) specializing in content optimization for AI-powered search, voice search, and featured snippets. You understand how AI models extract and present information.';
		$options['max_tokens']    = 4000; // Longer output for comprehensive optimization

		return $this->generateContent( $prompt, $options );
	}
}
