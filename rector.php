<?php

/**
 * Rector Configuration - Production Ready.
 *
 * Automated refactoring and code modernization for the Laravel monorepo.
 * This configuration is optimized for production use with comprehensive
 * rule sets and proper exclusions.
 *
 * @see https://github.com/rectorphp/rector
 * @see https://getrector.com/documentation
 *
 * Usage:
 *   Preview changes:  composer rector
 *   Apply changes:    composer rector:fix
 *   Specific path:    vendor/rector/rector/bin/rector process packages/Foundation --dry-run
 *
 * @version 2.0.0
 *
 * @author Pixielity Development Team
 */

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;
use RectorLaravel\Rector\ClassMethod\MakeModelAttributesAndScopesProtectedRector;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\Packages\Faker\FakerSetList;
use RectorLaravel\Set\Packages\Livewire\LivewireSetList;

// use RectorLaravel\Set\Packages\Cashier\CashierSetList; // Uncomment when using Laravel Cashier

return RectorConfig::configure()
    // =========================================================================
    // PATHS CONFIGURATION
    // =========================================================================
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // =========================================================================
    // SKIP CONFIGURATION
    // =========================================================================
    ->withSkip([
        // =====================================================================
        // PATHS TO SKIP
        // =====================================================================
        // Third-party dependencies
        '*/vendor/*',
        __DIR__ . '/vendor',
        // Node modules
        '*/node_modules/*',
        // Storage and cache
        '*/storage/*',
        '*/cache/*',
        '*/bootstrap/cache/*',
        // Build artifacts
        '*/build/*',
        '*/dist/*',
        '*/var/*',
        // Test fixtures (intentionally old code)
        '*/tests/Fixtures/*',
        '*/tests/fixtures/*',
        '*/tests/Stubs/*',
        '*/tests/stubs/*',
        // Test concerns that must match parent signatures
        __DIR__ . '/tests/Concerns/LoadsApplicationWithAttributes.php',// Database migrations (don't modify)
        '*/database/migrations/*',
        '*/src/database/migrations/*',
        '*/Migrations/*',
        // Compiled views
        '*/resources/views/vendor/*',
        // Generated files
        '*/bootstrap/providers.php',
        '*/_ide_helper.php',
        '*/_ide_helper_models.php',
        '*/ide-helper.php',
        // =====================================================================
        // RULES TO SKIP
        // =====================================================================
        // Don't convert string interpolation to sprintf (Laravel convention)
        EncapsedStringsToSprintfRector::class,
        // Don't force newlines after statements (formatting handled by Pint)
        NewlineAfterStatementRector::class,
        // =====================================================================
        // RULES TO SKIP FOR SPECIFIC PATHS
        // =====================================================================
        // Don't make properties readonly in DTOs (they need to be mutable)
        ReadOnlyPropertyRector::class => [
            '*/Data/*',
            '*/DTO/*',
            '*/Dtos/*',
            '*/DataTransferObjects/*',
        ],
        // Don't remove "unused" properties in Models (they're for Eloquent)
        RemoveUnusedPrivatePropertyRector::class => [
            '*/Models/*',
        ],
        // Don't remove "unused" methods in Observers (model lifecycle hooks)
        RemoveUnusedPrivateMethodRector::class => [
            '*/Observers/*',
        ],
        // =====================================================================
        // SKIP SPECIFIC RULES FOR SPECIFIC FILES
        // =====================================================================
        // Don't add parameter types to __call magic method (breaks Laravel compatibility)
        ParamTypeByMethodCallTypeRector::class => [
            __DIR__ . '/tests/Concerns/LoadsApplicationWithAttributes.php',
        ],
        // Don't privatize Laravel scope methods (called via magic methods)
        PrivatizeFinalClassMethodRector::class => [
            '*/Traits/*',
            '*/Models/*',
        ],
        // Don't make Laravel scopes protected - they must be public for magic calling
        MakeModelAttributesAndScopesProtectedRector::class,
        // Don't convert Eloquent magic methods if we prefer them (optional - remove if you want explicit queries)
        // LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        // SKIP TYPE HINTS FOR ORCHESTRA TESTBENCH METHODS
        // =====================================================================
        // Don't add type hints to defineEnvironment() - must match Orchestra Testbench signature
        RenameParamToMatchTypeRector::class => [
            __DIR__ . '/tests/Concerns/LoadsApplicationWithAttributes.php',
        ],
        StrictArrayParamDimFetchRector::class => [
            __DIR__ . '/tests/Concerns/LoadsApplicationWithAttributes.php',
        ],
    ])
    // =========================================================================
    // PHP VERSION TARGET
    // =========================================================================
    ->withPhpSets(
        php84: true  // Target PHP 8.4 features
    )
    // =========================================================================
    // RULE SETS - COMPREHENSIVE PRODUCTION CONFIGURATION
    // =========================================================================
    ->withSets([
        // =====================================================================
        // PHP VERSION UPGRADES
        // =====================================================================

        /*
         * Upgrade to PHP 8.4
         *
         * Includes all PHP 8.x features:
         * - PHP 8.0: Constructor property promotion, named arguments, attributes
         * - PHP 8.1: Readonly properties, enums, never type, final class constants
         * - PHP 8.2: Readonly classes, DNF types, true type
         * - PHP 8.3: Typed class constants, readonly amendments
         * - PHP 8.4: Property hooks, asymmetric visibility
         *
         * Note: LevelSetList::UP_TO_PHP_84 is redundant with withPhpSets(php84: true)
         * but kept for explicit documentation of included features.
         */
        // =====================================================================
        // CODE QUALITY IMPROVEMENTS
        // =====================================================================

        /*
         * Dead Code Removal
         *
         * Removes code that has no effect:
         * - Unused private methods and properties
         * - Unused imports and variables
         * - Unreachable code after return/throw
         * - Empty methods and blocks
         * - Unused parameters
         * - Dead conditions
         */
        SetList::DEAD_CODE,

        /*
         * Code Quality Improvements
         *
         * Improves code quality and readability:
         * - Simplify boolean expressions
         * - Use null coalescing operator
         * - Combine consecutive assignments
         * - Simplify array functions
         * - Inline single-use variables
         * - Remove unnecessary parentheses
         * - Simplify ternary operators
         * - Use spaceship operator
         */
        SetList::CODE_QUALITY,

        /*
         * Coding Style Consistency
         *
         * Applies consistent coding style:
         * - Short array syntax []
         * - Consistent string quotes
         * - Consistent null comparison
         * - Consistent boolean naming
         * - Remove unnecessary semicolons
         * - Consistent use of strict comparison
         */
        SetList::CODING_STYLE,

        /*
         * Early Return Pattern
         *
         * Refactors nested conditions to use early returns:
         * - Reduces nesting levels
         * - Improves readability
         * - Makes code flow clearer
         * - Reduces cognitive complexity
         */
        SetList::EARLY_RETURN,

        /*
         * Privatization
         *
         * Makes class members as private as possible:
         * - Changes public to protected if only used in class
         * - Changes protected to private if only used in class
         * - Makes methods final if not overridden
         * - Reduces public API surface
         */
        SetList::PRIVATIZATION,

        /*
         * Type Declarations
         *
         * Adds missing type declarations:
         * - Parameter types
         * - Return types
         * - Property types
         * - Void return types
         * - Mixed types where needed
         * - Union and intersection types
         */
        SetList::TYPE_DECLARATION,

        /*
         * Naming Conventions
         *
         * Improves naming conventions:
         * - Descriptive variable names
         * - Boolean method names (is*, has*, should*)
         * - Getter/setter standardization
         * - Remove Hungarian notation
         */
        SetList::NAMING,

        /*
         * Instanceof Checks
         *
         * Optimizes instanceof checks:
         * - Removes redundant instanceof checks
         * - Simplifies type checking logic
         * - Improves performance
         */
        SetList::INSTANCEOF,
        // =====================================================================
        // LARAVEL-SPECIFIC
        // =====================================================================

        /*
         * Laravel Code Quality
         *
         * Laravel-specific improvements:
         * - Use Eloquent methods over Query Builder where appropriate
         * - Optimize collection usage
         * - Use Laravel helpers consistently
         * - Improve route definitions
         * - Simplify validation rules
         * - Optimize database queries
         */
        LaravelSetList::LARAVEL_CODE_QUALITY,

        /*
         * Laravel Array/String Functions to Static Calls
         *
         * Converts Laravel helper functions to static calls:
         * - array_* → Arr::*
         * - str_* → Str::*
         * - Improves IDE support and type safety
         */
        LaravelSetList::ARRAY_STR_FUNCTIONS_TO_STATIC_CALL,
        // =====================================================================
        // LARAVEL VERSION UPGRADES (Production/Enterprise)
        // =====================================================================

        /*
         * Laravel 11.x Upgrades
         *
         * Upgrades to Laravel 11.x:
         * - Update deprecated methods
         * - Modernize syntax
         * - Apply new conventions
         */
        LaravelSetList::LARAVEL_110,

        /*
         * Laravel 12.x Upgrades
         *
         * Upgrades to Laravel 12.x (latest):
         * - Update deprecated methods
         * - Modernize syntax
         * - Apply new conventions
         */
        LaravelSetList::LARAVEL_120,
        // =====================================================================
        // LARAVEL BEST PRACTICES (Production/Enterprise)
        // =====================================================================

        /*
         * Laravel Collections
         *
         * Optimize collection usage:
         * - Use collection methods over array functions
         * - Chain collection methods efficiently
         * - Improve readability
         */
        LaravelSetList::LARAVEL_COLLECTION,

        /*
         * Laravel Container String to Fully Qualified Name
         *
         * Replace string class names with ::class constants:
         * - app('App\Services\UserService') → app(UserService::class)
         * - Improves refactoring safety
         * - Better IDE support
         */
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,

        /*
         * Laravel Facade Aliases to Full Names
         *
         * Replace facade aliases with full class names:
         * - Use Illuminate\Support\Facades\Cache instead of Cache
         * - Improves IDE support
         * - Makes dependencies explicit
         */
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,

        /*
         * Laravel Eloquent Magic Method to Query Builder
         *
         * Convert magic methods to explicit query builder calls:
         * - User::whereName('John') → User::where('name', 'John')
         * - Improves type safety
         * - Better IDE support
         */
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,

        /*
         * Laravel If Helpers
         *
         * Use Laravel's conditional helpers:
         * - when(), unless(), tap()
         * - Improves readability
         * - More expressive code
         */
        LaravelSetList::LARAVEL_IF_HELPERS,

        /*
         * Laravel Testing
         *
         * Modernize test code:
         * - Use modern assertion methods
         * - Update deprecated test methods
         * - Improve test readability
         */
        LaravelSetList::LARAVEL_TESTING,

        /*
         * Laravel Type Declarations
         *
         * Add type declarations to Laravel-specific code:
         * - Controller methods
         * - Service classes
         * - Repository methods
         * - Improves type safety
         */
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,

        /*
         * Laravel Factories
         *
         * Modernize factory usage:
         * - Use class-based factories
         * - Update factory methods
         * - Improve factory definitions
         */
        LaravelSetList::LARAVEL_FACTORIES,
        // =====================================================================
        // PACKAGE-SPECIFIC (Based on installed packages)
        // =====================================================================

        /*
         * Livewire 3.0 Upgrades
         *
         * Upgrades Livewire components to version 3.0:
         * - Update component syntax
         * - Modernize lifecycle hooks
         * - Update property bindings
         */
        LivewireSetList::LIVEWIRE_30,

        /*
         * Faker 1.0 Upgrades
         *
         * Upgrades Faker usage to version 1.0:
         * - Update deprecated methods
         * - Modernize faker calls
         */
        FakerSetList::FAKER_10,
        // =====================================================================
        // FUTURE PACKAGES (Uncomment when needed)
        // =====================================================================

        /*
         * Laravel Cashier Upgrades
         *
         * Uncomment when using Laravel Cashier for subscription billing:
         * - Update Cashier API calls
         * - Modernize billing methods
         */
        // CashierSetList::CASHIER_15, // Uncomment when using Cashier
    ])
    // =========================================================================
    // ADDITIONAL RULES (Not in sets or need specific configuration)
    // =========================================================================
    // ->withRules([
    //     // Add custom rules here if needed
    //     // Example: AddVoidReturnTypeWhereNoReturnRector::class,
    // ])
    // =========================================================================
    // RULE CONFIGURATION
    // =========================================================================
    // ->withConfiguredRule(
    //     // Add configured rules here if needed
    //     // Example: EncapsedStringsToSprintfRector::class,
    //     // []
    // )
    // =========================================================================
    // IMPORT NAMES CONFIGURATION
    // =========================================================================
    ->withImportNames(
        importNames: true,  // Add use statements
        importDocBlockNames: true,  // Import in PHPDoc
        importShortClasses: false,  // Don't import short names (App, User, etc.)
        removeUnusedImports: true,  // Remove unused imports
    )
    // =========================================================================
    // PARALLEL PROCESSING - OPTIMIZED FOR PRODUCTION
    // =========================================================================
    ->withParallel(
        timeoutSeconds: 300,  // 5 minutes timeout (increased for large codebases)
        maxNumberOfProcess: 8,  // 8 parallel processes (balanced for stability)
        jobSize: 15,  // 15 files per job (optimized for balance)
    )
    // =========================================================================
    // ADDITIONAL CONFIGURATION
    // =========================================================================
    /*
     * Cache Directory
     *
     * Rector uses cache to speed up subsequent runs.
     * Cache is stored in /tmp by default.
     */
    ->withCache(
        cacheDirectory: __DIR__ . '/var/cache/rector',  // Use default cache implementation
    )
    /*
     * File Extensions
     *
     * Process only PHP files.
     */
    ->withFileExtensions(['php'])
    /*
     * Root Files
     *
     * Include root-level PHP files.
     */
    ->withRootFiles()
    /*
     * Bootstrap Files
     *
     * Load these files before processing (for autoloading, etc.)
     */
    // ->withBootstrapFiles([
    //     __DIR__ . '/vendor/autoload.php',
    // ])
    /*
     * Memory Limit
     *
     * Increase memory limit for large codebases.
     */
    ->withMemoryLimit('2G');
