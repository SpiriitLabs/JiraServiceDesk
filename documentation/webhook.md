# Webhook Documentation

## Creating a Jira Webhook

To configure the webhook in the Jira interface, go to:  
`https://<YOUR_SPACE>.atlassian.net/plugins/servlet/webhooks`

For the URL, use:  
`https://<YOUR_URL>/webhook/jira`

Generate a secret token and place it in the `JIRA_WEBHOOK_SECRET` variable inside the `.env.local` file.

### Ticket Filtering

In the ticket filtering section, use the following JQL query to only trigger the webhook for relevant issues: `labels in user label (ex :'from-client')`

### Event Subscription

Select the following events to be monitored and processed by the Service Desk webhook:

- **Issue → Created**
- **Issue → Updated**
- **Issue → Deleted**
- **Comment → Created**
- **Comment → Updated**

Only these events will be handled by the Service Desk webhook.

## Webhook Management

The webhooks are managed by the "webhook" component of Symfony (https://symfony.com/doc/current/webhook.html).

The secret generated in Jira must be placed in the `JIRA_WEBHOOK_SECRET` environment variable in the `.env.local` file.

The webhook must be in JSON format and use the POST method. If the webhook or you did not set the secret and the secret does not match the webhook, it will be declined.

Only the events that you should have activated above will be authorized by the webhook and processed accordingly. These events send emails to the people concerned and who have activated notifications for these events.
