<?php

/**
 * Local Fireworks model metadata directory.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider\Metadata;

use FireworksAi\AiProvider\Provider\FireworksProvider;
use FireworksAi\AiProvider\Util\FireworksConfig;
use FireworksAi\AiProvider\Util\FireworksModelCatalog;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleModelMetadataDirectory;

final class FireworksModelMetadataDirectory extends AbstractOpenAiCompatibleModelMetadataDirectory
{
    protected function getBaseCacheKey(): string
    {
        return parent::getBaseCacheKey() . '_' . md5(
            serialize(FireworksModelCatalog::all()) . '|' . FireworksConfig::getDefaultModelId()
        );
    }

    /**
     * Build metadata from the versioned local catalog. No models request is made.
     *
     * @return array<string, ModelMetadata> Model metadata keyed by ID.
     */
    protected function sendListModelsRequest(): array
    {
        $models = [];
        foreach (FireworksModelCatalog::getModelIds() as $modelId) {
            $models[$modelId] = $this->createMetadata($modelId);
        }

        return $models;
    }

    /**
     * Required by the OpenAI-compatible base class; the local catalog does not parse responses.
     *
     * @param Response $response Unused response.
     * @return list<ModelMetadata> Empty list.
     */
    protected function parseResponseToModelMetadataList(Response $response): array
    {
        return [];
    }

    /**
     * Create a request for the abstract directory contract.
     *
     * @param HttpMethodEnum $method HTTP method.
     * @param string $path Relative path.
     * @param array<string, string|list<string>> $headers Request headers.
     * @param string|array<string, mixed>|null $data Request data.
     * @return Request Request object.
     */
    protected function createRequest(
        HttpMethodEnum $method,
        string $path,
        array $headers = [],
        $data = null
    ): Request {
        $headers['User-Agent'] = FireworksConfig::getUserAgent();

        return new Request(
            $method,
            FireworksProvider::url($path),
            $headers,
            $data,
            FireworksConfig::createRequestOptions()
        );
    }

    /**
     * Create metadata for known explicit model IDs.
     *
     * @param list<string> $modelIds Model IDs.
     * @return array<string, ModelMetadata> Known model metadata.
     */
    protected function createModelMetadataForExplicitModelIds(array $modelIds): array
    {
        $metadata = [];
        foreach ($modelIds as $modelId) {
            if (FireworksModelCatalog::hasModel($modelId)) {
                $metadata[$modelId] = $this->createMetadata($modelId);
            }
        }

        return $metadata;
    }

    private function createMetadata(string $modelId): ModelMetadata
    {
        $inputModalities = [[ModalityEnum::text()]];
        if (FireworksModelCatalog::supportsImageInput($modelId)) {
            $inputModalities[] = [ModalityEnum::text(), ModalityEnum::image()];
        }

        $options = [
            new SupportedOption(OptionEnum::systemInstruction()),
            new SupportedOption(OptionEnum::maxTokens()),
            new SupportedOption(OptionEnum::stopSequences()),
            new SupportedOption(OptionEnum::customOptions()),
            new SupportedOption(OptionEnum::inputModalities(), $inputModalities),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::text()]]),
        ];

        if (FireworksModelCatalog::supportsStructuredOutput($modelId)) {
            $options[] = new SupportedOption(OptionEnum::outputMimeType(), ['text/plain', 'application/json']);
            $options[] = new SupportedOption(OptionEnum::outputSchema());
        }

        if (FireworksModelCatalog::supportsTools($modelId)) {
            $options[] = new SupportedOption(OptionEnum::functionDeclarations());
        }

        if (FireworksModelCatalog::supportsSampling($modelId)) {
            $options = array_merge(
                $options,
                [
                    new SupportedOption(OptionEnum::candidateCount()),
                    new SupportedOption(OptionEnum::temperature()),
                    new SupportedOption(OptionEnum::topP()),
                    new SupportedOption(OptionEnum::presencePenalty()),
                    new SupportedOption(OptionEnum::frequencyPenalty()),
                    new SupportedOption(OptionEnum::logprobs()),
                    new SupportedOption(OptionEnum::topLogprobs()),
                ]
            );
        }

        $definition = FireworksModelCatalog::getModel($modelId);

        return new ModelMetadata(
            $modelId,
            is_array($definition) && isset($definition['display_name'])
                ? (string) $definition['display_name']
                : $modelId,
            [
                CapabilityEnum::textGeneration(),
                CapabilityEnum::chatHistory(),
            ],
            $options
        );
    }
}
