<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Attributes;

use Attribute;

/**
 * Service Attribute for Dependency Injection.
 *
 * This attribute is used to mark classes as services that should be
 * automatically registered in the service container. It includes
 * configuration for singleton binding and alias registration.
 *
 * ## Usage:
 * ```php
 * #[TestServiceAttribute(
 *     singleton: true,
 *     alias: 'my.service'
 * )]
 * class MyService implements ServiceInterface {}
 * ```
 *
 * ## Test Scenarios:
 * - Service class discovery
 * - Attribute-based auto-registration
 * - Singleton vs transient binding
 * - Service alias resolution
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TestServiceAttribute
{
    /**
     * Create a new service attribute instance.
     *
     * @param bool   $singleton Whether the service should be registered as a singleton
     * @param string $alias     Container binding alias for easier resolution
     */
    public function __construct(
        public readonly bool $singleton = true,
        public readonly string $alias = '',
    ) {
        // Properties are automatically assigned via constructor promotion
    }
}
