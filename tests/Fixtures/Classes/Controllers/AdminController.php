<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Controllers;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestRouteAttribute;

/**
 * Admin Controller.
 *
 * A test fixture representing an admin controller with protected
 * routes. This class is used to test method-level attribute discovery
 * with different middleware configurations.
 *
 * ## Routes Defined:
 * - GET /admin/dashboard (dashboard)
 * - GET /admin/settings (settings)
 *
 * ## Test Scenarios:
 * - Admin route discovery
 * - Multiple middleware filtering
 * - Named vs unnamed routes
 * - Path prefix patterns
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class AdminController
{
    /**
     * Display the admin dashboard.
     *
     * This method demonstrates an admin route with both authentication
     * and admin middleware, plus a named route.
     *
     * @return string Simple response for testing
     */
    #[TestRouteAttribute(method: 'GET', path: '/admin/dashboard', middleware: ['auth', 'admin'], name: 'admin.dashboard')]
    public function dashboard(): string
    {
        // Return a simple identifier for testing
        return 'dashboard';
    }

    /**
     * Display the admin settings page.
     *
     * This method demonstrates an admin route with both authentication
     * and admin middleware, but without a named route.
     *
     * @return string Simple response for testing
     */
    #[TestRouteAttribute(method: 'GET', path: '/admin/settings', middleware: ['auth', 'admin'])]
    public function settings(): string
    {
        // Return a simple identifier for testing
        return 'settings';
    }
}
