<?php

/**
 * Versioned Fireworks serverless model catalog.
 *
 * The catalog is intentionally local. Fireworks' management model-list endpoint requires an
 * account ID, while these canonical serverless IDs are public and are suitable for the global
 * inference endpoint.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider\Util;

final class FireworksModelCatalog
{
    /**
     * @var array<string, array{
     *     display_name: string,
     *     vision: bool,
     *     tools: bool,
     *     structured_output: bool,
     *     reasoning: bool,
     *     sampling: bool,
     *     preview: bool
     * }>
     */
    private const CATALOG = [
        'accounts/fireworks/models/kimi-k3' => [
            'display_name' => 'Kimi K3',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => true,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/kimi-k2p7-code' => [
            'display_name' => 'Kimi K2.7 Code',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/kimi-k2p6' => [
            'display_name' => 'Kimi K2.6',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/deepseek-v4p1-flash' => [
            'display_name' => 'DeepSeek V4.1 Flash',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/deepseek-v4-pro-0813' => [
            'display_name' => 'DeepSeek-V4-Pro-0813',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/deepseek-v4-flash-0731' => [
            'display_name' => 'DeepSeek-V4-Flash-0731',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/deepseek-v4-flash-vision-exp' => [
            'display_name' => 'DeepSeek V4 Flash Vision Exp',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => true,
        ],
        'accounts/fireworks/models/glm-5p3-flash' => [
            'display_name' => 'GLM 5.3 Flash',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/glm-5p3' => [
            'display_name' => 'GLM 5.3',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/glm-5p2' => [
            'display_name' => 'GLM 5.2',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/qwen3p8-max' => [
            'display_name' => 'Qwen 3.8 Max',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/minimax-m3' => [
            'display_name' => 'MiniMax M3',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => false,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/gpt-oss-120b' => [
            'display_name' => 'OpenAI GPT OSS 120B',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => true,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/muse-glimmer-30b' => [
            'display_name' => 'Muse Glimmer 30B',
            'vision' => true,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => true,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/nemotron-lightning-3p5-30b-a3b' => [
            'display_name' => 'NVIDIA Nemotron 3.5 Lightning 30B A3B',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => true,
            'sampling' => true,
            'preview' => false,
        ],
        'accounts/fireworks/models/nemotron-3-ultra-nvfp4' => [
            'display_name' => 'NVIDIA Nemotron 3 Ultra (Preview)',
            'vision' => false,
            'tools' => true,
            'structured_output' => true,
            'reasoning' => true,
            'sampling' => true,
            'preview' => true,
        ],
    ];

    /**
     * Return all catalog definitions.
     *
     * @return array<string, array<string, mixed>> Model definitions.
     */
    public static function all(): array
    {
        return self::CATALOG;
    }

    /**
     * Alias for callers that prefer a model-oriented name.
     *
     * @return array<string, array<string, mixed>> Model definitions.
     */
    public static function getModels(): array
    {
        return self::all();
    }

    /**
     * Return canonical IDs in picker order.
     *
     * @return list<string> Model IDs.
     */
    public static function getModelIds(): array
    {
        $ids = array_keys(self::CATALOG);
        usort($ids, [self::class, 'compareModelIds']);

        return $ids;
    }

    /**
     * Get one model definition.
     *
     * @param string $modelId Model ID.
     * @return array<string, mixed>|null Definition, or null for an unknown ID.
     */
    public static function getModel(string $modelId): ?array
    {
        return self::CATALOG[$modelId] ?? null;
    }

    /**
     * Whether a model is in the maintained catalog.
     *
     * @param string $modelId Model ID.
     * @return bool Whether the model is known.
     */
    public static function hasModel(string $modelId): bool
    {
        return isset(self::CATALOG[$modelId]);
    }

    public static function supportsImageInput(string $modelId): bool
    {
        return (bool) (self::CATALOG[$modelId]['vision'] ?? false);
    }

    public static function isVisionModel(string $modelId): bool
    {
        return self::supportsImageInput($modelId);
    }

    public static function supportsTools(string $modelId): bool
    {
        return (bool) (self::CATALOG[$modelId]['tools'] ?? false);
    }

    public static function supportsStructuredOutput(string $modelId): bool
    {
        return (bool) (self::CATALOG[$modelId]['structured_output'] ?? false);
    }

    public static function supportsReasoning(string $modelId): bool
    {
        return (bool) (self::CATALOG[$modelId]['reasoning'] ?? false);
    }

    public static function supportsSampling(string $modelId): bool
    {
        return (bool) (self::CATALOG[$modelId]['sampling'] ?? false);
    }

    public static function isPreview(string $modelId): bool
    {
        return (bool) (self::CATALOG[$modelId]['preview'] ?? false);
    }

    /**
     * Compare model IDs for a stable picker order.
     *
     * The configured default is first, then stable models in the catalog's canonical order, then
     * preview models. Unknown IDs sort naturally after known IDs.
     *
     * @param string $first First model ID.
     * @param string $second Second model ID.
     * @return int Comparison result.
     */
    public static function compareModelIds(string $first, string $second): int
    {
        if ($first === $second) {
            return 0;
        }

        $preferred = FireworksConfig::getDefaultModelId();
        if ($first === $preferred || $second === $preferred) {
            return $first === $preferred ? -1 : 1;
        }

        $firstPreview = self::isPreview($first) ? 1 : 0;
        $secondPreview = self::isPreview($second) ? 1 : 0;
        if ($firstPreview !== $secondPreview) {
            return $firstPreview <=> $secondPreview;
        }

        $ids = array_keys(self::CATALOG);
        $firstIndex = array_search($first, $ids, true);
        $secondIndex = array_search($second, $ids, true);
        if ($firstIndex !== false || $secondIndex !== false) {
            if ($firstIndex === false) {
                return 1;
            }
            if ($secondIndex === false) {
                return -1;
            }

            return $firstIndex <=> $secondIndex;
        }

        return strnatcasecmp($first, $second);
    }
}
