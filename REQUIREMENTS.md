# System Requirements

This application relies on system tools to handle SSH connections securely and interactively.

## Recommended Tools

### 1. sshpass (Primary)
Used to securely pass the password to the SSH client without user interaction.
- **macOS:** `brew install hudochenkov/sshpass/sshpass`
- **Linux:** `apt-get install sshpass`

### 2. Clipboard Support (Fallback)
If `sshpass` is not available, the application attempts to copy the password to your clipboard so you can paste it.
- **macOS:** Uses `pbcopy` (Pre-installed).
- **Linux:** Uses `xclip` or `xsel` (Not yet implemented, falls back to manual).

## Fallback Behavior
If no tools are found, the application will default to the standard `ssh` command, which will prompt you to type the password manually.
