# AIOSEO AI-Powered Features

## Overview

This document describes the new AI-powered features added to All in One SEO Pack (AIOSEO). These features integrate multiple AI providers (OpenAI, Claude, and Google Gemini) to help with content creation, SEO research, and Answer Engine Optimization (AEO).

## Features

### 1. Multi-Provider AI Integration
- **OpenAI (GPT-4o)**: Advanced content generation and analysis
- **Claude (Anthropic)**: Detailed SEO analysis and content optimization
- **Google Gemini**: Comprehensive keyword research and AEO optimization

### 2. AI-Powered Content Generation
Generate SEO-optimized content using natural language prompts across all supported AI providers.

**API Endpoint**: `POST /wp-json/aioseo/v1/ai/generate-content`

**Request Body**:
```json
{
  "prompt": "Write a blog post about sustainable gardening",
  "provider": "openai",
  "postId": 123,
  "options": {
    "system_prompt": "You are an expert content writer...",
    "model": "gpt-4o"
  }
}
```

### 3. SEO Analysis
AI-powered SEO analysis that provides:
- Keyword density analysis
- Content structure evaluation
- Readability scoring
- Meta description suggestions
- Title tag recommendations
- Internal linking opportunities
- Overall SEO score (0-100)
- Actionable improvement recommendations

**API Endpoint**: `POST /wp-json/aioseo/v1/ai/analyze-seo`

**Request Body**:
```json
{
  "content": "Your content here...",
  "keyword": "target keyword",
  "provider": "claude",
  "postId": 123
}
```

### 4. Keyword Research
Comprehensive AI-driven keyword research featuring:
- Primary keywords (high volume)
- Long-tail keywords (specific, lower competition)
- Question-based keywords
- LSI (Latent Semantic Indexing) keywords
- Search intent analysis
- Content structure recommendations

**API Endpoint**: `POST /wp-json/aioseo/v1/ai/research-keywords`

**Request Body**:
```json
{
  "topic": "sustainable gardening",
  "provider": "gemini",
  "postId": 123
}
```

### 5. Answer Engine Optimization (AEO)
Optimize content for AI-powered search engines, voice assistants, and featured snippets:
- Featured snippet optimization (paragraphs, lists, tables)
- Voice search optimization
- FAQ schema-ready content
- "People Also Ask" style content
- Natural language processing

**API Endpoint**: `POST /wp-json/aioseo/v1/ai/optimize-aeo`

**Request Body**:
```json
{
  "content": "Your content...",
  "questions": [
    "What is sustainable gardening?",
    "How to start a sustainable garden?"
  ],
  "provider": "claude"
}
```

### 6. Elementor Integration

#### AI Widgets
Three custom Elementor widgets for AI-powered content:

##### a) AI Content Generator Widget
- Generate content directly in Elementor
- Choose AI provider
- Auto-generate on page load (optional)
- Customizable styling

##### b) AI FAQ Generator Widget (AEO Optimized)
- Generate FAQ sections automatically
- Specify number of questions (3-15)
- FAQ schema support
- Optimized for featured snippets

##### c) AI Keyword Research Widget
- Display keyword research results
- Toggle different keyword types
- Customizable display options
- Real-time keyword generation

#### AI Prompt Control
Custom Elementor control for AI-powered content generation in any widget.

#### Features
- Seamless integration with Elementor editor
- Live preview of AI-generated content
- Provider selection per widget
- Auto-suggestions when enabled

## Configuration

### Plugin Settings

Add your API keys in **AIOSEO Settings > AI**:

```php
// AI Settings Structure
[
  'enabled' => true,
  'defaultProvider' => 'openai', // 'openai', 'claude', or 'gemini'

  // OpenAI Configuration
  'openaiApiKey' => 'sk-...',
  'openaiModel' => 'gpt-4o',

  // Claude Configuration
  'claudeApiKey' => 'sk-ant-...',
  'claudeModel' => 'claude-3-5-sonnet-20241022',

  // Gemini Configuration
  'geminiApiKey' => 'AIza...',
  'geminiModel' => 'gemini-1.5-pro',

  // General AI Settings
  'temperature' => 0.7, // 0-1
  'maxTokens' => 2000,

  // Feature Toggles
  'features' => [
    'contentGeneration' => true,
    'seoAnalysis' => true,
    'keywordResearch' => true,
    'aeoOptimization' => true,
    'elementorIntegration' => true,
  ],

  // Elementor Settings
  'elementor' => [
    'enableAiWidgets' => true,
    'autoSuggestions' => true,
  ],
]
```

### Getting API Keys

1. **OpenAI**: https://platform.openai.com/api-keys
2. **Claude (Anthropic)**: https://console.anthropic.com/
3. **Google Gemini**: https://makersuite.google.com/app/apikey

## Database Schema

### AI Interactions Table

The plugin creates a new table `wp_aioseo_ai_interactions` to track all AI interactions:

```sql
CREATE TABLE wp_aioseo_ai_interactions (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  action varchar(100) NOT NULL,
  provider varchar(50) NOT NULL,
  user_id bigint(20) unsigned NOT NULL,
  post_id bigint(20) unsigned DEFAULT NULL,
  prompt text,
  data longtext,
  response longtext,
  success tinyint(1) DEFAULT 1,
  tokens_used int DEFAULT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id),
  KEY action (action),
  KEY provider (provider),
  KEY user_id (user_id),
  KEY post_id (post_id),
  KEY created_at (created_at)
);
```

## Architecture

### File Structure

```
app/Common/
├── Ai/
│   ├── Provider.php           # Abstract AI provider base class
│   ├── OpenAi.php            # OpenAI implementation
│   ├── Claude.php            # Claude implementation
│   ├── Gemini.php            # Gemini implementation
│   └── AiManager.php         # Provider management & routing
├── Api/
│   └── Ai.php                # REST API endpoints
├── Models/
│   └── AiInteraction.php     # Database model
└── Integrations/
    └── Elementor/
        ├── Elementor.php     # Main integration class
        ├── Widgets/
        │   ├── AiContent.php
        │   ├── AiFaq.php
        │   └── AiKeywords.php
        └── Controls/
            └── AiPrompt.php
```

### Provider Architecture

All AI providers extend the abstract `Provider` class and implement:

```php
abstract public function generateContent( $prompt, $options = [] );
abstract public function analyzeSeo( $content, $keyword = '', $options = [] );
abstract public function researchKeywords( $topic, $options = [] );
abstract public function optimizeForAeo( $content, $questions = [], $options = [] );
```

## Usage Examples

### PHP Usage

```php
// Initialize AI Manager
$aiManager = aioseo()->aiManager;

// Generate content
$result = $aiManager->generateContent(
    'Write a blog post about sustainable gardening',
    [
        'system_prompt' => 'You are an expert gardening writer.',
    ]
);

// Analyze SEO
$analysis = $aiManager->analyzeSeo(
    $content,
    'sustainable gardening',
    []
);

// Research keywords
$keywords = $aiManager->researchKeywords('sustainable gardening');

// Optimize for AEO
$optimized = $aiManager->optimizeForAeo(
    $content,
    [
        'What is sustainable gardening?',
        'How to start a sustainable garden?',
    ]
);

// Change provider
$aiManager->setCurrentProvider('claude');
```

### JavaScript Usage (REST API)

```javascript
// Generate content
const response = await fetch('/wp-json/aioseo/v1/ai/generate-content', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpApiSettings.nonce,
  },
  body: JSON.stringify({
    prompt: 'Write about sustainable gardening',
    provider: 'openai',
    postId: 123,
  }),
});

const data = await response.json();
if (data.success) {
  console.log('Generated content:', data.content);
}
```

### Elementor Usage

1. **In Elementor Editor**:
   - Drag the "AI Content Generator" widget onto your page
   - Enter your content prompt
   - Select AI provider
   - Click "Generate Content"

2. **AI FAQ Widget**:
   - Drag the "AI FAQ Generator" widget
   - Enter topic
   - Set number of questions
   - Enable FAQ schema
   - Click "Generate FAQs"

3. **Keyword Research Widget**:
   - Drag the "AI Keyword Research" widget
   - Enter topic
   - Toggle keyword types
   - Click "Research Keywords"

## Best Practices

### 1. Content Generation
- Be specific in your prompts
- Include target keywords naturally
- Set appropriate temperature (0.7 for balanced, lower for consistent)
- Review and edit AI-generated content

### 2. SEO Analysis
- Run analysis after major content changes
- Focus on top recommendations first
- Use keyword density as a guide, not a rule
- Optimize meta descriptions for CTR

### 3. Keyword Research
- Start broad, then narrow down
- Focus on search intent
- Use long-tail keywords for easier ranking
- Target question keywords for featured snippets

### 4. AEO Optimization
- Structure content for voice search
- Create FAQ sections
- Use clear headings as questions
- Provide direct, concise answers
- Include tables and lists for featured snippets

### 5. Elementor Integration
- Use AI widgets for initial content drafts
- Combine multiple widgets for comprehensive pages
- Enable auto-suggestions for faster workflows
- Customize widget styling to match your theme

## Performance Considerations

- **Rate Limits**: Respect API provider rate limits
- **Token Usage**: Monitor token consumption to manage costs
- **Caching**: Results are logged for reference
- **Async Processing**: Consider background processing for long operations

## Security

- API keys are stored securely in WordPress options
- All requests require proper WordPress authentication
- User capabilities are checked before API access
- Input sanitization on all user-provided data
- Output escaping when rendering content

## Troubleshooting

### Common Issues

1. **"No AI provider is configured"**
   - Add API keys in AIOSEO Settings > AI
   - Enable the AI features

2. **"API request failed"**
   - Check API key validity
   - Verify internet connectivity
   - Check provider status page
   - Review API rate limits

3. **Elementor widgets not appearing**
   - Enable AI widgets in settings
   - Clear Elementor cache
   - Regenerate CSS

4. **Poor content quality**
   - Refine your prompts
   - Adjust temperature settings
   - Try different providers
   - Add more context in system prompts

## API Rate Limits

- **OpenAI**: Varies by plan (check console)
- **Claude**: 50 requests/minute (Tier 1)
- **Gemini**: 60 requests/minute (free tier)

Monitor your usage in the AI interactions history.

## Cost Estimates

### OpenAI (GPT-4o)
- Input: $2.50 per 1M tokens
- Output: $10.00 per 1M tokens

### Claude (Claude 3.5 Sonnet)
- Input: $3.00 per 1M tokens
- Output: $15.00 per 1M tokens

### Google Gemini (Gemini 1.5 Pro)
- Free tier: 2 RPM
- Paid: $0.50-$7.00 per 1M tokens (depending on context)

## Future Enhancements

Potential future features:
- [ ] Gutenberg block integration
- [ ] Bulk content generation
- [ ] Image generation with DALL-E/Midjourney
- [ ] Multi-language support
- [ ] Custom AI model training
- [ ] Scheduled content generation
- [ ] A/B testing for AI-generated content
- [ ] Integration with more page builders
- [ ] Voice input for prompts
- [ ] AI-powered image alt text generation

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review API provider documentation
3. Check AIOSEO support forums
4. Contact AIOSEO support with AI interaction logs

## Version History

### 4.9.0 (Current)
- Initial AI features release
- Multi-provider support (OpenAI, Claude, Gemini)
- Content generation
- SEO analysis
- Keyword research
- AEO optimization
- Elementor integration with 3 custom widgets
- AI interactions tracking

## License

This feature is part of All in One SEO Pack and follows the same license terms.

---

**Note**: AI-generated content should always be reviewed and edited by humans. AI is a tool to assist, not replace, human creativity and expertise.
