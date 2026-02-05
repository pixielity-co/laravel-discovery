<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Attributes;

use Attribute;

/**
 * Validation Attribute for Property Rules.
 *
 * This attribute is used to mark class properties with validation rules
 * for testing property-level attribute discovery and rule extraction.
 * It supports common validation scenarios like required fields, email
 * validation, and min/max constraints.
 *
 * ## Usage:
 * ```php
 * class UserSettings {
 *     #[TestValidateAttribute(
 *         required: true,
 *         email: true,
 *         min: 5,
 *         max: 100
 *     )]
 *     public string $email;
 * }
 * ```
 *
 * ## Test Scenarios:
 * - Property-level attribute discovery
 * - Validation rule extraction
 * - Boolean flag handling (required, email)
 * - Numeric constraint handling (min, max)
 * - Custom rule arrays
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class TestValidateAttribute
{
    /**
     * Create a new validation attribute instance.
     *
     * @param bool          $required Whether the property value is required (not null/empty)
     * @param bool          $email    Whether the property value must be a valid email address
     * @param int|null      $min      Minimum value for numbers or minimum length for strings
     * @param int|null      $max      Maximum value for numbers or maximum length for strings
     * @param array<string> $rules    Additional custom validation rules
     */
    public function __construct(
        public readonly bool $required = false,
        public readonly bool $email = false,
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly array $rules = [],
    ) {
        // Properties are automatically assigned via constructor promotion
    }
}
