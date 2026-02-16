# Release Management Guide

## Quick Start

Instead of manually editing changelog.md and running node changelogView.js, you now have automated release management!

## Simple Usage

```bash
npm run release
```

That's it! This single command will:

1. ✅ Analyze your commits since the last release
2. ✅ Determine the version bump (patch by default)
3. ✅ Update changelog.md with grouped changes
4. ✅ Update package.json version
5. ✅ Generate changelog.html automatically
6. ✅ Create a git commit and tag
7. ✅ Push to GitHub with tags automatically
8. ✅ Done! Your release is live!

## Commit Message Format

For best results, use conventional commit messages:

```bash
git commit -m "feat: add new filter indicator"
git commit -m "fix: resolve product card alignment"
git commit -m "docs: update README"
git commit -m "style: improve button colors"
git commit -m "refactor: reorganize filter functions"
git commit -m "perf: optimize image loading"
```

### Commit Types

- `feat:` - ✨ New Features
- `fix:` - 🐛 Bug Fixes
- `docs:` - 📚 Documentation
- `style:` - 🎨 Styling
- `refactor:` - ♻️ Code Refactoring
- `perf:` - ⚡ Performance Improvements
- `test:` - 🧪 Tests
- `build:` - 🔧 Build System
- `chore:` - 🔧 Maintenance

## Specific Version Bumps

```bash
# Patch release (2.4.0 → 2.4.1) - bug fixes
npm run release:patch

# Minor release (2.4.0 → 2.5.0) - new features
npm run release:minor

# Major release (2.4.0 → 3.0.0) - breaking changes
npm run release:major
```

## What Happens During Release?

The release automatically:

1. Bumps the version
2. Updates changelog.md and generates changelog.html
3. Creates a git commit and tag
4. **Pushes to GitHub with tags** (all done for you!)

## First Time with Existing Commits?

If you want to generate a release from recent commits:

```bash
npm run release -- --first-release
```

This will create the first tag without bumping the version.

## Dry Run (Preview Changes)

Want to see what would happen without making changes?

```bash
npx standard-version --dry-run
```

## What Gets Updated?

- ✅ `package.json` - version number
- ✅ `package-lock.json` - version number
- ✅ `changelog.md` - prepends new version section
- ✅ `changelog.html` - automatically generated
- ✅ Git tag - creates version tag (v2.5.0)

## Existing Changelog Content

Your existing changelog entries are **preserved**! New releases are prepended to the top of changelog.md.

## Examples

### Typical Workflow

```bash
# Make some changes
git add .
git commit -m "feat: add Company Casuals image scraper"
git commit -m "fix: product card layout issues"

# Create release
npm run release

# Push to remote
git push --follow-tags origin main
```

### Result

Creates changelog entry like:

```markdown
## Version 2.5.0 - (2026-02-16)

### ✨ New Features
- add Company Casuals image scraper

### 🐛 Bug Fixes
- product card layout issues
```

## Troubleshooting

### If you accidentally run release

```bash
# Undo the last commit (keeps your changes)
git reset --soft HEAD~1

# Delete the tag
git tag -d v2.5.0
```

### If changelog looks wrong

You can still manually edit `changelog.md` and regenerate HTML:

```bash
npm run changelog:html
```

## Pro Tips

1. Make commits regularly with conventional commit messages
2. Run `npm run release` when you're ready to create a version
3. Review the changes before pushing
4. Use `--dry-run` to preview if unsure
5. Your existing changelog sections remain intact

## Need Help?

Run dry-run to see what would happen:

```bash
npx standard-version --dry-run
```

Check the version:

```bash
cat package.json | grep version
```

View recent tags:

```bash
git tag -l | tail -5
```
