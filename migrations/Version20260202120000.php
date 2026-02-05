<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260202120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Multi-channel notifications: convert boolean preference columns to JSON arrays, add Slack credentials';
    }

    public function up(Schema $schema): void
    {
        // Step 1: Add new Slack credential columns
        $this->addSql('ALTER TABLE user ADD slack_bot_token VARCHAR(255) DEFAULT NULL, ADD slack_member_id VARCHAR(255) DEFAULT NULL');

        // Step 2: Add temporary JSON columns
        $this->addSql('ALTER TABLE user ADD preference_notification_issue_created_json JSON DEFAULT NULL, ADD preference_notification_issue_updated_json JSON DEFAULT NULL, ADD preference_notification_comment_created_json JSON DEFAULT NULL, ADD preference_notification_comment_updated_json JSON DEFAULT NULL, ADD preference_notification_comment_only_on_tag_json JSON DEFAULT NULL');

        // Step 3: Migrate data - preserve existing preferences
        // IN_APP was always active (handler always persisted entity), so in_app is set for all users.
        // EMAIL is set only when master toggle AND per-event toggle were both true.

        $this->addSql('UPDATE user SET preference_notification_issue_created_json = CASE WHEN preference_notification = 1 AND preference_notification_issue_created = 1 THEN \'["in_app","email"]\' ELSE \'["in_app"]\' END');

        $this->addSql('UPDATE user SET preference_notification_issue_updated_json = CASE WHEN preference_notification = 1 AND preference_notification_issue_updated = 1 THEN \'["in_app","email"]\' ELSE \'["in_app"]\' END');

        $this->addSql('UPDATE user SET preference_notification_comment_created_json = CASE WHEN preference_notification = 1 AND preference_notification_comment_created = 1 THEN \'["in_app","email"]\' ELSE \'["in_app"]\' END');

        $this->addSql('UPDATE user SET preference_notification_comment_updated_json = CASE WHEN preference_notification = 1 AND preference_notification_comment_updated = 1 THEN \'["in_app","email"]\' ELSE \'["in_app"]\' END');

        $this->addSql('UPDATE user SET preference_notification_comment_only_on_tag_json = CASE WHEN preference_notification = 1 AND preference_notification_comment_only_on_tag = 1 THEN \'["in_app","email"]\' ELSE \'[]\' END');

        // Step 4: Drop old boolean columns
        $this->addSql('ALTER TABLE user DROP preference_notification, DROP preference_notification_issue_created, DROP preference_notification_issue_updated, DROP preference_notification_comment_created, DROP preference_notification_comment_updated, DROP preference_notification_comment_only_on_tag');

        // Step 5: Rename JSON columns to final names
        $this->addSql('ALTER TABLE user CHANGE preference_notification_issue_created_json preference_notification_issue_created JSON NOT NULL, CHANGE preference_notification_issue_updated_json preference_notification_issue_updated JSON NOT NULL, CHANGE preference_notification_comment_created_json preference_notification_comment_created JSON NOT NULL, CHANGE preference_notification_comment_updated_json preference_notification_comment_updated JSON NOT NULL, CHANGE preference_notification_comment_only_on_tag_json preference_notification_comment_only_on_tag JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Step 1: Add old boolean columns back
        $this->addSql('ALTER TABLE user ADD preference_notification_bool TINYINT(1) NOT NULL DEFAULT 0, ADD preference_notification_issue_created_bool TINYINT(1) NOT NULL DEFAULT 0, ADD preference_notification_issue_updated_bool TINYINT(1) NOT NULL DEFAULT 0, ADD preference_notification_comment_created_bool TINYINT(1) NOT NULL DEFAULT 0, ADD preference_notification_comment_updated_bool TINYINT(1) NOT NULL DEFAULT 0, ADD preference_notification_comment_only_on_tag_bool TINYINT(1) NOT NULL DEFAULT 0');

        // Step 2: Migrate data back - if JSON contains "email", set both master and event to true
        $this->addSql('UPDATE user SET preference_notification_issue_created_bool = CASE WHEN JSON_CONTAINS(preference_notification_issue_created, \'"email"\') THEN 1 ELSE 0 END');
        $this->addSql('UPDATE user SET preference_notification_issue_updated_bool = CASE WHEN JSON_CONTAINS(preference_notification_issue_updated, \'"email"\') THEN 1 ELSE 0 END');
        $this->addSql('UPDATE user SET preference_notification_comment_created_bool = CASE WHEN JSON_CONTAINS(preference_notification_comment_created, \'"email"\') THEN 1 ELSE 0 END');
        $this->addSql('UPDATE user SET preference_notification_comment_updated_bool = CASE WHEN JSON_CONTAINS(preference_notification_comment_updated, \'"email"\') THEN 1 ELSE 0 END');
        $this->addSql('UPDATE user SET preference_notification_comment_only_on_tag_bool = CASE WHEN JSON_CONTAINS(preference_notification_comment_only_on_tag, \'"email"\') THEN 1 ELSE 0 END');

        // Set master toggle to true if any event has email
        $this->addSql('UPDATE user SET preference_notification_bool = CASE WHEN JSON_CONTAINS(preference_notification_issue_created, \'"email"\') OR JSON_CONTAINS(preference_notification_issue_updated, \'"email"\') OR JSON_CONTAINS(preference_notification_comment_created, \'"email"\') OR JSON_CONTAINS(preference_notification_comment_updated, \'"email"\') OR JSON_CONTAINS(preference_notification_comment_only_on_tag, \'"email"\') THEN 1 ELSE 0 END');

        // Step 3: Drop JSON columns
        $this->addSql('ALTER TABLE user DROP preference_notification_issue_created, DROP preference_notification_issue_updated, DROP preference_notification_comment_created, DROP preference_notification_comment_updated, DROP preference_notification_comment_only_on_tag');

        // Step 4: Rename boolean columns to original names
        $this->addSql('ALTER TABLE user CHANGE preference_notification_bool preference_notification TINYINT(1) NOT NULL, CHANGE preference_notification_issue_created_bool preference_notification_issue_created TINYINT(1) NOT NULL, CHANGE preference_notification_issue_updated_bool preference_notification_issue_updated TINYINT(1) NOT NULL, CHANGE preference_notification_comment_created_bool preference_notification_comment_created TINYINT(1) NOT NULL, CHANGE preference_notification_comment_updated_bool preference_notification_comment_updated TINYINT(1) NOT NULL, CHANGE preference_notification_comment_only_on_tag_bool preference_notification_comment_only_on_tag TINYINT(1) NOT NULL');

        // Step 5: Drop Slack columns
        $this->addSql('ALTER TABLE user DROP slack_bot_token, DROP slack_member_id');
    }
}
