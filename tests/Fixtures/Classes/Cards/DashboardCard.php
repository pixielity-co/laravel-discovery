<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Cards;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;

/**
 * Dashboard Card Component.
 *
 * A test fixture representing a dashboard card with high priority
 * and enabled status. This class is used to test attribute-based
 * discovery with property filtering.
 *
 * ## Attribute Configuration:
 * - Enabled: true (card is active)
 * - Priority: 10 (high priority)
 * - Tags: ['dashboard', 'analytics']
 * - Group: 'main'
 *
 * ## Test Scenarios:
 * - Discovering enabled cards
 * - Filtering by priority
 * - Tag-based filtering
 * - Group-based organization
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[TestCardAttribute(enabled: true, priority: 10, tags: ['dashboard', 'analytics'], group: 'main')]
class DashboardCard
{
    /**
     * Render the dashboard card.
     *
     * Returns a simple string representation of the card.
     * In a real application, this would return HTML or a view.
     *
     * @return string The rendered card content
     */
    public function render(): string
    {
        // Return a simple identifier for testing
        return 'dashboard';
    }
}
