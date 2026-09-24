<?php

/**
 * Fireworks AI provider.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider\Provider;

use FireworksAi\AiProvider\Metadata\FireworksModelMetadataDirectory;
use FireworksAi\AiProvider\Models\FireworksTextGenerationModel;
use FireworksAi\AiProvider\Util\FireworksConfig;
use WordPress\AiClient\AiClient;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

final class FireworksProvider extends AbstractApiProvider
{
    protected static function baseUrl(): string
    {
        return FireworksConfig::getBaseUrl();
    }

    protected static function createModel(
        ModelMetadata $modelMetadata,
        ProviderMetadata $providerMetadata
    ): ModelInterface {
        foreach ($modelMetadata->getSupportedCapabilities() as $capability) {
            if ($capability->isTextGeneration()) {
                $model = new FireworksTextGenerationModel($modelMetadata, $providerMetadata);
                $model->setRequestOptions(FireworksConfig::createRequestOptions());

                return $model;
            }
        }

        throw new RuntimeException(
            sprintf(
                'The model "%s" has no supported text-generation capability for Fireworks AI.',
                $modelMetadata->getId()
            )
        );
    }

    protected static function createProviderMetadata(): ProviderMetadata
    {
        $args = [
            FireworksConfig::PROVIDER_ID,
            'Fireworks AI',
            ProviderTypeEnum::cloud(),
            'https://app.fireworks.ai/account/api-keys',
            RequestAuthenticationMethod::apiKey(),
        ];

        if (version_compare(AiClient::VERSION, '1.2.0', '>=')) {
            $description = 'OpenAI-compatible text and vision generation with Fireworks AI serverless models.';
            $args[] = function_exists('__')
                ? __('OpenAI-compatible text and vision generation with Fireworks AI serverless models.', 'ai-provider-for-fireworks-ai')
                : $description;
        }

        if (version_compare(AiClient::VERSION, '1.3.0', '>=')) {
            $args[] = dirname(__DIR__, 2) . '/assets/images/fireworks.svg';
        }

        return new ProviderMetadata(...$args);
    }

    protected static function createProviderAvailability(): ProviderAvailabilityInterface
    {
        return new FireworksProviderAvailability();
    }

    protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
    {
        return new FireworksModelMetadataDirectory();
    }
}
