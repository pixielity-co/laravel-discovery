<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Attributes;

use Attribute;

/**
 * Route Attribute for HTTP Endpoints.
 *
 * This attribute is used to mark controller methods as HTTP routes
 * with configuration for method, path, middleware, and naming.
 * It's designed to test method-level attribute discovery and filtering.
 *
 * ## Usage:
 * ```php
 * class UserController {
 *     #[TestRouteAttribute(
 *         method: 'GET',
 *         path: '/users',
 *         middleware: ['auth'],
 *         name: 'users.index'
 *     )]
 *     public function index() {}
 * }
 * ```
 *
 * ## Test Scenarios:
 * - Method-level attribute discovery
 * - Property-based filtering (method, path)
 * - Array property handling (middleware)
 * - Route metadata extraction
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
class TestRouteAttribute
{
    /**
     * Create a new route attribute instance.
     *
     * @param string        $method     HTTP method (GET, POST, PUT, PATCH, DELETE, etc.)
     * @param string        $path       Route URI path (e.g., '/users/{id}')
     * @param array<string> $middleware Middleware stack to apply to this route
     * @param string|null   $name       Optional route name for URL generation
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $middleware = [],
        public readonly ?string $name = null,
    ) {
        // Properties are automatically assigned via constructor promotion
    }
}
