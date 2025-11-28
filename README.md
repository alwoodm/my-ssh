# My-SSH ![Status](https://img.shields.io/badge/Status-In%20Development-yellow?style=for-the-badge)

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel Zero](https://img.shields.io/badge/Laravel%20Zero-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![SQLite](https://img.shields.io/badge/sqlite-%2307405e.svg?style=for-the-badge&logo=sqlite&logoColor=white)

**My-SSH** is a powerful, secure, and user-friendly CLI tool for managing SSH connections. Built with Laravel Zero, it simplifies your workflow by organizing hosts, users, and connection details in one place.

## Features

-   **🚀 Fast & Intuitive**: Connect to your servers with a single command.
-   **🔒 Secure**: All sensitive data (passwords) is encrypted using AES-256-CBC with a unique key per installation.
-   **📂 Organized**: Manage multiple hosts and users effortlessly.
-   **⚡️ Quick Actions**: fast arguments support for rapid management.

## Installation

### Homebrew (Recommended)

You can easily install My-SSH using [Homebrew](https://brew.sh).

```bash
brew tap alwoodm/tap
brew install myssh
```

### Manual Installation

Alternatively, you can download the latest binary from the [Releases](https://github.com/alwood/my-ssh/releases) page.

```bash
# Move the binary to your path
mv myssh /usr/local/bin/myssh
chmod +x /usr/local/bin/myssh
```

## Usage

### Managing Hosts

Add a new host to your inventory:

```bash
myssh host:add
```

List all available hosts:

```bash
myssh host:list
```

Edit an existing host:

```bash
myssh host:edit <alias>
```

Remove a host:

```bash
myssh host:delete <alias>
```

### Managing Users

Add a user to a specific host:

```bash
myssh user:add <host_alias>
```

Remove a user from a host:

```bash
myssh user:delete <host_alias> <username>
```

### Connecting

Connect to a host using its alias. If multiple users are defined, you will be prompted to select one.

```bash
myssh connect <alias>
# or simply
myssh <alias>
```

### Other Commands

-   `myssh list`: Show all available commands.

## Security

Your data is stored locally in `~/.myssh/database.sqlite`.
A unique encryption key is generated on first run and stored in `~/.myssh/.key`.
**Keep this key safe!** Without it, your encrypted passwords cannot be recovered.

## Development

If you want to contribute or run the project locally, feel free to fork this repository or create a new branch.

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/alwoodm/my-ssh.git
    cd my-ssh
    ```

2.  **Install dependencies**:
    ```bash
    composer install
    ```

3.  **Setup environment**:
    ```bash
    cp .env.example .env
    touch database/database.sqlite
    php myssh key:generate
    ```

4.  **Run the application**:
    ```bash
    php myssh list
    ```

## License

My-SSH is open-sourced software licensed under the [MIT license](LICENSE).
