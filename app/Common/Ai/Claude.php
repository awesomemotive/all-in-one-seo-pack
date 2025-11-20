<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Claude (Anthropic) provider implementation.
 *
 * @since 4.9.0
 */
class Claude extends Provider {
	/**
	 * The provider name.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $name = 'Claude';

	/**
	 * The API endpoint.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.anthropic.com/v1/messages';

	/**
	 * The model to use.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $model = 'claude-3-5-sonnet-20241022';

	/**
	 * Generate content using Claude.
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
				'error'   => 'Claude API key is not configured.',
			];
		}

		$systemPrompt = isset( $options['system_prompt'] ) ? $options['system_prompt'] : 'You are a professional content writer specializing in SEO-optimized content.';
		$model        = isset( $options['model'] ) ? $options['model'] : $this->model;

		$body = [
			'model'       => $model,
			'max_tokens'  => $this->maxTokens,
			'temperature' => $this->temperature,
			'system'      => $systemPrompt,
			'messages'    => [
				[
					'role'    => 'user',
					'content' => $prompt,
				],
			],
		];

		$response = $this->makeRequest(
			$this->endpoint,
			$body,
			[
				'x-api-key'         => $this->apiKey,
				'anthropic-version' => '2023-06-01',
			]
		);

		if ( ! $response['success'] ) {
			return $response;
		}

		$content = $response['data']['content'][0]['text'] ?? '';

		return [
			'success' => true,
			'content' => $content,
			'usage'   => $response['data']['usage'] ?? [],
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
		$prompt .= "Provide a comprehensive SEO analysis including:\n";
		$prompt .= "1. Keyword usage and density analysis\n";
		$prompt .= "2. Content structure and heading hierarchy\n";
		$prompt .= "3. Readability score and suggestions\n";
		$prompt .= "4. Recommended meta description (150-160 characters)\n";
		$prompt .= "5. Optimized title tag (50-60 characters)\n";
		$prompt .= "6. Internal linking opportunities\n";
		$prompt .= "7. Overall SEO score (0-100) with justification\n";
		$prompt .= "8. Prioritized improvement recommendations\n";
		$prompt .= "9. Content gaps and missing elements\n\n";
		$prompt .= "Format your response as valid JSON with this structure:\n";
		$prompt .= '{"score": 85, "keyword_density": 2.5, "readability": "good", "meta_description": "...", "title": "...", "recommendations": ["..."], "content_gaps": ["..."]}';

		$options['system_prompt'] = 'You are an expert SEO analyst with deep knowledge of search engine algorithms, user intent, and content optimization. Provide detailed, actionable analysis based on current SEO best practices.';

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
		$prompt = "Conduct comprehensive keyword research for the topic: {$topic}\n\n";
		$prompt .= "Provide detailed analysis including:\n";
		$prompt .= "1. Primary keywords (high search volume, moderate to high competition)\n";
		$prompt .= "2. Long-tail keywords (specific queries, lower competition, higher intent)\n";
		$prompt .= "3. Question-based keywords (Who, What, Where, When, Why, How)\n";
		$prompt .= "4. Related topics and subtopics to cover\n";
		$prompt .= "5. LSI (Latent Semantic Indexing) keywords for context\n";
		$prompt .= "6. Search intent analysis (informational, navigational, transactional)\n";
		$prompt .= "7. Content structure recommendations\n";
		$prompt .= "8. Competitor content gaps to exploit\n\n";
		$prompt .= "Format as valid JSON:\n";
		$prompt .= '{"primary_keywords": [{"keyword": "...", "intent": "...", "difficulty": "medium"}], "long_tail": ["..."], "questions": ["..."], "related_topics": ["..."], "lsi_keywords": ["..."], "content_structure": ["..."]}';

		$options['system_prompt'] = 'You are an expert keyword researcher and SEO strategist with comprehensive knowledge of search trends, user behavior, and content marketing. Provide actionable keyword research.';

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
		$prompt = "Optimize the following content for Answer Engine Optimization (AEO) to maximize visibility in AI-powered search engines, voice assistants, and featured snippets:\n\n";
		$prompt .= "Original Content:\n{$content}\n\n";

		if ( ! empty( $questions ) ) {
			$prompt .= "Related Questions to Address:\n";
			foreach ( $questions as $question ) {
				$prompt .= "- {$question}\n";
			}
			$prompt .= "\n";
		}

		$prompt .= "AEO Optimization Guidelines:\n";
		$prompt .= "1. Create direct, concise answers to common questions (ideal for voice search)\n";
		$prompt .= "2. Structure content for featured snippets:\n";
		$prompt .= "   - Numbered lists for step-by-step processes\n";
		$prompt .= "   - Bullet points for key features or benefits\n";
		$prompt .= "   - Tables for comparisons\n";
		$prompt .= "   - Clear definitions in paragraph format\n";
		$prompt .= "3. Use natural language and conversational tone for voice search\n";
		$prompt .= "4. Include FAQ sections in Q&A format (ready for FAQ schema)\n";
		$prompt .= "5. Add 'People Also Ask' style content sections\n";
		$prompt .= "6. Structure headings as questions where appropriate\n";
		$prompt .= "7. Provide comprehensive but scannable content\n";
		$prompt .= "8. Include relevant statistics and data points\n";
		$prompt .= "9. Add summary boxes for key takeaways\n\n";
		$prompt .= "Return the fully optimized content with all AEO improvements applied. Maintain the original message but enhance for AI search visibility.";

		$options['system_prompt'] = 'You are an expert in Answer Engine Optimization (AEO), AI search algorithms, and content structuring for maximum visibility in AI-powered search results. Optimize content for platforms like ChatGPT, Perplexity, Google SGE, and voice assistants.';
		$options['max_tokens']    = 4000; // Longer output for optimized content

		return $this->generateContent( $prompt, $options );
	}
}
