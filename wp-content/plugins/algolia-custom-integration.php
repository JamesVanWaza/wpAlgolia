<?php

/**
 * Plugin Name:     Algolia Custom Integration
 * Description:     Add Algolia Search feature
 * Text Domain:     algolia-custom-integration
 * Version:         1.0.1
 *
 * @package         Algolia_Custom_Integration
 */

// If you're using Composer, require the Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

global $algolia;

$algolia = \Algolia\AlgoliaSearch\SearchClient::create("InsertAPIForProject", "YourAdminApiKey");

require_once __DIR__ . '/wp-cli.php';


