# Workflow Failures Summary

**Date**: February 6, 2026  
**Commit**: d8ad777 - "fix: complete PHPStan workflow configuration"  
**Repository**: pixielity-co/laravel-discovery

## Overview

After pushing the workflow configuration fix, two workflows were triggered:
1. **Code Quality** - Failed ❌
2. **Tests** - Multiple failures across different PHP/Laravel versions ❌

---

## 1. Code Quality Workflow Failures

### Run ID: 21735195447

#### ❌ Code Style (Pint) - FAILED
**Duration**: 20s  
**Issue**: Style violation in `src/Strategies/DirectoryStrategy.php`

**Error Details**:
```
FAIL   74 files, 1 style issue
⨯ src/Strategies/DirectoryStrategy.php function_declaration, not_operator_with_successor_space
```

**Root Cause**: Missing space after `!` operator or function declaration formatting issue.

**Resolution**: ✅ Fixed locally with `vendor/bin/pint src/Strategies/DirectoryStrategy.php`

#### ✅ Security Audit - PASSED
**Duration**: 17s  
No security vulnerabilities found.

#### ⚠️ Dependency Review - SKIPPED
**Duration**: 0s  
Only runs on pull requests.

---

## 2. Tests Workflow Failures

### Run ID: 21735195443

The test workflow runs a matrix of 12 combinations:
- **OS**: ubuntu-latest, windows-latest, macos-latest
- **PHP**: 8.5
- **Laravel**: 11.*, 12.*
- **Stability**: prefer-lowest, prefer-stable

### Critical Issue: Syntax Error in DirectoryStrategy.php

**Affected Configurations** (PHP 8.3 only):
- ❌ P8.3 - L11.* - prefer-lowest - macos-latest
- ❌ P8.3 - L11.* - prefer-stable - macos-latest
- ❌ P8.3 - L12.* - prefer-lowest - macos-latest
- ❌ P8.3 - L12.* - prefer-stable - macos-latest

**Error**:
```
An error occurred inside PHPUnit.
Message: syntax error, unexpected token "->"
Location: /src/Strategies/DirectoryStrategy.php:133
Exit code: 255
```

**Note**: The workflow is configured to test PHP 8.5, but some runs show PHP 8.3/8.4. This suggests the matrix configuration may have been changed after these runs started.

### Test Failures (PHP 8.4 configurations)

**Affected Configurations**:
- ❌ P8.4 - L11.* - prefer-stable - macos-latest (24 failures)
- ❌ P8.4 - L11.* - prefer-lowest - macos-latest (24 failures)
- ❌ P8.4 - L12.* - prefer-stable - macos-latest (24 failures)
- ❌ P8.4 - L12.* - prefer-lowest - macos-latest (24 failures)

**Common Test Failures** (24 total):

1. **DiscoveryBuilderTest::test_get_executes_discovery**
   - Failed asserting that an object is not empty
   - Location: `tests/Unit/DiscoveryBuilderTest.php:248`

2. **NamespaceResolverTest::test_resolves_namespace_from_file_path**
   - Failed asserting that 'Tests\Fixtures\Classes\Cards\DashboardCard' contains "Pixielity"
   - Location: `tests/Unit/Resolvers/NamespaceResolverTest.php:80`

3. **NamespaceResolverTest::test_handles_monorepo_packages**
   - Similar namespace resolution issue

**Additional Issues**:
- PHP Deprecation warnings from GuzzleHttp\Promise (nullable parameter declarations)
- PHP Deprecation warnings from Symfony\Translation (nullable parameter declarations)

---

## 3. Workflow Configuration Issues

### Problem: PHP Version Mismatch

The `tests.yml` workflow is configured to test **PHP 8.5** only:
```yaml
matrix:
  php: [8.5]
```

However, the actual runs show PHP 8.3 and 8.4 being tested, suggesting:
1. The configuration was recently changed
2. Old workflow runs were still in progress
3. There may be caching issues

### Problem: Incomplete PHPStan Job

The PHPStan job in `tests.yml` was incomplete (missing steps), which was fixed in commit d8ad777.

---

## 4. Root Causes Analysis

### Code Style Issue
- **File**: `src/Strategies/DirectoryStrategy.php`
- **Issue**: Formatting violation (spacing around operators/function declarations)
- **Impact**: Blocks merge if Pint check is required
- **Status**: ✅ Fixed locally, needs to be committed and pushed

### Syntax Error (PHP 8.3)
- **File**: `src/Strategies/DirectoryStrategy.php:133`
- **Issue**: Syntax error with `->` token
- **Impact**: Complete test failure on PHP 8.3
- **Possible Cause**: PHP 8.4+ syntax used that's incompatible with 8.3
- **Status**: ⚠️ Needs investigation (workflow now targets PHP 8.5 only)

### Test Failures (PHP 8.4)
- **Primary Issue**: Namespace resolution problems
- **Tests Affected**: 24 tests across multiple test classes
- **Pattern**: Tests expect "Pixielity" namespace but get "Tests\Fixtures" namespace
- **Impact**: Indicates namespace resolver logic may not handle test fixtures correctly
- **Status**: ⚠️ Needs investigation

---

## 5. Recommended Actions

### Immediate Actions

1. **Fix and Push Code Style Issue**
   ```bash
   cd packages/Discovery
   vendor/bin/pint src/Strategies/DirectoryStrategy.php
   git add src/Strategies/DirectoryStrategy.php
   git commit -m "style: fix Pint violations in DirectoryStrategy"
   git push
   ```

2. **Verify PHP 8.5 Compatibility**
   - Ensure all code is compatible with PHP 8.5
   - Check if DirectoryStrategy.php uses PHP 8.5-specific syntax
   - Consider testing locally with PHP 8.5

3. **Investigate Test Failures**
   - Run failing tests locally: `vendor/bin/phpunit --no-coverage --filter="test_get_executes_discovery"`
   - Check namespace resolver logic for test fixture handling
   - Verify test expectations match actual behavior

### Configuration Improvements

1. **Clarify PHP Version Strategy**
   - Decide if testing only PHP 8.5 is intentional
   - Consider testing multiple PHP versions (8.3, 8.4, 8.5) for broader compatibility
   - Update documentation to reflect supported PHP versions

2. **Optimize Workflow Matrix**
   - Current matrix: 12 combinations (3 OS × 2 Laravel × 2 stability)
   - Consider reducing OS coverage if not needed (e.g., Ubuntu only for most tests)
   - Add PHP version to matrix if broader compatibility is desired

3. **Add Pre-commit Hooks**
   - Run Pint automatically before commits
   - Catch style issues before they reach CI

---

## 6. Workflow Architecture

### Current Setup (Good Separation)

**Code Quality Workflow** (`code-quality.yml`):
- ✅ Fast feedback (Pint, Security Audit)
- ✅ Runs on every push/PR
- ✅ Separate from slow test matrix

**Tests Workflow** (`tests.yml`):
- ✅ Comprehensive testing across matrix
- ✅ Includes PHPStan static analysis
- ✅ Coverage reporting to Codecov

This separation is intentional and beneficial - don't consolidate them.

---

## 7. Next Steps

1. ✅ Fix Pint style issue (completed locally)
2. ⬜ Commit and push style fix
3. ⬜ Monitor new workflow runs
4. ⬜ Investigate and fix 24 test failures
5. ⬜ Verify PHP 8.5 compatibility
6. ⬜ Update documentation with supported versions
7. ⬜ Consider adding pre-commit hooks

---

## Appendix: Test Execution Summary

### Local Test Results

**Single Test Execution** (PHP 8.5.2):
```bash
vendor/bin/phpunit --no-coverage --filter="test_get_executes_discovery"
```
**Result**: ✅ PASSED (1 test, 2 assertions, 27 PHPUnit deprecations)

This suggests the test failures may be specific to:
- PHP 8.4 environment
- Specific Laravel versions
- CI environment differences
- Dependency version differences (prefer-lowest vs prefer-stable)
