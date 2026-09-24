<?php

/**
 * Fireworks AI provider configuration.
 *
 * The AI Client owns credentials. This class reads only non-secret configuration and asks the
 * registry whether a credential is available.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider\Util;

use WordPress\AiClient\AiClient;
use WordPress\AiClient\Providers\Http\DTO\RequestOptions;

final class FireworksConfig
{
    public const VERSION = '1.0.0';
    public const PROVIDER_ID = 'fireworks';
    public const DEFAULT_BASE_URL = 'https://api.fireworks.ai/inference/v1';
    public const DEFAULT_MODEL = 'accounts/fireworks/models/glm-5p3-flash';
    public const DEFAULT_STRUCTURED_OUTPUT = 'json_schema';
    public const DEFAULT_REQUEST_TIMEOUT = 120.0;
    public const DEFAULT_CONNECT_TIMEOUT = 10.0;

    /**
     * Resolve an environment variable or PHP constant.
     *
     * Environment variables take precedence over constants.
     *
     * @param string $name Configuration name.
     * @return string Resolved scalar value, or an empty string.
     */
    public static function env(string $name): string
    {
        $value = getenv($name);
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (defined($name)) {
            $constant = constant($name);
            if (is_scalar($constant)) {
                return (string) $constant;
            }
        }

        return '';
    }

    /**
     * Get the Fireworks OpenAI-compatible API base URL.
     *
     * @return string Base URL without a trailing slash or operation suffix.
     */
    public static function getBaseUrl(): string
    {
        $configured = self::env('FIREWORKS_BASE_URL');
        return self::normalizeBaseUrl($configured === '' ? self::DEFAULT_BASE_URL : $configured);
    }

    /**
     * Normalize an API base URL supplied by an administrator.
     *
     * @param string $url Base URL.
     * @return string Normalized base URL.
     */
    public static function normalizeBaseUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');
        if (substr($url, -17) === '/chat/completions') {
            $url = substr($url, 0, -17);
        }

        return rtrim($url, '/');
    }

    /**
     * Get the model placed first in model preference filters.
     *
     * @return string Model ID.
     */
    public static function getDefaultModelId(): string
    {
        $configured = trim(self::env('FIREWORKS_DEFAULT_MODEL'));
        return $configured === '' ? self::DEFAULT_MODEL : $configured;
    }

    /**
     * Get the structured output wire-format mode.
     *
     * @return string One of json_schema, json_object, or none.
     */
    public static function getStructuredOutputMode(): string
    {
        $configured = strtolower(trim(self::env('FIREWORKS_STRUCTURED_OUTPUT')));
        return in_array($configured, ['json_schema', 'json_object', 'none'], true)
            ? $configured
            : self::DEFAULT_STRUCTURED_OUTPUT;
    }

    /**
     * Get the total request timeout in seconds.
     *
     * @return float Request timeout.
     */
    public static function getRequestTimeout(): float
    {
        $configured = self::env('FIREWORKS_REQUEST_TIMEOUT');
        return $configured === ''
            ? self::DEFAULT_REQUEST_TIMEOUT
            : max(1.0, (float) $configured);
    }

    /**
     * Get the connection timeout in seconds.
     *
     * @return float Connection timeout.
     */
    public static function getConnectTimeout(): float
    {
        $configured = self::env('FIREWORKS_CONNECT_TIMEOUT');
        return $configured === ''
            ? self::DEFAULT_CONNECT_TIMEOUT
            : max(1.0, (float) $configured);
    }

    /**
     * Check whether the AI Client registry has a Fireworks credential.
     *
     * This is a local registry lookup. It never reads the connector option and never calls Fireworks.
     *
     * @return bool Whether credentials are configured.
     */
    public static function hasCredentials(): bool
    {
        if (!class_exists(AiClient::class)) {
            return false;
        }

        $registry = AiClient::defaultRegistry();
        if (!$registry->hasProvider(self::PROVIDER_ID)) {
            return false;
        }

        return $registry->getProviderRequestAuthentication(self::PROVIDER_ID) !== null;
    }

    /**
     * Create options used by model and metadata requests.
     *
     * @return RequestOptions Request options.
     */
    public static function createRequestOptions(): RequestOptions
    {
        $options = new RequestOptions();
        $options->setTimeout(self::getRequestTimeout());
        $options->setConnectTimeout(self::getConnectTimeout());

        return $options;
    }

    /**
     * Get the User-Agent sent to Fireworks.
     *
     * @return string User-Agent value.
     */
    public static function getUserAgent(): string
    {
        $configured = trim(self::env('FIREWORKS_USER_AGENT'));
        if ($configured === '') {
            return 'ai-provider-for-fireworks-ai/' . self::VERSION;
        }

        return preg_replace('/[\r\n]+/', ' ', $configured) ?: 'ai-provider-for-fireworks-ai/' . self::VERSION;
    }
}
