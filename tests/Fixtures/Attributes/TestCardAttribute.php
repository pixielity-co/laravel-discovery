<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Attributes;

use Attribute;

/**
 * Card Attribute for Dashboard Components.
 *
 * This attribute is used to mark classes as dashboard cards with
 * configuration properties like enabled status, priority, tags, and grouping.
 * It's designed to test complex attribute discovery with property filtering.
 *
 * ## Usage:
 * ```php
 * #[TestCardAttribute(
 *     enabled: true,
 *     priority: 10,
 *     tags: ['dashboard', 'analytics'],
 *     group: 'main'
 * )]
 * class DashboardCard {}
 * ```
 *
 * ## Test Scenarios:
 * - Attribute discovery with properties
 * - Property-based filtering (enabled, priority)
 * - Array property handling (tags)
 * - Optional property handling (group)
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TestCardAttribute
{
    /**
     * Create a new card attribute instance.
     *
     * @param bool          $enabled  Whether the card is enabled and should be displayed
     * @param int           $priority Display priority (higher values appear first)
     * @param array<string> $tags     Categorization tags for filtering and grouping
     * @param string|null   $group    Optional group name for organizing cards
     */
    public function __construct(
        public readonly bool $enabled = true,
        public readonly int $priority = 0,
        public readonly array $tags = [],
        public readonly ?string $group = null,
    ) {
        // Properties are automatically assigned via constructor promotion
    }
}
