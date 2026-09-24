<?php

/**
 * Runnable self-check for the Fireworks AI provider.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

$root = dirname(__DIR__);
if (!defined('ABSPATH')) {
    define('ABSPATH', $root . '/');
}

require $root . '/src/autoload.php';

use FireworksAi\AiProvider\Util\FireworksConfig;
use FireworksAi\AiProvider\Util\FireworksModelCatalog;

$checks = 0;
$failures = 0;

/**
 * Record one assertion.
 *
 * @param bool $condition Assertion result.
 * @param string $description Assertion description.
 * @return void
 */
function check(bool $condition, string $description): void
{
    global $checks, $failures;
    $checks++;

    if ($condition) {
        fwrite(STDOUT, "ok    {$description}\n");
        return;
    }

    $failures++;
    fwrite(STDERR, "FAIL  {$description}\n");
}

foreach (
    [
        'FIREWORKS_BASE_URL',
        'FIREWORKS_DEFAULT_MODEL',
        'FIREWORKS_STRUCTURED_OUTPUT',
        'FIREWORKS_REQUEST_TIMEOUT',
        'FIREWORKS_CONNECT_TIMEOUT',
        'FIREWORKS_USER_AGENT',
    ] as $name
) {
    putenv($name);
}

$modelIds = FireworksModelCatalog::getModelIds();
check(count($modelIds) === 16, 'catalog contains 16 canonical model IDs');
check(
    in_array('accounts/fireworks/models/glm-5p3-flash', $modelIds, true),
    'catalog contains the default GLM model'
);
check(
    $modelIds[0] === FireworksConfig::DEFAULT_MODEL,
    'default model is first in picker order'
);
check(
    FireworksModelCatalog::supportsImageInput('accounts/fireworks/models/kimi-k3'),
    'Kimi K3 is marked as vision capable'
);
check(
    FireworksModelCatalog::supportsImageInput('accounts/fireworks/models/glm-5p3-flash'),
    'GLM 5.3 Flash is marked as vision capable'
);
check(
    !FireworksModelCatalog::supportsImageInput('accounts/fireworks/models/glm-5p2'),
    'GLM 5.2 is marked as text only'
);
check(
    FireworksModelCatalog::supportsTools('accounts/fireworks/models/glm-5p3-flash'),
    'catalog marks tool calling support'
);
check(
    FireworksModelCatalog::supportsStructuredOutput('accounts/fireworks/models/glm-5p3-flash'),
    'catalog marks structured output support'
);
check(
    FireworksModelCatalog::supportsReasoning('accounts/fireworks/models/gpt-oss-120b'),
    'catalog marks GPT OSS reasoning support'
);
check(
    FireworksModelCatalog::isPreview('accounts/fireworks/models/nemotron-3-ultra-nvfp4'),
    'preview model is classified'
);
check(
    FireworksModelCatalog::compareModelIds(
        'accounts/fireworks/models/glm-5p3-flash',
        'accounts/fireworks/models/nemotron-3-ultra-nvfp4'
    ) < 0,
    'stable model sorts before preview model'
);
check(
    FireworksConfig::getBaseUrl() === FireworksConfig::DEFAULT_BASE_URL,
    'default base URL'
);
check(
    FireworksConfig::normalizeBaseUrl('https://example.test/v1/chat/completions') === 'https://example.test/v1',
    'chat completions suffix is removed from a base URL'
);
check(
    FireworksConfig::getDefaultModelId() === FireworksConfig::DEFAULT_MODEL,
    'default model configuration'
);
check(
    FireworksConfig::getRequestTimeout() >= 60.0 && FireworksConfig::getConnectTimeout() >= 5.0,
    'timeouts are long enough for an LLM request'
);
check(
    FireworksConfig::getUserAgent() === 'ai-provider-for-fireworks-ai/1.0.0',
    'User-Agent identifies the plugin version'
);
check(!FireworksConfig::hasCredentials(), 'without the AI Client no credential is reported');

putenv('FIREWORKS_BASE_URL=https://fireworks.example.test/inference/v1/');
putenv('FIREWORKS_DEFAULT_MODEL=accounts/fireworks/models/kimi-k3');
putenv('FIREWORKS_STRUCTURED_OUTPUT=json_object');
putenv('FIREWORKS_REQUEST_TIMEOUT=180');
putenv('FIREWORKS_CONNECT_TIMEOUT=15');
putenv('FIREWORKS_USER_AGENT=fireworks-selfcheck/1');
check(
    FireworksConfig::getBaseUrl() === 'https://fireworks.example.test/inference/v1',
    'base URL environment override is normalized'
);
check(
    FireworksConfig::getDefaultModelId() === 'accounts/fireworks/models/kimi-k3',
    'default model environment override'
);
check(FireworksConfig::getStructuredOutputMode() === 'json_object', 'JSON object mode override');
check(FireworksConfig::getRequestTimeout() === 180.0, 'request timeout override');
check(FireworksConfig::getConnectTimeout() === 15.0, 'connect timeout override');
check(FireworksConfig::getUserAgent() === 'fireworks-selfcheck/1', 'User-Agent override');

foreach (['FIREWORKS_BASE_URL', 'FIREWORKS_DEFAULT_MODEL', 'FIREWORKS_STRUCTURED_OUTPUT', 'FIREWORKS_REQUEST_TIMEOUT', 'FIREWORKS_CONNECT_TIMEOUT', 'FIREWORKS_USER_AGENT'] as $name) {
    putenv($name);
}

$sdkPath = null;
foreach ($argv as $argument) {
    if (strpos($argument, '--sdk=') === 0) {
        $sdkPath = substr($argument, 6);
    }
}

if ($sdkPath !== null && is_file($sdkPath . '/polyfills.php')) {
    require $sdkPath . '/polyfills.php';
    spl_autoload_register(static function (string $class) use ($sdkPath): void {
        $prefix = 'WordPress\\AiClient\\';
        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $file = $sdkPath . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });

    use_sdk_checks();
} else {
    fwrite(STDOUT, "skip  SDK-dependent checks (pass --sdk=<path to php-ai-client/src> to run them)\n");
}

fwrite(STDOUT, sprintf("\n%d checks, %d failure(s)\n", $checks, $failures));
exit($failures === 0 ? 0 : 1);

/**
 * Exercise the provider against the real SDK and a fake transporter.
 *
 * @return void
 */
function use_sdk_checks(): void
{
    $provider = \FireworksAi\AiProvider\Provider\FireworksProvider::class;
    $directory = $provider::modelMetadataDirectory();
    $models = $directory->listModelMetadata();
    check(count($models) === 16, 'local catalog becomes SDK metadata without a list request');
    check($models[0]->getId() === \FireworksAi\AiProvider\Util\FireworksConfig::DEFAULT_MODEL, 'SDK metadata keeps the default first');

    $byId = [];
    foreach ($models as $model) {
        $byId[$model->getId()] = $model;
    }

    $optionNames = static function (\WordPress\AiClient\Providers\Models\DTO\ModelMetadata $model): array {
        return array_map(
            static function ($option): string {
                return $option->getName()->value;
            },
            $model->getSupportedOptions()
        );
    };
    check(
        in_array('functionDeclarations', $optionNames($byId[\FireworksAi\AiProvider\Util\FireworksConfig::DEFAULT_MODEL]), true),
        'default model declares tool calling'
    );
    check(
        in_array('outputSchema', $optionNames($byId[\FireworksAi\AiProvider\Util\FireworksConfig::DEFAULT_MODEL]), true),
        'default model declares structured output'
    );

    $transporter = new class implements \WordPress\AiClient\Providers\Http\Contracts\HttpTransporterInterface {
        public $request = null;
        public $body = [];
        public $sendCount = 0;

        public function send(
            \WordPress\AiClient\Providers\Http\DTO\Request $request,
            ?\WordPress\AiClient\Providers\Http\DTO\RequestOptions $options = null
        ): \WordPress\AiClient\Providers\Http\DTO\Response {
            $this->sendCount++;
            $this->request = $request;
            $this->body = json_decode((string) $request->getBody(), true);

            return new \WordPress\AiClient\Providers\Http\DTO\Response(
                200,
                [],
                json_encode([
                    'id' => 'fireworks-selfcheck',
                    'choices' => [[
                        'message' => [
                            'role' => 'assistant',
                            'reasoning_content' => 'pondering',
                            'content' => '{"answer":"ok"}',
                        ],
                        'finish_reason' => 'stop',
                    ]],
                    'usage' => [
                        'prompt_tokens' => 2,
                        'completion_tokens' => 3,
                        'total_tokens' => 5,
                    ],
                ])
            );
        }
    };

    $registry = \WordPress\AiClient\AiClient::defaultRegistry();
    $registry->setHttpTransporter($transporter);
    if (!$registry->hasProvider($provider)) {
        $registry->registerProvider($provider);
    }
    check(!\FireworksAi\AiProvider\Util\FireworksConfig::hasCredentials(), 'registered provider has no credential by default');
    $registry->setProviderRequestAuthentication(
        \FireworksAi\AiProvider\Util\FireworksConfig::PROVIDER_ID,
        new \WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication('selfcheck-token')
    );
    check(\FireworksAi\AiProvider\Util\FireworksConfig::hasCredentials(), 'credential presence comes from the registry');

    $model = new \FireworksAi\AiProvider\Models\FireworksTextGenerationModel(
        $directory->getModelMetadata(\FireworksAi\AiProvider\Util\FireworksConfig::DEFAULT_MODEL),
        $provider::metadata()
    );
    $model->setHttpTransporter($transporter);
    $model->setRequestAuthentication(
        new \WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication('selfcheck-token')
    );
    $model->setRequestOptions(\FireworksAi\AiProvider\Util\FireworksConfig::createRequestOptions());
    $model->setConfig(\WordPress\AiClient\Providers\Models\DTO\ModelConfig::fromArray([
        'maxTokens' => 64,
        'temperature' => 0.2,
        'topP' => 0.8,
        'presencePenalty' => 0.1,
        'frequencyPenalty' => 0.1,
        'logprobs' => true,
        'topLogprobs' => 2,
        'outputMimeType' => 'application/json',
        'outputSchema' => ['type' => 'object', 'properties' => ['answer' => ['type' => 'string']]],
        'functionDeclarations' => [[
            'name' => 'lookup',
            'description' => 'Look up a value.',
            'parameters' => ['type' => 'object'],
        ]],
    ]));
    $result = $model->generateTextResult([
        new \WordPress\AiClient\Messages\DTO\Message(
            \WordPress\AiClient\Messages\Enums\MessageRoleEnum::user(),
            [new \WordPress\AiClient\Messages\DTO\MessagePart('Hello')]
        ),
    ]);

    check(
        $transporter->request->getUri() === 'https://api.fireworks.ai/inference/v1/chat/completions',
        'generation request uses the Fireworks chat completions URL'
    );
    $headers = $transporter->request->getHeaders();
    check(
        isset($headers['Authorization'][0]) && $headers['Authorization'][0] === 'Bearer selfcheck-token',
        'generation request carries Bearer authentication'
    );
    check(
        isset($headers['User-Agent'][0]) && strpos($headers['User-Agent'][0], 'ai-provider-for-fireworks-ai/') === 0,
        'generation request carries the plugin User-Agent'
    );
    check(
        isset($transporter->body['response_format']['json_schema']['name'])
            && isset($transporter->body['response_format']['json_schema']['schema']),
        'structured output uses the named JSON Schema wrapper'
    );
    check(
        ($transporter->body['temperature'] ?? null) === 0.2
            && ($transporter->body['top_p'] ?? null) === 0.8
            && ($transporter->body['presence_penalty'] ?? null) === 0.1
            && ($transporter->body['frequency_penalty'] ?? null) === 0.1
            && ($transporter->body['logprobs'] ?? null) === true
            && ($transporter->body['top_logprobs'] ?? null) === 2,
        'Fireworks sampling parameters remain in the request'
    );
    check(
        isset($transporter->body['tools'][0]['function']['name'])
            && $transporter->body['tools'][0]['function']['name'] === 'lookup',
        'tool declarations use the OpenAI-compatible wire format'
    );
    putenv('FIREWORKS_STRUCTURED_OUTPUT=none');
    $model->generateTextResult([
        new \WordPress\AiClient\Messages\DTO\Message(
            \WordPress\AiClient\Messages\Enums\MessageRoleEnum::user(),
            [new \WordPress\AiClient\Messages\DTO\MessagePart('Plain response')]
        ),
    ]);
    check(!isset($transporter->body['response_format']), 'disabled structured output omits response_format');
    putenv('FIREWORKS_STRUCTURED_OUTPUT');
    $parts = $result->getCandidates()[0]->getMessage()->getParts();
    $thoughtTexts = [];
    foreach ($parts as $part) {
        if ($part->getChannel()->isThought()) {
            $thoughtTexts[] = $part->getText();
        }
    }
    check(in_array('pondering', $thoughtTexts, true), 'reasoning_content becomes a thought part');

    $model->generateTextResult([
        new \WordPress\AiClient\Messages\DTO\Message(
            \WordPress\AiClient\Messages\Enums\MessageRoleEnum::user(),
            [
                new \WordPress\AiClient\Messages\DTO\MessagePart('Describe this image.'),
                new \WordPress\AiClient\Messages\DTO\MessagePart(
                    new \WordPress\AiClient\Files\DTO\File('https://images.example.test/sample.png', 'image/png')
                ),
            ]
        ),
    ]);
    $imageMessage = $transporter->body['messages'][0]['content'] ?? [];
    $hasImageUrl = false;
    foreach ($imageMessage as $contentPart) {
        if (
            is_array($contentPart)
            && ($contentPart['type'] ?? null) === 'image_url'
            && ($contentPart['image_url']['url'] ?? null) === 'https://images.example.test/sample.png'
        ) {
            $hasImageUrl = true;
        }
    }
    check($hasImageUrl, 'remote image input uses the OpenAI image_url content block');
}
