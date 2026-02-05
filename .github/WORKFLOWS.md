# GitHub Configuration

This directory contains all GitHub-specific configuration for the Laravel Discovery package.

## 📁 Directory Structure

```
.github/
├── workflows/              # GitHub Actions workflows
│   ├── tests.yml          # Main test suite (multi-OS, multi-PHP, multi-Laravel)
│   ├── code-quality.yml   # Code style, security audit, dependency review
│   ├── release.yml        # Automated releases and Packagist updates
│   ├── compatibility.yml  # Weekly compatibility checks
│   ├── documentation.yml  # Documentation deployment
│   ├── stale.yml         # Stale issue/PR management
│   ├── labeler.yml       # Auto-labeling PRs
│   └── auto-merge.yml    # Auto-merge Dependabot PRs
├── ISSUE_TEMPLATE/        # Issue templates
│   ├── bug_report.yml    # Bug report template
│   └── feature_request.yml # Feature request template
├── PULL_REQUEST_TEMPLATE.md # PR template
├── dependabot.yml        # Dependabot configuration
├── labeler.yml          # Labeler configuration
└── FUNDING.yml          # Funding/sponsorship info
```

## 🔄 Workflows

### tests.yml - Main Test Suite

**Triggers:** Push to main/develop, PRs, weekly schedule

**Matrix:**

- OS: Ubuntu, Windows, macOS
- PHP: 8.3, 8.4
- Laravel: 11._, 12._
- Stability: prefer-lowest, prefer-stable

**Features:**

- Multi-platform testing
- Code coverage with Codecov
- PHPStan level 8 analysis
- Composer caching for faster builds

### code-quality.yml - Code Quality Checks

**Triggers:** Push to main/develop, PRs

**Jobs:**

- Laravel Pint code style check
- Security audit with `composer audit`
- Dependency review for PRs

### release.yml - Automated Releases

**Triggers:** Version tags (v*.*.\*)

**Process:**

1. Run full test suite
2. Run PHPStan analysis
3. Extract changelog for version
4. Create GitHub release
5. Trigger Packagist update

**Required Secrets:**

- `PACKAGIST_USERNAME`
- `PACKAGIST_TOKEN`

### compatibility.yml - Compatibility Testing

**Triggers:** Weekly schedule, manual dispatch

**Purpose:** Test against upcoming PHP/Laravel versions to catch compatibility issues early

### documentation.yml - Documentation Deployment

**Triggers:** Push to main (docs changes)

**Purpose:** Deploy documentation to GitHub Pages

### stale.yml - Stale Management

**Triggers:** Daily schedule

**Configuration:**

- Issues: 60 days stale, 7 days to close
- PRs: 30 days stale, 14 days to close
- Exempt labels: pinned, security, bug, enhancement

### labeler.yml - Auto Labeling

**Triggers:** PR opened/updated

**Labels:**

- `documentation` - Docs changes
- `tests` - Test changes
- `source` - Source code changes
- `ci` - CI/CD changes
- `dependencies` - Dependency updates
- And more...

### auto-merge.yml - Dependabot Auto-merge

**Triggers:** Dependabot PRs

**Behavior:** Auto-merge patch and minor updates after CI passes

## 🐛 Issue Templates

### Bug Report (bug_report.yml)

Structured form for bug reports including:

- Bug description
- Reproduction steps
- Expected vs actual behavior
- Code sample
- Environment details (PHP, Laravel, OS)

### Feature Request (feature_request.yml)

Structured form for feature requests including:

- Problem statement
- Proposed solution
- Alternatives considered
- Example usage
- Breaking change indicator

## 📝 Pull Request Template

Comprehensive PR template covering:

- Description and type of change
- Related issues
- Changes made
- Testing details
- Breaking changes
- Checklist for contributors

## 🤖 Dependabot

**Configuration:**

- Weekly updates on Monday at 9 AM
- Composer dependencies
- GitHub Actions
- Auto-labeling
- Commit message prefixes

## 🏷️ Labels

Auto-applied based on file changes:

- `documentation` - \*.md, docs/\*\*
- `tests` - tests/\*\*, phpunit.xml
- `source` - src/\*\*
- `ci` - .github/\*\*
- `dependencies` - composer.json/lock
- `cache` - src/Cache/\*\*
- `strategies` - src/Strategies/\*\*
- `filters` - src/Filters/\*\*
- `validators` - src/Validators/\*\*

## 🔐 Required Secrets

For full CI/CD functionality, configure these secrets:

### Repository Secrets

```
CODECOV_TOKEN          # Codecov integration
PACKAGIST_USERNAME     # Packagist API username
PACKAGIST_TOKEN        # Packagist API token
```

### Setting Secrets

1. Go to repository Settings
2. Navigate to Secrets and variables → Actions
3. Click "New repository secret"
4. Add each secret

## 📊 Status Badges

Add these to your README:

```markdown
[![Tests](https://github.com/pixielity-co/laravel-discovery/actions/workflows/tests.yml/badge.svg)](https://github.com/pixielity-co/laravel-discovery/actions/workflows/tests.yml)
[![Code Quality](https://github.com/pixielity-co/laravel-discovery/actions/workflows/code-quality.yml/badge.svg)](https://github.com/pixielity-co/laravel-discovery/actions/workflows/code-quality.yml)
[![codecov](https://codecov.io/gh/pixielity/laravel-discovery/branch/main/graph/badge.svg)](https://codecov.io/gh/pixielity/laravel-discovery)
```

## 🚀 Deployment Workflow

### For Contributors

1. Fork repository
2. Create feature branch
3. Make changes
4. Push to fork
5. Create PR
6. CI runs automatically
7. Address review feedback
8. Merge after approval

### For Maintainers

1. Review and approve PR
2. Merge to develop
3. Test in develop
4. Merge to main
5. Create version tag
6. Release workflow runs automatically
7. GitHub release created
8. Packagist updated

## 🔧 Maintenance

### Weekly Tasks (Automated)

- Compatibility testing
- Dependency updates via Dependabot
- Stale issue/PR management

### Release Tasks (Automated)

- Version tagging triggers release
- Changelog extraction
- GitHub release creation
- Packagist notification

### Manual Tasks

- Review and merge PRs
- Triage new issues
- Update documentation
- Plan new features

## 📚 Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Dependabot Documentation](https://docs.github.com/en/code-security/dependabot)
- [Contributing Guide](../CONTRIBUTING.md)
- [Security Policy](../SECURITY.md)

## 🤝 Contributing

See [CONTRIBUTING.md](../CONTRIBUTING.md) for detailed contribution guidelines.

## 📄 License

This configuration is part of the Laravel Discovery package and follows the same MIT license.
