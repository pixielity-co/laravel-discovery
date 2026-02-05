<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Settings;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestValidateAttribute;

/**
 * User Settings Class.
 *
 * A test fixture representing user-specific settings with
 * validation rules. This class is used to test property-level
 * attribute discovery with custom validation rules.
 *
 * ## Properties:
 * - timezone: Required with custom timezone rule
 * - locale: Required with custom locale rule
 * - notifications: Boolean flag (no validation)
 *
 * ## Test Scenarios:
 * - Property-level attribute discovery
 * - Custom validation rules extraction
 * - Required field filtering
 * - Properties without attributes
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class UserSettings
{
    /**
     * User timezone preference.
     *
     * Required field with custom timezone validation rule.
     * Used to test custom rule array extraction.
     */
    #[TestValidateAttribute(required: true, rules: ['timezone'])]
    public string $timezone;

    /**
     * User locale preference.
     *
     * Required field with custom locale validation rule.
     * Used to test custom rule array extraction.
     */
    #[TestValidateAttribute(required: true, rules: ['locale'])]
    public string $locale;

    /**
     * Notifications enabled flag.
     *
     * Boolean property with no validation attribute.
     * Used to test properties without attributes.
     */
    public bool $notifications = true;
}
