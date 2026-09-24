<?php

/**
 * Fireworks OpenAI-compatible chat completions model.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider\Models;

use FireworksAi\AiProvider\Util\FireworksConfig;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

final class FireworksTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel
{
    use FireworksRequestTrait;

    protected function prepareGenerateTextParams(array $prompt): array
    {
        $params = parent::prepareGenerateTextParams($prompt);

        if (isset($params['response_format']) && $params['response_format'] === []) {
            unset($params['response_format']);
        }

        return $params;
    }

    /**
     * Build Fireworks' named JSON Schema response format.
     *
     * @param array<string, mixed>|null $outputSchema Output schema, or null for JSON mode.
     * @return array<string, mixed> Response format, or an empty array when disabled.
     */
    protected function prepareResponseFormatParam(?array $outputSchema): array
    {
        $mode = FireworksConfig::getStructuredOutputMode();
        if ($mode === 'none') {
            return [];
        }

        if ($mode === 'json_object' || !is_array($outputSchema)) {
            return ['type' => 'json_object'];
        }

        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'fireworks_response',
                'schema' => $outputSchema,
            ],
        ];
    }
}
