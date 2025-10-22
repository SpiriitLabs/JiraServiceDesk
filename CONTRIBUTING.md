# Contributing to Jira Service Desk by Spiriit

First of all, thank you for taking the time to contribute! 🚀  
Your ideas, code, and feedback help improve the project and are very much appreciated.

## Ways to Contribute

There are many ways you can help:

- 🐛 Report bugs
- 💡 Suggest new features or enhancements
- 🛠️ Submit code improvements or fixes
- 📚 Improve documentation
- 🧪 Write tests or help improve test coverage

## Getting Started

### 1. Fork and Clone

Click “Fork” and then clone your fork:

```bash
git clone https://github.com/your-username/JiraServiceDesk.git
cd JiraServiceDesk
```

### 2. Setup Development Environment

You can use Docker and Make for local development:

```sh
make start
```

Ensure you have the following installed:
- Docker & Docker Compose
- Make
- Node.js and npm (for asset building via Webpack Encore)

### 3. Create a Branch

Use a descriptive name:

```sh
git checkout -b fix/webhook-error
```

### 4. Make Your Changes

Respect the Symfony code style, and test before committing.

### 5. Commit Your Changes

```sh
git commit -m "Fix: handle error on webhook delivery"
```

### 6. Push and Open a Pull Request

```sh
git push origin fix/webhook-error
```

Then go to the GitHub page of your fork and open a pull request against the main branch.

## Code Style

- Follow Symfony Coding Standards
- Use phpcs or PHP-CS-Fixer if possible
- Keep commits small and meaningful

## Tests

If you add or update functionality, please consider writing or updating automated tests.
This helps keep the project stable for all users.

## Reporting Issues

If you encounter a bug, please open a GitHub issue and include:

- Reproduction steps
- Symfony and PHP version
- Stack trace or logs if applicable

## Contributor Conduct

This project is under a [Code of Conduct](CODE_OF_CONDUCT.md). Please review it to help us maintain a respectful and inclusive community.

## Questions?

You can reach out directly at 📫 [hello@spiriit.com](mailto:hello@spiriit.com)

Thank you for your support and contributions! 💙