<?php

/**
 * Request construction shared by Fireworks models.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider\Models;

use FireworksAi\AiProvider\Provider\FireworksProvider;
use FireworksAi\AiProvider\Util\FireworksConfig;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;

trait FireworksRequestTrait
{
    /**
     * Create a request against Fireworks' OpenAI-compatible API.
     *
     * @param HttpMethodEnum $method HTTP method.
     * @param string $path Relative endpoint path.
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
        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['User-Agent'] = FireworksConfig::getUserAgent();

        return new Request(
            $method,
            FireworksProvider::url($path),
            $headers,
            $data,
            $this->getRequestOptions()
        );
    }
}
