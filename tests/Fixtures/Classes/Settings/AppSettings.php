<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Settings;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestValidateAttribute;

/**
 * Application Settings Class.
 *
 * A test fixture representing application-level settings with
 * validation rules. This class is used to test property-level
 * attribute discovery and validation rule extraction.
 *
 * ## Properties:
 * - name: Required string (3-50 chars)
 * - email: Required email address
 * - url: Optional string
 * - debug: Boolean flag (no validation)
 *
 * ## Test Scenarios:
 * - Property-level attribute discovery
 * - Required field filtering
 * - Email validation detection
 * - Min/max constraint extraction
 * - Optional property handling
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class AppSettings
{
    /**
     * Application name.
     *
     * Required field with length constraints (3-50 characters).
     * Used to test min/max validation rule extraction.
     *
     * @var string
     */
    #[TestValidateAttribute(required: true, min: 3, max: 50)]
    public string $name;

    /**
     * Application email address.
     *
     * Required field with email validation.
     * Used to test email validation rule detection.
     *
     * @var string
     */
    #[TestValidateAttribute(required: true, email: true)]
    public string $email;

    /**
     * Application URL.
     *
     * Optional field with no additional validation.
     * Used to test optional property handling.
     *
     * @var string|null
     */
    #[TestValidateAttribute(required: false)]
    public ?string $url = null;

    /**
     * Debug mode flag.
     *
     * Boolean property with no validation attribute.
     * Used to test properties without attributes.
     *
     * @var bool
     */
    public bool $debug = false;
}
