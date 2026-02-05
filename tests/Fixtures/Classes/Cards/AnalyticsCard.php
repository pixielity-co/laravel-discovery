<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Cards;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;

/**
 * Analytics Card Component.
 *
 * A test fixture representing an analytics card with lower priority
 * and disabled status. This class is used to test filtering of
 * disabled components and priority-based sorting.
 *
 * ## Attribute Configuration:
 * - Enabled: false (card is inactive)
 * - Priority: 5 (medium priority)
 * - Tags: ['analytics']
 * - Group: 'reports'
 *
 * ## Test Scenarios:
 * - Filtering out disabled cards
 * - Priority comparison
 * - Tag-based filtering
 * - Group-based organization
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[TestCardAttribute(enabled: false, priority: 5, tags: ['analytics'], group: 'reports')]
class AnalyticsCard
{
    /**
     * Render the analytics card.
     *
     * Returns a simple string representation of the card.
     * In a real application, this would return HTML or a view.
     *
     * @return string The rendered card content
     */
    public function render(): string
    {
        // Return a simple identifier for testing
        return 'analytics';
    }
}
