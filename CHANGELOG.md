# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - Deployment & Polish

### Added
- **Release Automation**: GitHub Actions workflow to build PHAR, create GitHub Release, and update Homebrew formula in `alwoodm/homebrew-tap`.
- **Homebrew Support**: Easy installation via `brew tap alwoodm/tap`.
- **User Deletion**: Dedicated `user:delete` command for consistency.
- **Default Command**: Support for `myssh <alias>` shortcut (skips `connect` keyword).
- **Dynamic Security**: Automatic generation of unique `APP_KEY` for PHAR builds, stored in `~/.myssh/.key`.
- **Infrastructure**: Automatic SQLite database creation and migration on first run.
- **Documentation**: Comprehensive `README.md` with installation and usage instructions.

### Fixed
- CI/CD workflows for PHP 8.3 and testing.
- Database path resolution for PHAR vs Development environments.
- Test suite enhancements using `RefreshDatabase` and `Prompt::fake`.

## [0.2.0] - Core Features

### Added
- **Host & User Management**: Full CRUD operations for Hosts and Users.
- **Interactive Commands**: `host:add`, `host:edit`, `host:delete`, `user:add`.
- **Secure Storage**: AES-256-CBC encryption for all stored passwords.
- **Smart Connectivity**:
    - Last login tracking for sorting hosts/users.
    - Automatic connection attempts via `sshpass`.
    - Fallback to clipboard copy (macOS) or manual entry.
- **Fast Arguments**: Direct CLI arguments support (e.g., `myssh connect homelab root`) to skip interactive menus.

### Changed
- Renamed project binary to `myssh`.
- Removed port management (standard SSH port assumed or handled via config).

## [0.1.0] - Initial Setup

### Added
- Initial Laravel Zero project structure.
- SQLite database support with Eloquent ORM.
- Development tools: Duster (linting) and CaptainHook (pre-commit hooks).
- Environment configuration (`.env.example`).
