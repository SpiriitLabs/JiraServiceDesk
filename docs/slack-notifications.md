# Slack Notifications

## Overview

Jira Service Desk supports multi-channel notifications. Each event type (issue created, issue updated, comment created, comment updated, mentioned in comment) can be configured per-user with any combination of three channels:

- **In the product** - Persists a notification in the in-app notification bell
- **Email** - Sends a templated email
- **Slack** - Sends a direct message via Slack Bot API with rich Block Kit formatting

Notifications are deduplicated within a 300-second window per link/user combination.

## Setting up a Slack Bot

1. Go to [https://api.slack.com/apps](https://api.slack.com/apps) and click **Create New App** > **From scratch**
2. Name the app (e.g., "Jira Service Desk Notifications") and select the target Slack workspace
3. Navigate to **OAuth & Permissions**
4. Under **Bot Token Scopes**, add:
   - `chat:write` - Send messages as the bot
   - `im:write` - Open DM channels with users
5. Click **Install to Workspace** and authorize the app
6. Copy the **Bot User OAuth Token** (starts with `xoxb-`) — this is the `slackBotToken`

## Getting the Slack Member ID

1. Open Slack
2. Click on the target user's profile
3. Click the **More** button (three dots)
4. Select **Copy member ID**
5. The ID format is `U0XXXXXXX` — this is the `slackMemberId`

## Admin Configuration

1. Go to **Admin > Users > Edit user**
2. In the **Slack Integration** section at the bottom of the form, fill in:
   - **Slack Bot Token** — the `xoxb-...` token from step 6 above
   - **Slack Member ID** — the `U0XXXXXXX` ID from the previous section
3. Click **Save**
4. Edit the user again — the **Slack** column now appears in the notification settings table
5. Check the **Slack** checkbox for the desired event types

## Message Format

Slack messages use Block Kit formatting:

- **Header** — Emoji + notification subject (e.g., `:new: New issue in Project X`)
- **Body section** — Notification body text (truncated to 500 characters)
- **Context block** — Extra metadata (e.g., comment author name) when applicable
- **Divider**
- **Action button** — "View in Jira Service Desk" linking to the relevant issue

## Troubleshooting

- **Bot must be in the workspace** where the target user exists
- **Required scopes**: `chat:write` and `im:write`
- **First message**: The bot must have sent at least one DM to the user, or the user must have opened a DM with the bot first (Slack API requirement for `chat.postMessage` to a user)
- **Check application logs** for Slack API errors — errors are logged with the `SlackNotificationService` logger
- **Token format**: Bot tokens start with `xoxb-`. User tokens (`xoxp-`) will not work
- **Member ID format**: Must be a user ID (starts with `U`), not a channel ID or display name
