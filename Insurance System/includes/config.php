<?php
/**
 * AI API configuration.
 * DO NOT commit real keys. Prefer environment variables on cPanel.
 * Set OPENAI_API_KEY via cPanel > MultiPHP INI Editor or a .env loader.
 */
define('AI_API_KEY', getenv('OPENAI_API_KEY') ?: 'sk-REPLACE_WITH_YOUR_KEY');
define('AI_API_URL', 'https://api.openai.com/v1/chat/completions');
define('AI_MODEL',   'gpt-4o-mini');