# GiveWP Release Process

This document outlines the complete process for releasing new versions of GiveWP.

## Table of Contents

- [Pre-Release Preparation](#pre-release-preparation)
- [Version Update Checklist](#version-update-checklist)
- [Testing & Quality Assurance](#testing--quality-assurance)
- [Release Execution](#release-execution)
- [Post-Release Verification](#post-release-verification)
- [Release Types](#release-types)

## Pre-Release Preparation

### 1. Release Planning
- [ ] Determine release type (major, minor, patch)
- [ ] Review all merged PRs since last release
- [ ] Ensure all intended features/fixes are merged
- [ ] Verify no critical issues are pending
- [ ] Coordinate with team on release timing

### 2. Code Freeze
- [ ] Announce code freeze to development team
- [ ] Create release branch if needed (`release/x.x.x`)
- [ ] Ensure all CI/CD checks are passing

### 3. Documentation Review
- [ ] Review and update user-facing documentation
- [ ] Verify developer documentation is current
- [ ] Check that all new features have proper documentation

## Version Update Checklist

### 1. Update Version Numbers
- [ ] Update `* Version:` in the plugin header (`give.php`)
- [ ] Update main plugin constant version (`GIVE_VERSION` in `give.php`)
- [ ] Update `Stable tag:` in `readme.txt`

### 2. Update Dependencies (if applicable)
- [ ] Update minimum GiveWP version for add-ons (`GIVE_RECURRING_MIN_GIVE_VERSION`, etc.)
- [ ] Update `Requires Give:` in `readme.txt` for add-ons

### 3. Update Plugin Requirements (optional)
**Main plugin file (`give.php`):**
- [ ] `Requires at least:` (WordPress version)
- [ ] `Requires PHP:` (PHP version)

**Readme file (`readme.txt`):**
- [ ] `Requires at least:` (WordPress version)
- [ ] `Tested up to:` (WordPress version)
- [ ] `Requires PHP:` (PHP version)
- [ ] `= Minimum Requirements =` section

### 4. Code Documentation Updates
- [ ] Find and replace all `@unreleased` tags with `@since {version}`
  ```bash
  # Example command to find @unreleased tags
  grep -r "@unreleased" src/ includes/ --include="*.php"

  # Replace with appropriate @since version
  find . -name "*.php" -exec sed -i 's/@unreleased/@since 4.3.2/g' {} \;
  ```

### 5. Changelog Updates
- [ ] Add new version entry to `readme.txt` changelog
- [ ] Update `CHANGELOG.md` if maintained separately
- [ ] Follow consistent changelog format:

```markdown
= 4.3.2: June 3rd, 2025 =
* New: Added new functionality that does...
* Enhancement: Updated existing feature for...
* Change: Existing functionality is now...
* Security: Added additional security measures for...
* Fix: Resolved an issue where...
```

## Testing & Quality Assurance

### 1. Automated Testing
- [ ] Run full PHPUnit test suite: `composer test`
- [ ] Run PHPStan static analysis: `composer phpstan`
- [ ] Verify all GitHub Actions/CI checks pass
- [ ] Run WordPress coding standards: `composer phpcs`

### 2. Manual Testing
- [ ] Test core donation functionality
- [ ] Verify payment gateways (Stripe, PayPal, etc.)
- [ ] Test form builder functionality
- [ ] Verify admin dashboard features
- [ ] Test donor dashboard functionality
- [ ] Check email notifications
- [ ] Verify reporting features
- [ ] Test with different WordPress versions
- [ ] Test with different PHP versions (if applicable)

### 3. Compatibility Testing
- [ ] Test with popular themes
- [ ] Test with common plugins
- [ ] Verify multisite compatibility
- [ ] Test database migrations (if any)

### 4. Performance Testing
- [ ] Check for memory leaks
- [ ] Verify query performance
- [ ] Test with large datasets
- [ ] Check frontend load times

## Release Execution

### 1. Final Pre-Release Checks
- [ ] Verify all version numbers are correct
- [ ] Validate `readme.txt` using [WordPress Validator](https://wordpress.org/plugins/developers/readme-validator/)
- [ ] Preview `readme.txt` using [WPReadme.com](https://wpreadme.com/)
- [ ] Ensure changelog is complete and accurate
- [ ] Verify no debug code or console.log statements remain

### 2. Build Process
- [ ] Run production build: `npm run build`
- [ ] Verify built assets are included
- [ ] Check that `.distignore` excludes development files
- [ ] Create release package/zip if needed

### 3. Version Control
- [ ] Commit all version updates
- [ ] Create and push version tag: `git tag v4.3.2 && git push origin v4.3.2`
- [ ] Merge release branch to main (if using release branches)

### 4. WordPress.org Release
- [ ] Draft new release using [version] as tag & title, making sure target branch is set to Master. Double check everything, then generate release notes & publish release.

## Post-Release Verification

### 1. Immediate Verification (within 1 hour)
- [ ] Verify plugin is available on WordPress.org
- [ ] Test installation from WordPress.org
- [ ] Check automatic updates work correctly
- [ ] Monitor for immediate bug reports

### 2. Short-term Monitoring (24-48 hours)
- [ ] Monitor support forums for issues
- [ ] Check error tracking services for new errors
- [ ] Review download statistics
- [ ] Monitor social media/community feedback
- [ ] Watch for compatibility reports

## Release Types

### Major Release (x.0.0)
- Breaking changes or significant new features
- Requires extensive testing and documentation updates
- May require user migration guides
- Consider beta/RC releases

### Minor Release (x.y.0)
- New features and enhancements
- Backward compatible
- Standard testing procedures
- Update feature documentation

### Patch Release (x.y.z)
- Bug fixes and security updates
- No new features
- Expedited testing for critical fixes
- Focus on regression testing

## Automation Tools

### Available Scripts
```bash
# Run all tests
composer test

# Check coding standards
composer phpcs

# Fix coding standards
composer phpcbf

# Run static analysis
composer phpstan

# Build assets
npm run build

# Development build with watch
npm run dev
```

### GitHub Actions
- Automated testing on PR and push
- Code quality checks
- WordPress compatibility matrix testing
- Security scanning

## Team Responsibilities

### Release Manager
- Coordinates release timeline
- Performs version updates
- Manages release communication
- Oversees testing process

### Development Team
- Code review and approval
- Feature completion verification
- Documentation updates
- Technical testing

### QA Team
- Manual testing execution
- Compatibility verification
- User acceptance testing
- Bug reproduction and verification

### Support Team
- Release communication preparation
- Support documentation updates
- Community notification
- Post-release monitoring

---

## Quick Checklist Summary

For a quick release checklist, follow these essential steps:

1. **Update versions** in `give.php` and `readme.txt`
2. **Replace @unreleased** tags with `@since {version}`
3. **Add changelog** entry to `readme.txt`
4. **Run tests** and ensure they pass
5. **Validate readme.txt** using WordPress validator
6. **Create git tag** and push to repository
7. **Release to WordPress.org**
8. **Monitor** for issues post-release

---

*Last updated: June 10, 2025*
*For questions about the release process, contact the development team.*
