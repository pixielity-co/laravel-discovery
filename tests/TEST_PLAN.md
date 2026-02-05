# Discovery Package - Comprehensive Test Plan

## Test Structure Overview

```
tests/
├── TestCase.php                          # Base test case
├── Fixtures/                             # Test fixtures and stubs
│   ├── Attributes/                       # Test attributes
│   │   ├── TestAttribute.php
│   │   ├── TestRouteAttribute.php
│   │   ├── TestValidateAttribute.php
│   │   └── TestCardAttribute.php
│   ├── Classes/                          # Test classes
│   │   ├── TestController.php
│   │   ├── TestService.php
│   │   ├── TestCommand.php
│   │   ├── AbstractTestClass.php
│   │   └── TestInterface.php
│   └── Directories/                      # Test directory structures
│       ├── Package1/
│       ├── Package2/
│       └── Module1/
├── Unit/                                 # Unit tests (isolated)
│   ├── Cache/
│   │   └── CacheManagerTest.php
│   ├── Strategies/
│   │   ├── AttributeStrategyTest.php
│   │   ├── DirectoryStrategyTest.php
│   │   ├── InterfaceStrategyTest.php
│   │   ├── ParentClassStrategyTest.php
│   │   ├── MethodStrategyTest.php
│   │   └── PropertyStrategyTest.php
│   ├── Filters/
│   │   ├── PropertyFilterTest.php
│   │   └── CallbackFilterTest.php
│   ├── Validators/
│   │   ├── InstantiableValidatorTest.php
│   │   ├── ExtendsValidatorTest.php
│   │   └── ImplementsValidatorTest.php
│   ├── Resolvers/
│   │   └── NamespaceResolverTest.php
│   ├── Factories/
│   │   └── StrategyFactoryTest.php
│   ├── DiscoveryManagerTest.php
│   └── DiscoveryBuilderTest.php
└── Feature/                              # Integration tests
    ├── AttributeDiscoveryTest.php
    ├── DirectoryDiscoveryTest.php
    ├── MethodDiscoveryTest.php
    ├── PropertyDiscoveryTest.php
    ├── InterfaceDiscoveryTest.php
    ├── ParentClassDiscoveryTest.php
    ├── ChainedDiscoveryTest.php          # Complex chaining scenarios
    ├── CachingTest.php
    ├── MonorepoDiscoveryTest.php
    └── RealWorldScenariosTest.php
```

---

## Unit Tests (Isolated Component Testing)

### 1. CacheManagerTest.php

**Purpose:** Test file-based caching system

**Test Methods:**

- `test_can_store_and_retrieve_cache()`
- `test_returns_null_for_missing_cache()`
- `test_can_clear_specific_cache()`
- `test_can_clear_all_caches()`
- `test_respects_cache_enabled_config()`
- `test_creates_cache_directory_if_not_exists()`
- `test_handles_invalid_cache_data_gracefully()`
- `test_cache_key_sanitization()`

---

### 2. AttributeStrategyTest.php

**Purpose:** Test attribute-based discovery

**Test Methods:**

- `test_discovers_classes_with_attribute()`
- `test_returns_empty_array_when_no_classes_found()`
- `test_includes_attribute_metadata()`
- `test_handles_multiple_attributes_on_same_class()`
- `test_discovers_attribute_with_properties()`
- `test_handles_non_existent_attribute()`
- `test_filters_by_attribute_property_values()`

---

### 3. DirectoryStrategyTest.php

**Purpose:** Test directory scanning

**Test Methods:**

- `test_discovers_classes_in_single_directory()`
- `test_discovers_classes_in_multiple_directories()`
- `test_handles_glob_patterns()`
- `test_handles_non_existent_directory()`
- `test_excludes_non_php_files()`
- `test_resolves_namespaces_correctly()`
- `test_handles_nested_directories()`
- `test_handles_empty_directory()`

---

### 4. MethodStrategyTest.php

**Purpose:** Test method attribute discovery

**Test Methods:**

- `test_discovers_methods_with_attribute()`
- `test_returns_method_metadata()`
- `test_includes_class_and_method_name()`
- `test_includes_file_and_line_number()`
- `test_handles_multiple_methods_in_same_class()`
- `test_handles_static_methods()`
- `test_handles_private_protected_public_methods()`

---

### 5. PropertyStrategyTest.php

**Purpose:** Test property attribute discovery

**Test Methods:**

- `test_discovers_properties_with_attribute()`
- `test_returns_property_metadata()`
- `test_includes_class_and_property_name()`
- `test_handles_multiple_properties_in_same_class()`
- `test_handles_static_properties()`
- `test_handles_private_protected_public_properties()`
- `test_handles_typed_properties()`

---

### 6. InterfaceStrategyTest.php

**Purpose:** Test interface implementation discovery

**Test Methods:**

- `test_discovers_classes_implementing_interface()`
- `test_excludes_interfaces_themselves()`
- `test_handles_multiple_interfaces()`
- `test_handles_nested_interface_inheritance()`
- `test_returns_empty_when_no_implementations()`

---

### 7. ParentClassStrategyTest.php

**Purpose:** Test parent class extension discovery

**Test Methods:**

- `test_discovers_classes_extending_parent()`
- `test_excludes_abstract_classes_when_specified()`
- `test_handles_multi_level_inheritance()`
- `test_excludes_parent_class_itself()`
- `test_returns_empty_when_no_extensions()`

---

### 8. PropertyFilterTest.php

**Purpose:** Test property-based filtering

**Test Methods:**

- `test_filters_by_exact_match()`
- `test_filters_by_not_equal()`
- `test_filters_by_greater_than()`
- `test_filters_by_less_than()`
- `test_filters_by_contains()`
- `test_filters_by_in_array()`
- `test_handles_nested_properties()`
- `test_handles_null_values()`

---

### 9. CallbackFilterTest.php

**Purpose:** Test custom callback filtering

**Test Methods:**

- `test_applies_callback_filter()`
- `test_receives_correct_parameters()`
- `test_handles_multiple_callbacks()`
- `test_handles_exception_in_callback()`

---

### 10. InstantiableValidatorTest.php

**Purpose:** Test instantiable class validation

**Test Methods:**

- `test_validates_concrete_classes()`
- `test_rejects_abstract_classes()`
- `test_rejects_interfaces()`
- `test_rejects_traits()`
- `test_handles_classes_with_constructor_params()`

---

### 11. ExtendsValidatorTest.php

**Purpose:** Test parent class validation

**Test Methods:**

- `test_validates_classes_extending_parent()`
- `test_rejects_classes_not_extending_parent()`
- `test_handles_multi_level_inheritance()`
- `test_handles_non_existent_parent_class()`

---

### 12. ImplementsValidatorTest.php

**Purpose:** Test interface implementation validation

**Test Methods:**

- `test_validates_classes_implementing_interface()`
- `test_rejects_classes_not_implementing_interface()`
- `test_handles_multiple_interfaces()`
- `test_handles_non_existent_interface()`

---

### 13. NamespaceResolverTest.php

**Purpose:** Test namespace resolution

**Test Methods:**

- `test_resolves_namespace_from_file_path()`
- `test_handles_monorepo_packages()`
- `test_handles_monorepo_modules()`
- `test_handles_app_directory()`
- `test_handles_custom_namespace_patterns()`
- `test_handles_invalid_file_paths()`

---

### 14. StrategyFactoryTest.php

**Purpose:** Test strategy factory

**Test Methods:**

- `test_creates_attribute_strategy()`
- `test_creates_directory_strategy()`
- `test_creates_interface_strategy()`
- `test_creates_parent_class_strategy()`
- `test_creates_method_strategy()`
- `test_creates_property_strategy()`
- `test_injects_dependencies_correctly()`

---

### 15. DiscoveryManagerTest.php

**Purpose:** Test main discovery manager

**Test Methods:**

- `test_attribute_method_returns_builder()`
- `test_directories_method_returns_builder()`
- `test_implementing_method_returns_builder()`
- `test_extending_method_returns_builder()`
- `test_methods_method_returns_builder()`
- `test_properties_method_returns_builder()`
- `test_clear_cache_delegates_to_cache_manager()`
- `test_finder_returns_symfony_finder()`

---

### 16. DiscoveryBuilderTest.php

**Purpose:** Test fluent builder

**Test Methods:**

- `test_where_adds_property_filter()`
- `test_filter_adds_callback_filter()`
- `test_instantiable_adds_validator()`
- `test_extends_adds_validator()`
- `test_implements_adds_validator()`
- `test_cached_enables_caching()`
- `test_get_executes_discovery()`
- `test_chaining_methods_returns_self()`

---

## Feature Tests (Integration Testing)

### 1. AttributeDiscoveryTest.php

**Purpose:** End-to-end attribute discovery

**Test Methods:**

- `test_discovers_classes_with_simple_attribute()`
- `test_discovers_classes_with_attribute_properties()`
- `test_filters_by_attribute_property()`
- `test_combines_with_directory_filter()`
- `test_caches_results()`
- `test_handles_no_results()`

---

### 2. DirectoryDiscoveryTest.php

**Purpose:** End-to-end directory discovery

**Test Methods:**

- `test_discovers_classes_in_directory()`
- `test_discovers_with_glob_patterns()`
- `test_discovers_in_multiple_directories()`
- `test_filters_by_interface()`
- `test_filters_by_parent_class()`
- `test_validates_instantiable()`

---

### 3. MethodDiscoveryTest.php

**Purpose:** End-to-end method discovery

**Test Methods:**

- `test_discovers_methods_with_attribute()`
- `test_filters_by_method_attribute_properties()`
- `test_discovers_across_multiple_classes()`
- `test_includes_correct_metadata()`
- `test_handles_static_and_instance_methods()`

---

### 4. PropertyDiscoveryTest.php

**Purpose:** End-to-end property discovery

**Test Methods:**

- `test_discovers_properties_with_attribute()`
- `test_filters_by_property_attribute_properties()`
- `test_discovers_across_multiple_classes()`
- `test_includes_correct_metadata()`
- `test_handles_typed_properties()`

---

### 5. InterfaceDiscoveryTest.php

**Purpose:** End-to-end interface discovery

**Test Methods:**

- `test_discovers_interface_implementations()`
- `test_combines_with_directory_filter()`
- `test_validates_instantiable()`
- `test_handles_multiple_interfaces()`

---

### 6. ParentClassDiscoveryTest.php

**Purpose:** End-to-end parent class discovery

**Test Methods:**

- `test_discovers_class_extensions()`
- `test_combines_with_directory_filter()`
- `test_validates_instantiable()`
- `test_handles_multi_level_inheritance()`

---

### 7. ChainedDiscoveryTest.php ⭐ **MOST IMPORTANT**

**Purpose:** Test complex chaining scenarios

**Test Methods:**

- `test_directories_then_attribute_then_filter()`
- `test_attribute_then_implements_then_instantiable()`
- `test_directories_then_extends_then_where()`
- `test_methods_then_filter_then_cached()`
- `test_properties_then_where_then_cached()`
- `test_complex_chain_with_multiple_filters()`
- `test_chain_with_all_validators()`
- `test_chain_order_independence()`

**Example Scenarios:**

```php
// Scenario 1: Directory → Attribute → Filter
Discovery::directories('packages/*/src/Cards')
    ->where('enabled', true)
    ->where('priority', '>', 5)
    ->cached('cards')
    ->get();

// Scenario 2: Attribute → Interface → Instantiable
Discovery::attribute(AsService::class)
    ->implementing(ServiceInterface::class)
    ->instantiable()
    ->get();

// Scenario 3: Methods → Multiple Filters
Discovery::methods(Route::class)
    ->where('method', 'GET')
    ->where('middleware', 'contains', 'auth')
    ->filter(fn($id, $meta) => str_starts_with($meta['class'], 'App\\'))
    ->cached('routes')
    ->get();

// Scenario 4: Complex Multi-Step
Discovery::directories([
        'packages/*/src/Settings',
        'modules/*/src/Settings',
    ])
    ->extending(Settings::class)
    ->instantiable()
    ->where('enabled', true)
    ->filter(fn($class) => !str_contains($class, 'Test'))
    ->cached('settings')
    ->get();
```

---

### 8. CachingTest.php

**Purpose:** Test caching behavior

**Test Methods:**

- `test_caches_discovery_results()`
- `test_returns_cached_results_on_second_call()`
- `test_cache_key_uniqueness()`
- `test_clear_cache_invalidates_results()`
- `test_cache_respects_config()`
- `test_cache_disabled_in_local_environment()`

---

### 9. MonorepoDiscoveryTest.php

**Purpose:** Test monorepo-specific features

**Test Methods:**

- `test_discovers_across_packages()`
- `test_discovers_across_modules()`
- `test_resolves_package_namespaces()`
- `test_resolves_module_namespaces()`
- `test_handles_custom_monorepo_structure()`

---

### 10. RealWorldScenariosTest.php

**Purpose:** Test real-world use cases

**Test Methods:**

- `test_auto_register_settings_classes()`
- `test_dynamic_route_registration()`
- `test_plugin_system_discovery()`
- `test_health_check_discovery()`
- `test_command_discovery()`
- `test_middleware_discovery()`
- `test_event_listener_discovery()`

---

## Test Fixtures Structure

```
tests/Fixtures/
├── Attributes/
│   ├── TestAttribute.php              # Simple attribute
│   ├── TestRouteAttribute.php         # Route attribute with properties
│   ├── TestValidateAttribute.php      # Validation attribute
│   ├── TestCardAttribute.php          # Card attribute with enabled/priority
│   └── TestServiceAttribute.php       # Service attribute
├── Classes/
│   ├── Controllers/
│   │   ├── TestController.php         # With route attributes
│   │   └── AdminController.php        # With route attributes
│   ├── Services/
│   │   ├── TestService.php            # Implements interface
│   │   ├── AbstractService.php        # Abstract class
│   │   └── ServiceInterface.php       # Interface
│   ├── Commands/
│   │   ├── TestCommand.php            # Extends Command
│   │   └── BaseCommand.php            # Base command class
│   ├── Settings/
│   │   ├── AppSettings.php            # With properties
│   │   └── UserSettings.php           # With properties
│   └── Cards/
│       ├── DashboardCard.php          # With card attribute
│       └── AnalyticsCard.php          # With card attribute
└── Directories/
    ├── Package1/
    │   └── src/
    │       ├── Services/
    │       └── Commands/
    ├── Package2/
    │   └── src/
    │       ├── Settings/
    │       └── Cards/
    └── Module1/
        └── src/
            └── Controllers/
```

---

## Test Execution Plan

### Phase 1: Unit Tests (Isolated)

1. Cache Manager
2. Strategies (all 6)
3. Filters (2)
4. Validators (3)
5. Resolvers
6. Factories
7. Manager & Builder

### Phase 2: Feature Tests (Integration)

1. Individual discovery types (6 tests)
2. Chained discovery (complex scenarios)
3. Caching behavior
4. Monorepo features
5. Real-world scenarios

### Phase 3: Coverage & Quality

1. Achieve >90% code coverage
2. Test edge cases
3. Test error handling
4. Performance benchmarks

---

## Test Naming Conventions

- Unit tests: `test_<what_it_does>()`
- Feature tests: `test_<user_scenario>()`
- Use snake_case for test methods
- Be descriptive and specific

---

## Assertions to Use

- `assertCount()` - For array sizes
- `assertArrayHasKey()` - For metadata structure
- `assertInstanceOf()` - For object types
- `assertEquals()` - For exact matches
- `assertStringContainsString()` - For partial matches
- `assertEmpty()` - For no results
- `assertNotEmpty()` - For has results
- `assertTrue()` / `assertFalse()` - For boolean checks

---

## Total Test Count Estimate

- **Unit Tests:** ~80 test methods
- **Feature Tests:** ~50 test methods
- **Total:** ~130 comprehensive test methods

This ensures thorough coverage of all discovery scenarios!
