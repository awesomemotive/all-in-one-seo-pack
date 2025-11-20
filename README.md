# All in One SEO - AI Powered Edition

🤖 The world's most popular WordPress SEO plugin - now supercharged with AI!

## ✨ New AI Features

- **Content Generation** - Write blog posts and pages with OpenAI, Claude, or Gemini
- **SEO Analysis** - Get AI-powered recommendations to improve your content
- **Keyword Research** - Find the best keywords for your content
- **AEO Optimization** - Optimize for voice search and AI assistants (Alexa, Siri, etc.)
- **Elementor Integration** - AI widgets that work directly in Elementor page builder

## 📥 How to Download and Install

### Method 1: Download ZIP (Easiest!)

1. **Click the green "Code" button** at the top of this page
2. **Click "Download ZIP"**
3. **Unzip the file** on your computer
4. **Rename the folder** to `all-in-one-seo-pack` (remove any -main or -branch suffix)
5. **Upload to WordPress**:
   - Go to your WordPress admin
   - Navigate to **Plugins → Add New → Upload Plugin**
   - Click **Choose File** and select the ZIP
   - Click **Install Now**
   - Click **Activate**

### Method 2: Direct Upload via FTP

1. **Download the ZIP** (green Code button → Download ZIP)
2. **Unzip** the file on your computer
3. **Rename folder** to `all-in-one-seo-pack`
4. **Upload via FTP**:
   - Connect to your website with FTP (FileZilla, etc.)
   - Go to: `/wp-content/plugins/`
   - Upload the `all-in-one-seo-pack` folder
5. **Activate in WordPress**:
   - Go to **Plugins**
   - Find "All in One SEO - AI Powered"
   - Click **Activate**

### Method 3: Git Clone (For Developers)

```bash
# Go to your plugins folder
cd /path/to/wordpress/wp-content/plugins/

# Clone this repository
git clone [YOUR-REPO-URL] all-in-one-seo-pack

# Switch to the AI features branch
cd all-in-one-seo-pack
git checkout claude/ai-wordpress-plugin-01QHezJTXQygFYAmKMUKd97C
```

Then activate in WordPress admin.

## 🔑 Getting Your AI Keys (Required)

You need at least ONE API key from these AI providers:

### OpenAI (Recommended to Start)
1. Go to: https://platform.openai.com/signup
2. Sign up and add payment method
3. Create API key: https://platform.openai.com/api-keys
4. Copy your key (starts with `sk-proj-...`)
5. **Cost**: ~$0.002 per page ($5-20/month typical)

### Claude (Anthropic)
1. Go to: https://console.anthropic.com/
2. Sign up and add payment
3. Create API key
4. Copy your key (starts with `sk-ant-...`)
5. **Cost**: Similar to OpenAI

### Google Gemini (Has Free Tier!)
1. Go to: https://makersuite.google.com/app/apikey
2. Sign in with Google account
3. Create API key
4. Copy your key
5. **Cost**: FREE tier available!

## ⚙️ Quick Setup (5 Minutes)

1. **Install and Activate** the plugin
2. **Go to**: AIOSEO → General Settings
3. **Look for "AI" tab** (might need to scroll down)
4. **Turn ON** "Enable AI Features"
5. **Paste your API key(s)** in the appropriate field
6. **Click "Test Key"** to make sure it works (you'll see a green ✓)
7. **Choose features** you want to use:
   - ✅ Content Generation
   - ✅ SEO Analysis
   - ✅ Keyword Research
   - ✅ AEO Optimization
   - ✅ Elementor Integration (if you use Elementor)
8. **Click "Save Changes"**

## 🚀 How to Use

### Write Content with AI

1. Create a new post or page
2. Look for **"AI Assistant"** panel on the right side
3. Type what you want: "Write about healthy eating"
4. Click **"Generate"**
5. Click **"Insert into Editor"**
6. Done! ✨

### Check Your SEO

1. Write your content
2. Click **"SEO Analysis"** tab in AI Assistant
3. Enter your target keyword
4. Click **"Analyze"**
5. See your score and get tips to improve!

### Research Keywords

1. Click **"Keywords"** tab
2. Enter your topic: "fitness tips"
3. Click **"Research"**
4. Get tons of keyword ideas!
5. Click any keyword tag to add it to your post

### Use in Elementor

1. Edit a page with Elementor
2. Search for "AI Content" widget
3. Drag it onto your page
4. Enter your prompt
5. Click "Generate Content"
6. Watch it write on your page!

## 📚 Full Documentation

- **User Guide**: See `AI_FEATURES_README.md` in this folder
- **Developer Guide**: See `FRONTEND_IMPLEMENTATION.md`
- **Dev Setup**: See `README-DEV.md`

## 🆘 Troubleshooting

### Plugin Not Showing Up?

**Problem**: After uploading, you don't see it in Plugins list

**Fix**:
- Make sure the folder is named exactly `all-in-one-seo-pack`
- Make sure it's in `/wp-content/plugins/all-in-one-seo-pack/`
- The main file should be at: `/wp-content/plugins/all-in-one-seo-pack/all_in_one_seo_pack.php`

### "Test Key Failed"?

**Problem**: When you test your API key, it shows a red X

**Fix**:
- Check you copied the ENTIRE key (no spaces at start/end)
- Make sure you added payment method to the AI provider
- Wait 5 minutes after creating the key, then try again
- Check you have credit/money with the AI provider

### AI Features Not Showing?

**Problem**: Don't see AI Assistant or AI settings

**Fix**:
- Make sure you turned ON "Enable AI Features"
- Click Save after turning it on
- Refresh your page
- Check you're on version 4.9.0 (Plugins page shows version)

### Elementor Widgets Not Showing?

**Problem**: Can't find AI widgets in Elementor

**Fix**:
- Go to AIOSEO → General Settings → AI tab
- Turn ON "Elementor Integration"
- Turn ON "Enable AI Widgets"
- Click Save
- Refresh Elementor editor page

## 💰 Costs

### Expected Monthly Costs

- **Light Use** (5-10 AI generations/day): $5-10/month
- **Medium Use** (20-30 generations/day): $15-25/month
- **Heavy Use** (50+ generations/day): $30-50/month

### Free Option

Use **Google Gemini** - it has a free tier!

### Saving Money Tips

1. Start with shorter prompts
2. Use Gemini for basic stuff (it's free!)
3. Use OpenAI/Claude for important content
4. Set a budget limit in your AI provider account
5. Monitor usage in AIOSEO → General Settings → AI → Usage Stats

## 🎯 System Requirements

- **WordPress**: 5.3 or higher
- **PHP**: 7.0 or higher (7.4+ recommended)
- **Memory**: 128MB minimum (256MB recommended)
- **Internet**: Active connection (to talk to AI providers)
- **HTTPS**: Recommended for security

## 🔒 Security & Privacy

- ✅ Your API keys are stored securely in your WordPress database
- ✅ All API calls use HTTPS encryption
- ✅ No AI content is stored by the AI providers (per their policies)
- ✅ You control what data is sent to AI providers
- ✅ GDPR compliant
- ✅ No personal user data sent to AI providers

## 📊 What's Included

### AI Providers Supported
- OpenAI (GPT-4o, GPT-4o Mini, GPT-4 Turbo)
- Claude (3.5 Sonnet, 3 Opus, 3 Sonnet, 3 Haiku)
- Google Gemini (1.5 Pro, 1.5 Flash, 1.0 Pro)

### AI Features
- Content Generation (blog posts, pages, descriptions)
- SEO Analysis (get scored 0-100 with tips)
- Keyword Research (find primary, long-tail, question keywords)
- AEO Optimization (optimize for voice search & AI assistants)
- Meta Description Generator
- Title Tag Generator
- FAQ Generator
- Bulk Content Ideas

### Elementor Widgets
- AI Content Generator
- AI FAQ Generator (with schema markup)
- AI Keyword Research

### Admin Features
- Settings page with API key testing
- Usage statistics dashboard
- Quick test tool
- Post editor AI assistant (4 tabs)
- History of all AI interactions

## 📖 Quick Reference

### Where to Find Things

- **AI Settings**: AIOSEO → General Settings → AI tab
- **Post AI Assistant**: Edit any post → Right sidebar → "AI Assistant" panel
- **Elementor Widgets**: Elementor Editor → Search "AI"
- **Usage Stats**: AIOSEO → General Settings → AI → Scroll to "Usage Statistics"
- **Test Tool**: AIOSEO → General Settings → AI → Scroll to "Quick Test"

## 📝 Version History

### Version 4.9.0 (Current)
- ✨ Added AI-powered content generation
- ✨ Added SEO analysis with scoring
- ✨ Added keyword research
- ✨ Added AEO optimization
- ✨ Added Elementor integration with 3 widgets
- ✨ Multi-provider support (OpenAI, Claude, Gemini)
- ✨ Usage tracking and statistics
- ✨ Post editor AI assistant
- ✨ API key validation and testing

## 📄 License

GPL-3.0+ - Same as WordPress and original AIOSEO plugin

Free to use, modify, and distribute!

## ⚠️ Important Notes

1. **This requires API keys** - The AI features won't work without at least one API key
2. **Costs money** - AI providers charge per use (though Gemini has free tier)
3. **Internet required** - AI features need internet to work
4. **Review AI content** - Always review and edit AI-generated content before publishing
5. **Set budgets** - Set spending limits in your AI provider accounts

## 🎉 You're Ready!

1. ✅ Download the plugin
2. ✅ Upload to WordPress
3. ✅ Activate it
4. ✅ Get an API key
5. ✅ Add it to settings
6. ✅ Start creating amazing content with AI!

---

**Questions?** Check `AI_FEATURES_README.md` for detailed documentation!

**Need help?** Read the troubleshooting section above!

**Ready to start?** Follow the "Quick Setup" section!

Enjoy your AI-powered WordPress! 🚀✨
