<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OpenAI provider implementation.
 *
 * @since 4.9.0
 */
class OpenAi extends Provider {
	/**
	 * The provider name.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $name = 'OpenAI';

	/**
	 * The API endpoint.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.openai.com/v1/chat/completions';

	/**
	 * The model to use.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $model = 'gpt-4o';

	/**
	 * Generate content using OpenAI.
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
				'error'   => 'OpenAI API key is not configured.',
			];
		}

		$systemPrompt = isset( $options['system_prompt'] ) ? $options['system_prompt'] : 'You are a professional content writer specializing in SEO-optimized content.';
		$model        = isset( $options['model'] ) ? $options['model'] : $this->model;

		$body = [
			'model'       => $model,
			'messages'    => [
				[
					'role'    => 'system',
					'content' => $systemPrompt,
				],
				[
					'role'    => 'user',
					'content' => $prompt,
				],
			],
			'max_tokens'  => $this->maxTokens,
			'temperature' => $this->temperature,
		];

		$response = $this->makeRequest(
			$this->endpoint,
			$body,
			[
				'Authorization' => 'Bearer ' . $this->apiKey,
			]
		);

		if ( ! $response['success'] ) {
			return $response;
		}

		$content = $response['data']['choices'][0]['message']['content'] ?? '';

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
		$prompt .= "Provide a detailed SEO analysis including:\n";
		$prompt .= "1. Keyword usage and density\n";
		$prompt .= "2. Content structure and headings\n";
		$prompt .= "3. Readability score\n";
		$prompt .= "4. Meta description suggestion\n";
		$prompt .= "5. Title tag suggestion\n";
		$prompt .= "6. Internal linking opportunities\n";
		$prompt .= "7. Overall SEO score (0-100)\n";
		$prompt .= "8. Specific improvement recommendations\n\n";
		$prompt .= "Format your response as JSON with the following structure:\n";
		$prompt .= '{"score": 85, "keyword_density": 2.5, "readability": "good", "meta_description": "...", "title": "...", "recommendations": ["..."]}';

		$options['system_prompt'] = 'You are an expert SEO analyst. Provide detailed, actionable SEO analysis and recommendations.';

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
		$prompt = "Research keywords and topics for: {$topic}\n\n";
		$prompt .= "Provide:\n";
		$prompt .= "1. Primary keywords (high volume, competitive)\n";
		$prompt .= "2. Long-tail keywords (specific, less competitive)\n";
		$prompt .= "3. Related topics and questions people ask\n";
		$prompt .= "4. Content structure suggestions\n";
		$prompt .= "5. Competitor analysis insights\n";
		$prompt .= "6. Semantic keywords (LSI keywords)\n\n";
		$prompt .= "Format as JSON:\n";
		$prompt .= '{"primary_keywords": ["..."], "long_tail": ["..."], "questions": ["..."], "related_topics": ["..."], "lsi_keywords": ["..."]}';

		$options['system_prompt'] = 'You are an expert keyword researcher and SEO strategist. Provide comprehensive keyword research based on current search trends and user intent.';

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
		$prompt = "Optimize the following content for Answer Engine Optimization (AEO) to rank in AI search results, featured snippets, and voice search:\n\n";
		$prompt .= "Content:\n{$content}\n\n";

		if ( ! empty( $questions ) ) {
			$prompt .= "Related Questions to Address:\n";
			foreach ( $questions as $question ) {
				$prompt .= "- {$question}\n";
			}
			$prompt .= "\n";
		}

		$prompt .= "Optimization Requirements:\n";
		$prompt .= "1. Add clear, concise answers to common questions\n";
		$prompt .= "2. Structure content for featured snippets (lists, tables, definitions)\n";
		$prompt .= "3. Optimize for voice search (natural language, question-answer format)\n";
		$prompt .= "4. Add FAQ schema-ready Q&A sections\n";
		$prompt .= "5. Use semantic HTML structure (headings, lists, emphasis)\n";
		$prompt .= "6. Include 'People Also Ask' style content\n";
		$prompt .= "7. Ensure content is scannable and easy to understand\n\n";
		$prompt .= "Return the optimized content with AEO improvements applied.";

		$options['system_prompt'] = 'You are an expert in Answer Engine Optimization (AEO) and AI search. Optimize content to rank in AI-powered search engines, voice assistants, and featured snippets.';
		$options['max_tokens']    = 4000; // Longer output for optimized content

		return $this->generateContent( $prompt, $options );
	}
}
