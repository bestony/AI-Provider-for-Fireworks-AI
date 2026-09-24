# AI Provider for Fireworks AI

This plugin registers Fireworks AI as a provider for the WordPress AI Client. It uses Fireworks'
OpenAI-compatible Chat Completions API for text generation, vision input, tool calls, structured JSON
output, and reasoning responses.

## Configuration

1. Install and activate the plugin.
2. Open **Settings > Connectors** and save a Fireworks API key.
3. Use the WordPress AI Client model picker. The default model is
   accounts/fireworks/models/glm-5p3-flash.

The default API base URL is:

    https://api.fireworks.ai/inference/v1

The connector key is managed by WordPress. For deployments that keep secrets outside the database,
define FIREWORKS_API_KEY in the environment. This plugin does not read the connector option
directly; availability comes from the AI Client registry.

Optional environment variables or PHP constants:

* FIREWORKS_BASE_URL — HTTPS-compatible OpenAI API base URL.
* FIREWORKS_DEFAULT_MODEL — model ID to prefer in text and vision pickers.
* FIREWORKS_STRUCTURED_OUTPUT — json_schema (default), json_object, or none.
* FIREWORKS_REQUEST_TIMEOUT — request timeout in seconds. Default: 120.
* FIREWORKS_CONNECT_TIMEOUT — connection timeout in seconds. Default: 10.
* FIREWORKS_USER_AGENT — optional User-Agent override.

The plugin ships a versioned catalog of 16 global Serverless model IDs. Fast and US router IDs are
not included because Fireworks documents a separate endpoint and routing policy for those IDs.

## Data sent to Fireworks

When a WordPress AI Client call uses this provider, the request can include the selected model ID,
prompt messages and conversation history, system instructions, image URLs or inline image data,
tool definitions and tool results, structured-output schemas, sampling parameters, and custom options
provided by the calling code. Fireworks returns generated text, tool calls, reasoning content, and
token usage. This plugin does not log prompts, tokens, cookies, or Authorization headers.

This plugin does not implement Fireworks' separate image-generation API.

## Fireworks documentation

* [Serverless pricing and model catalog](https://docs.fireworks.ai/serverless/pricing)
* [OpenAI SDK quickstart](https://docs.fireworks.ai/getting-started/quickstart#python-openai-sdk)
* [Text models](https://docs.fireworks.ai/guides/querying-text-models)
* [Vision models](https://docs.fireworks.ai/guides/querying-vision-language-models)
* [Tool calling](https://docs.fireworks.ai/guides/function-calling)
* [Structured response formatting](https://docs.fireworks.ai/structured-responses/structured-response-formatting)
* [Reasoning](https://docs.fireworks.ai/guides/reasoning)
* [Terms of Service](https://fireworks.ai/terms-of-service)
* [Privacy Policy](https://fireworks.ai/privacy-policy)

## Development

    php scripts/selfcheck.php
    php scripts/selfcheck.php --sdk=/path/to/php-ai-client/src

The self-check uses a fake HTTP transporter and does not require a real API key.

## License

GPL-2.0-or-later. See LICENSE.
