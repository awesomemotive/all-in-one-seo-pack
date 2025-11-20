/**
 * AIOSEO Elementor AI Integration
 * Main JavaScript for AI-powered Elementor widgets
 *
 * @since 4.9.0
 */

(function($) {
	'use strict';

	/**
	 * AIOSEO Elementor AI Handler
	 */
	window.AIOSEOElementorAI = {
		/**
		 * API base URL
		 */
		apiUrl: aioseoElementorAi.restUrl,

		/**
		 * Nonce for API requests
		 */
		nonce: aioseoElementorAi.nonce,

		/**
		 * Currently active requests
		 */
		activeRequests: {},

		/**
		 * Initialize
		 */
		init() {
			if (typeof elementor === 'undefined') {
				console.warn('AIOSEO AI: Elementor not loaded');
				return;
			}

			this.bindEvents();
			this.initWidgets();
			console.log('AIOSEO Elementor AI initialized');
		},

		/**
		 * Bind events
		 */
		bindEvents() {
			// Listen for Elementor editor load
			$(window).on('elementor:init', () => {
				this.onElementorReady();
			});

			// Listen for widget renders
			elementor.hooks.addAction('panel/open_editor/widget', (panel, model, view) => {
				this.onWidgetEdit(panel, model, view);
			});
		},

		/**
		 * When Elementor is ready
		 */
		onElementorReady() {
			console.log('AIOSEO AI: Elementor ready');

			// Add custom events
			elementor.channels.editor.on('aioseo:generate:content', (data) => {
				this.generateContent(data);
			});

			elementor.channels.editor.on('aioseo:generate:faq', (data) => {
				this.generateFAQ(data);
			});

			elementor.channels.editor.on('aioseo:research:keywords', (data) => {
				this.researchKeywords(data);
			});
		},

		/**
		 * Initialize widgets on frontend
		 */
		initWidgets() {
			// Content Generator Widget
			$(document).on('click', '.aioseo-ai-content-widget[data-prompt]', function() {
				const $widget = $(this);
				if ($widget.data('auto-generate') === 'yes' && !$widget.data('generated')) {
					AIOSEOElementorAI.triggerContentGeneration($widget);
				}
			});

			// Auto-generate on load for widgets
			$('.aioseo-ai-content-widget[data-prompt]').each(function() {
				const $widget = $(this);
				if ($widget.closest('.elementor-element-edit-mode').length === 0) {
					// Only on frontend, not in editor
					if ($widget.data('auto-generate') === 'yes' && !$widget.data('generated')) {
						AIOSEOElementorAI.triggerContentGeneration($widget);
					}
				}
			});
		},

		/**
		 * When a widget is opened for editing
		 */
		onWidgetEdit(panel, model, view) {
			const widgetType = model.get('widgetType');

			if (widgetType === 'aioseo_ai_content') {
				this.setupContentWidget(panel, model, view);
			} else if (widgetType === 'aioseo_ai_faq') {
				this.setupFaqWidget(panel, model, view);
			} else if (widgetType === 'aioseo_ai_keywords') {
				this.setupKeywordsWidget(panel, model, view);
			}
		},

		/**
		 * Setup Content Generator Widget
		 */
		setupContentWidget(panel, model, view) {
			const $panel = panel.$el;

			// Find generate button
			const $generateBtn = $panel.find('[data-event="aioseo:generate:content"]');

			$generateBtn.off('click').on('click', (e) => {
				e.preventDefault();

				const prompt = model.getSetting('prompt');
				const provider = model.getSetting('provider') || 'openai';

				if (!prompt) {
					this.showNotice('error', aioseoElementorAi.i18n.error, 'Please enter a prompt');
					return;
				}

				this.generateContent({
					prompt: prompt,
					provider: provider,
					model: model,
					view: view
				});
			});
		},

		/**
		 * Setup FAQ Widget
		 */
		setupFaqWidget(panel, model, view) {
			const $panel = panel.$el;

			const $generateBtn = $panel.find('[data-event="aioseo:generate:faq"]');

			$generateBtn.off('click').on('click', (e) => {
				e.preventDefault();

				const topic = model.getSetting('topic');
				const provider = model.getSetting('provider') || 'claude';
				const numQuestions = model.getSetting('num_questions') || 5;

				if (!topic) {
					this.showNotice('error', aioseoElementorAi.i18n.error, 'Please enter a topic');
					return;
				}

				this.generateFAQ({
					topic: topic,
					provider: provider,
					numQuestions: numQuestions,
					model: model,
					view: view
				});
			});
		},

		/**
		 * Setup Keywords Widget
		 */
		setupKeywordsWidget(panel, model, view) {
			const $panel = panel.$el;

			const $researchBtn = $panel.find('[data-event="aioseo:research:keywords"]');

			$researchBtn.off('click').on('click', (e) => {
				e.preventDefault();

				const topic = model.getSetting('topic');
				const provider = model.getSetting('provider') || 'gemini';

				if (!topic) {
					this.showNotice('error', aioseoElementorAi.i18n.error, 'Please enter a topic');
					return;
				}

				this.researchKeywords({
					topic: topic,
					provider: provider,
					model: model,
					view: view
				});
			});
		},

		/**
		 * Generate content
		 */
		async generateContent(data) {
			const { prompt, provider, model, view } = data;
			const requestId = 'content_' + Date.now();

			// Show loading
			this.showLoading(view, aioseoElementorAi.i18n.generating);

			try {
				const response = await this.apiRequest('ai/generate-content', {
					prompt: prompt,
					provider: provider,
					postId: elementor.config.document.id
				}, requestId);

				if (response.success) {
					this.updateWidgetContent(view, response.content);
					this.showNotice('success', aioseoElementorAi.i18n.success, 'Content generated successfully!');
				} else {
					throw new Error(response.error || 'Generation failed');
				}
			} catch (error) {
				console.error('AIOSEO AI Error:', error);
				this.showNotice('error', aioseoElementorAi.i18n.error, error.message);
				this.hideLoading(view);
			}
		},

		/**
		 * Generate FAQ
		 */
		async generateFAQ(data) {
			const { topic, provider, numQuestions, model, view } = data;
			const requestId = 'faq_' + Date.now();

			this.showLoading(view, 'Generating FAQ...');

			try {
				// Create prompt for FAQ generation
				const prompt = `Generate ${numQuestions} frequently asked questions and detailed answers about: ${topic}\n\n` +
					`Format as:\nQ: [Question]\nA: [Detailed answer]\n\nRepeat for all questions.`;

				const response = await this.apiRequest('ai/generate-content', {
					prompt: prompt,
					provider: provider,
					postId: elementor.config.document.id,
					options: {
						system_prompt: 'You are an expert FAQ writer. Create comprehensive, clear questions and answers optimized for SEO and featured snippets.'
					}
				}, requestId);

				if (response.success) {
					const faqHtml = this.formatFAQ(response.content);
					this.updateWidgetContent(view, faqHtml);
					this.showNotice('success', aioseoElementorAi.i18n.success, 'FAQ generated successfully!');
				} else {
					throw new Error(response.error || 'FAQ generation failed');
				}
			} catch (error) {
				console.error('AIOSEO AI Error:', error);
				this.showNotice('error', aioseoElementorAi.i18n.error, error.message);
				this.hideLoading(view);
			}
		},

		/**
		 * Research keywords
		 */
		async researchKeywords(data) {
			const { topic, provider, model, view } = data;
			const requestId = 'keywords_' + Date.now();

			this.showLoading(view, 'Researching keywords...');

			try {
				const response = await this.apiRequest('ai/research-keywords', {
					topic: topic,
					provider: provider,
					postId: elementor.config.document.id
				}, requestId);

				if (response.success) {
					const keywordsHtml = this.formatKeywords(response.content);
					this.updateWidgetContent(view, keywordsHtml);
					this.showNotice('success', aioseoElementorAi.i18n.success, 'Keywords researched successfully!');
				} else {
					throw new Error(response.error || 'Keyword research failed');
				}
			} catch (error) {
				console.error('AIOSEO AI Error:', error);
				this.showNotice('error', aioseoElementorAi.i18n.error, error.message);
				this.hideLoading(view);
			}
		},

		/**
		 * Trigger content generation from widget element
		 */
		triggerContentGeneration($widget) {
			const prompt = $widget.data('prompt');
			const provider = $widget.data('provider') || 'openai';
			const $content = $widget.find('.aioseo-ai-content');

			if (!prompt) return;

			$content.html('<div class="aioseo-ai-loading"><span class="spinner"></span> Generating content...</div>');
			$widget.data('generated', true);

			this.apiRequest('ai/generate-content', {
				prompt: prompt,
				provider: provider
			}).then(response => {
				if (response.success) {
					$content.html(response.content);
				} else {
					$content.html('<div class="aioseo-ai-error">Error: ' + (response.error || 'Generation failed') + '</div>');
				}
			}).catch(error => {
				$content.html('<div class="aioseo-ai-error">Error: ' + error.message + '</div>');
			});
		},

		/**
		 * Format FAQ content to HTML
		 */
		formatFAQ(content) {
			const lines = content.split('\n');
			let html = '<div class="aioseo-faq-container">';
			let currentQ = '';
			let currentA = '';

			lines.forEach(line => {
				line = line.trim();
				if (line.startsWith('Q:') || line.startsWith('Question:')) {
					if (currentQ && currentA) {
						html += `<div class="aioseo-faq-item">
							<h3 class="aioseo-faq-question">${currentQ}</h3>
							<div class="aioseo-faq-answer">${currentA}</div>
						</div>`;
					}
					currentQ = line.replace(/^(Q:|Question:)\s*/i, '');
					currentA = '';
				} else if (line.startsWith('A:') || line.startsWith('Answer:')) {
					currentA = line.replace(/^(A:|Answer:)\s*/i, '');
				} else if (line && currentQ && !currentA.includes(line)) {
					currentA += ' ' + line;
				}
			});

			// Add last item
			if (currentQ && currentA) {
				html += `<div class="aioseo-faq-item">
					<h3 class="aioseo-faq-question">${currentQ}</h3>
					<div class="aioseo-faq-answer">${currentA}</div>
				</div>`;
			}

			html += '</div>';
			return html;
		},

		/**
		 * Format keywords to HTML
		 */
		formatKeywords(content) {
			try {
				// Try to parse as JSON first
				const data = typeof content === 'string' ? JSON.parse(content) : content;
				let html = '<div class="aioseo-keywords-container">';

				if (data.primary_keywords && data.primary_keywords.length) {
					html += '<div class="aioseo-keyword-section">';
					html += '<h3 class="aioseo-keywords-heading">Primary Keywords</h3>';
					html += '<ul class="aioseo-keyword-list">';
					data.primary_keywords.forEach(kw => {
						const keyword = typeof kw === 'object' ? kw.keyword : kw;
						html += `<li class="aioseo-keyword-item">${keyword}</li>`;
					});
					html += '</ul></div>';
				}

				if (data.long_tail && data.long_tail.length) {
					html += '<div class="aioseo-keyword-section">';
					html += '<h3 class="aioseo-keywords-heading">Long-tail Keywords</h3>';
					html += '<ul class="aioseo-keyword-list">';
					data.long_tail.forEach(kw => {
						html += `<li class="aioseo-keyword-item">${kw}</li>`;
					});
					html += '</ul></div>';
				}

				if (data.questions && data.questions.length) {
					html += '<div class="aioseo-keyword-section">';
					html += '<h3 class="aioseo-keywords-heading">Question Keywords</h3>';
					html += '<ul class="aioseo-keyword-list">';
					data.questions.forEach(q => {
						html += `<li class="aioseo-keyword-item">${q}</li>`;
					});
					html += '</ul></div>';
				}

				if (data.lsi_keywords && data.lsi_keywords.length) {
					html += '<div class="aioseo-keyword-section">';
					html += '<h3 class="aioseo-keywords-heading">LSI Keywords</h3>';
					html += '<ul class="aioseo-keyword-list">';
					data.lsi_keywords.forEach(kw => {
						html += `<li class="aioseo-keyword-item">${kw}</li>`;
					});
					html += '</ul></div>';
				}

				html += '</div>';
				return html;
			} catch (e) {
				// If not JSON, just display as formatted text
				return `<div class="aioseo-keywords-container"><pre>${content}</pre></div>`;
			}
		},

		/**
		 * Update widget content
		 */
		updateWidgetContent(view, content) {
			if (!view) return;

			const $widget = view.$el;
			const $contentArea = $widget.find('.aioseo-ai-content, .aioseo-faq-container, .aioseo-keywords-container');

			if ($contentArea.length) {
				$contentArea.html(content);
			}

			this.hideLoading(view);
		},

		/**
		 * Show loading state
		 */
		showLoading(view, message) {
			if (!view) return;

			const $widget = view.$el;
			const $loadingArea = $widget.find('.aioseo-ai-content, .aioseo-faq-container, .aioseo-keywords-container');

			if ($loadingArea.length) {
				$loadingArea.html(`
					<div class="aioseo-ai-loading">
						<span class="aioseo-ai-spinner"></span>
						<span class="aioseo-ai-loading-text">${message}</span>
					</div>
				`);
			}
		},

		/**
		 * Hide loading state
		 */
		hideLoading(view) {
			if (!view) return;

			const $widget = view.$el;
			$widget.find('.aioseo-ai-loading').remove();
		},

		/**
		 * Show notice
		 */
		showNotice(type, title, message) {
			if (typeof elementor !== 'undefined' && elementor.notifications) {
				elementor.notifications.showToast({
					message: `<strong>${title}</strong><br>${message}`,
					buttons: []
				});
			} else {
				alert(`${title}: ${message}`);
			}
		},

		/**
		 * Make API request
		 */
		async apiRequest(endpoint, data, requestId) {
			const url = this.apiUrl + endpoint;

			// Cancel any existing request with same ID
			if (requestId && this.activeRequests[requestId]) {
				this.activeRequests[requestId].abort();
			}

			const controller = new AbortController();
			if (requestId) {
				this.activeRequests[requestId] = controller;
			}

			try {
				const response = await fetch(url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': this.nonce
					},
					body: JSON.stringify(data),
					signal: controller.signal
				});

				const result = await response.json();

				if (requestId) {
					delete this.activeRequests[requestId];
				}

				return result;
			} catch (error) {
				if (requestId) {
					delete this.activeRequests[requestId];
				}
				throw error;
			}
		}
	};

	// Initialize when document is ready
	$(document).ready(() => {
		if (typeof aioseoElementorAi !== 'undefined') {
			AIOSEOElementorAI.init();
		}
	});

	// Also initialize on Elementor frontend init
	$(window).on('elementor/frontend/init', () => {
		if (typeof aioseoElementorAi !== 'undefined') {
			AIOSEOElementorAI.initWidgets();
		}
	});

})(jQuery);
