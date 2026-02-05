<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Attributes;

use Attribute;

/**
 * Simple Test Attribute.
 *
 * A minimal attribute with no properties used for testing basic
 * attribute discovery functionality. This attribute can be applied
 * to classes to mark them for discovery.
 *
 * ## Usage:
 * ```php
 * #[TestAttribute]
 * class MyClass {}
 * ```
 *
 * ## Test Scenarios:
 * - Basic attribute discovery
 * - Class-level attribute detection
 * - Attribute presence validation
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TestAttribute
{
    /**
     * Create a new test attribute instance.
     *
     * This attribute has no constructor parameters as it's designed
     * to be a simple marker attribute for testing purposes.
     */
    public function __construct()
    {
        // No properties to initialize
    }
}
