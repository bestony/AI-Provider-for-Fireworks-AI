<?php

/**
 * Local Fireworks provider availability check.
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider\Provider;

use FireworksAi\AiProvider\Util\FireworksConfig;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;

final class FireworksProviderAvailability implements ProviderAvailabilityInterface
{
    public function isConfigured(): bool
    {
        return FireworksConfig::hasCredentials();
    }
}
