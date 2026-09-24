=== AI Provider for Fireworks AI ===
Contributors:      bestony
Tags:              ai, connector, fireworks, artificial-intelligence
Requires at least: 7.0
Tested up to:      7.1
Stable tag:        1.0.0
Requires PHP:      7.4
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Fireworks AI provider for the WordPress AI Client.

== Description ==

This plugin sends WordPress AI Client text-generation requests through Fireworks AI's
OpenAI-compatible Chat Completions API. It supports text input, vision input, chat history, tool
calling, structured JSON output, and reasoning responses. It does not implement the separate
Fireworks image-generation API.

The default endpoint is:

https://api.fireworks.ai/inference/v1

The default model is:

accounts/fireworks/models/glm-5p3-flash

== Installation ==

1. Upload the plugin directory to /wp-content/plugins/ai-provider-for-fireworks-ai/.
2. Activate the plugin through the Plugins menu.
3. Open Settings → Connectors and save a Fireworks API key.

== Configuration ==

The API key is managed by the WordPress AI Client connector. You can also set FIREWORKS_API_KEY in
the environment. The plugin checks the AI Client registry and does not read the connector option
directly.

Optional environment variables or PHP constants:

* FIREWORKS_BASE_URL — compatible API base URL. Default: https://api.fireworks.ai/inference/v1.
* FIREWORKS_DEFAULT_MODEL — model ID to prefer in model pickers.
* FIREWORKS_STRUCTURED_OUTPUT — json_schema, json_object, or none. Default: json_schema.
* FIREWORKS_REQUEST_TIMEOUT — request timeout in seconds. Default: 120.
* FIREWORKS_CONNECT_TIMEOUT — connection timeout in seconds. Default: 10.
* FIREWORKS_USER_AGENT — optional User-Agent override.

The plugin uses a static catalog of 16 canonical global Serverless model IDs. Fireworks Fast and US
router IDs are excluded because they use a separate endpoint and routing policy.

== External services ==

This plugin sends the selected model ID, prompts, conversation history, system instructions, image
URLs or inline image data, tool definitions and results, JSON schemas, sampling parameters, and
custom options to Fireworks AI when a site uses the provider. Fireworks returns generated content,
tool calls, reasoning content, and token usage. The plugin does not send data until the WordPress AI
Client performs a request, and it does not log prompts, tokens, cookies, or Authorization headers.

* Fireworks API documentation: https://docs.fireworks.ai/getting-started/quickstart#python-openai-sdk
* Serverless pricing: https://docs.fireworks.ai/serverless/pricing
* Vision models: https://docs.fireworks.ai/guides/querying-vision-language-models
* Tool calling: https://docs.fireworks.ai/guides/function-calling
* Structured responses: https://docs.fireworks.ai/structured-responses/structured-response-formatting
* Reasoning: https://docs.fireworks.ai/guides/reasoning
* Terms of Service: https://fireworks.ai/terms-of-service
* Privacy Policy: https://fireworks.ai/privacy-policy

== Changelog ==

= 1.0.0 =
* Initial release with the Fireworks OpenAI-compatible Chat Completions provider.
