<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Controllers;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestRouteAttribute;

/**
 * Test Controller.
 *
 * A test fixture representing a basic controller with multiple
 * route methods. This class is used to test method-level attribute
 * discovery and route metadata extraction.
 *
 * ## Routes Defined:
 * - GET /users (index)
 * - GET /users/{id} (show)
 * - POST /users (store)
 *
 * ## Test Scenarios:
 * - Method-level attribute discovery
 * - HTTP method filtering (GET, POST)
 * - Middleware extraction
 * - Route naming
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class TestController
{
    /**
     * Display a listing of users.
     *
     * This method demonstrates a basic GET route with authentication
     * middleware and a named route.
     *
     * @return string Simple response for testing
     */
    #[TestRouteAttribute(method: 'GET', path: '/users', middleware: ['auth'], name: 'users.index')]
    public function index(): string
    {
        // Return a simple identifier for testing
        return 'index';
    }

    /**
     * Display a specific user.
     *
     * This method demonstrates a GET route with a parameter
     * and authentication middleware.
     *
     * @return string Simple response for testing
     */
    #[TestRouteAttribute(method: 'GET', path: '/users/{id}', middleware: ['auth'], name: 'users.show')]
    public function show(): string
    {
        // Return a simple identifier for testing
        return 'show';
    }

    /**
     * Store a new user.
     *
     * This method demonstrates a POST route with multiple middleware
     * (authentication and throttling).
     *
     * @return string Simple response for testing
     */
    #[TestRouteAttribute(method: 'POST', path: '/users', middleware: ['auth', 'throttle'], name: 'users.store')]
    public function store(): string
    {
        // Return a simple identifier for testing
        return 'store';
    }
}
