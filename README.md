# Jira Service Desk

A self-hosted web portal that connects to the Jira Cloud API and simplifies issue tracking for multiple users through a single authenticated account.  
Built with Symfony 7.2, Docker, and FrankenPHP, this project includes project/user management, ticket interaction, Kanban boards, notifications, and more.

![License: MIT](https://img.shields.io/badge/license-MIT-green.svg)
![Symfony 7.2](https://img.shields.io/badge/Symfony-7.2-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.3+-orange.svg)
![Latest Release](https://img.shields.io/github/v/release/spiriitlabs/JiraServiceDesk?color=blue)

---

## ✨ Features

- Login portal with user management
- Multi-project dashboard and statistics
- View, comment, and update Jira issues
- Assignee, priority, type, and status editing
- Attachment preview and download
- Kanban board synced with Jira project configuration
- Notification system (via Jira Webhook)
- Admin panel to manage users, projects, and logs

---

## 🛠 Tech Stack

- **Backend:** Symfony 7.2, PHP 8.3+
- **Frontend:** Webpack Encore (_JS & CSS bundling_)
- **Runtime:** FrankenPHP (_standalone binary_)
- **Dev Environment:** Docker + Make
- **Database:** MariaDB (_or any DB supported by Symfony_)
- **Jira Integration:** [php-JiraCloud-RESTAPI](https://github.com/lesstif/php-JiraCloud-RESTAPI)

---

## 🚀 Getting Started

### ✅ Development Setup

Make sure Docker and npm are installed, then:

```bash
make start
```

The app will be available at: `https://localhost`

### 📦 Production / Binary Setup

Download the latest FrankenPHP binary build from GitHub Releases and follow the binary [setup guide](documentation/deployment.md).

---

## ⚙️ Environment Variables

The following variables must be configured (via .env or your server):
```txt
###> symfony/framework-bundle ###
APP_ENV=prod
APP_SECRET=your-secret-key
###<> symfony/framework-bundle ###

###> symfony/mailer ###
MAILER_DSN=smtp://user:pass@mailserver
FROM_EMAIL=your@email.com
###< symfony/mailer ###

###> doctrine/doctrine-bundle ###
DATABASE_URL=mysql://user:pass@db:3306/dbname
###< doctrine/doctrine-bundle ###

###> hosts ###
ROUTER_REQUEST_CONTEXT_HOST=localhost
ROUTER_REQUEST_CONTEXT_SCHEME=https
###< hosts ###

###> lesstif/php-jira-rest-client ###
JIRAAPI_V3_USER=email@domain.com
JIRAAPI_V3_PERSONAL_ACCESS_TOKEN=your-jira-token
JIRAAPI_V3_HOST=https://your-domain.atlassian.net
###< lesstif/php-jira-rest-client ###

###> spiriitlabs/jira-service-desk ###
JIRA_ACCOUNT_ID=''
DEFAULT_PRIORITY_NAME=''
NOT_AVAILABLE_TYPES_JIRA_ID="[]"
###< spiriitlabs/jira-service-desk ###
```

---

## 🧭 User Flow

1. **Authentication:**
   - Users log in via the web portal.

2. **Dashboard:**
   - After logging in, users are presented with a dashboard that shows an overview of Jira issues and statistics, including:
     - Active projects
     - Assigned tickets
     - Favorite(s)

3. **Viewing & Managing Jira Issues:**
   - Users can view a list of Jira issues within their assigned projects.
   - Each issue displays:
     - Issue title, description, and comments.
     - Attachments (downloadable and previewable).
     - Key fields such as assignee, priority, issue type, and current status.

4. **Commenting & Updating Issues:**
   - Users can add comments to Jira issues.
   - Admins and authorized users can update key fields of an issue, such as:
     - **Assignee**: Change who is responsible for the task.
     - **Priority**: Update the issue’s priority.
     - **Type**: Modify issue type (e.g., Bug, Task, Story).
     - **State**: Change the status (e.g., In Progress, Done).

5. **Kanban Board View:**
   - A Kanban board view is available for project-related Jira tickets.
   - Users can see the issue status and manage issues via drag-and-drop.

6. **Notifications:**
   - Users receive notifications when:
     - New tickets are created.
     - Tickets are updated (comments added, fields changed, etc.).
     - New comment are created.
     - Comment is updated.
   - Notifications are handled through Jira Webhooks, so real-time updates are reflected in the app.

7. **Admin Panel:**
   - Admin users can manage the system, including:
    - Adding/updating/removing users.
    - Adding/removing projects.
    - Assigning users to projects.
    - Viewing and filtering logs.
    - Managing settings and configurations.

---

## 🔔 Webhook Integration

The application supports real-time notifications from Jira using webhooks.  
You can follow the detailed setup instructions in [`documentation/deployment.md`](documentation/deployment.md) to integrate the webhook functionality and start receiving updates from Jira.

---

## 🎨 Custom Branding

You can personalize the application’s branding by customizing the project name and logos.

### Project Name
- Set the `PROJECT_NAME` environment variable to change the displayed name throughout the app.

### Custom Logos
- Use the `LOGOS_PATH` environment variable to define paths for your custom logos (light, dark, and icon variants).
- Place your SVG files in the `/assets/images/logo/custom/` directory. For example:
  - `/assets/images/logo/custom/my_custom_logo-light.svg`
  - `/assets/images/logo/custom/my_custom_logo-dark.svg`
  - `/assets/images/logo/custom/my_custom_icon.svg`

#### Example `LOGOS_PATH` value
In your `.env` file (escape quotes as needed):
```
LOGOS_PATH="{\"light\":\"assets/images/logo/custom/my_custom_logo-light.svg\",\"dark\":\"assets/images/logo/custom/my_custom_logo-dark.svg\",\"icon\":\"assets/images/logo/custom/my_custom_icon.svg\"}"
```
- The paths should be relative to the `assets/` directory (e.g., `assets/images/logo/custom/...`).
- The application will automatically use these paths for branding in the UI.

---

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## 🙌 Credits & Acknowledgements

- [Symfony](https://symfony.com/) — MIT License  
- [FrankenPHP](https://github.com/dunglas/frankenphp) — MIT License  
- [php-JiraCloud-RESTAPI](https://github.com/lesstif/php-JiraCloud-RESTAPI) — Apache 2.0 License  
