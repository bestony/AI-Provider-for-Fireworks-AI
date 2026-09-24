<?php
/**
 * Plugin Name:       AI Provider for Fireworks AI
 * Plugin URI:        https://github.com/bestony/AI-Provider-for-Fireworks-AI
 * Description:       Fireworks AI provider for the WordPress AI Client.
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Bestony
 * Author URI:        https://github.com/bestony
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       ai-provider-for-fireworks-ai
 *
 * @package FireworksAi\AiProvider
 */

declare(strict_types=1);

namespace FireworksAi\AiProvider;

use FireworksAi\AiProvider\Provider\FireworksProvider;
use FireworksAi\AiProvider\Util\FireworksConfig;
use WordPress\AiClient\AiClient;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/src/autoload.php';

function load_textdomain(): void
{
    load_plugin_textdomain(
        'ai-provider-for-fireworks-ai',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

add_action('init', __NAMESPACE__ . '\\load_textdomain');

function register_provider(): void
{
    if (!class_exists(AiClient::class)) {
        return;
    }

    $registry = AiClient::defaultRegistry();
    if (!$registry->hasProvider(FireworksProvider::class)) {
        $registry->registerProvider(FireworksProvider::class);
    }
}

add_action('init', __NAMESPACE__ . '\\register_provider', 5);

/**
 * Prefer Fireworks' configured default model while preserving other providers.
 *
 * @param mixed $preferredModels Existing [provider_id, model_id] tuples.
 * @return array<int, array{string, string}> Filtered preference tuples.
 */
function prefer_fireworks_models($preferredModels): array
{
    $preferredList = is_array($preferredModels) ? array_values($preferredModels) : [];
    if (!FireworksConfig::hasCredentials()) {
        return $preferredList;
    }

    $defaultModel = FireworksConfig::getDefaultModelId();
    if ($defaultModel === '') {
        return $preferredList;
    }

    $preferred = [[FireworksConfig::PROVIDER_ID, $defaultModel]];
    foreach ($preferredList as $entry) {
        if (!is_array($entry) || count($entry) < 2) {
            continue;
        }

        $entry = array_values($entry);
        if (!is_string($entry[0]) || !is_string($entry[1])) {
            continue;
        }
        if (FireworksConfig::PROVIDER_ID === $entry[0]) {
            continue;
        }

        $preferred[] = [$entry[0], $entry[1]];
    }

    return $preferred;
}

add_filter('wpai_preferred_text_models', __NAMESPACE__ . '\\prefer_fireworks_models');
add_filter('wpai_preferred_vision_models', __NAMESPACE__ . '\\prefer_fireworks_models');
