<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806070252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD preferences_email_notification_global TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_email_notification_issue_created TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_email_notification_issue_updated TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_email_notification_comment_created TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_email_notification_comment_updated TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_email_notification_comment_only_on_tag TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_push_notification_global TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_push_notification_issue_created TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_push_notification_issue_updated TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_push_notification_comment_created TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_push_notification_comment_updated TINYINT(1) NOT NULL DEFAULT 1, ADD preferences_push_notification_comment_only_on_tag TINYINT(1) NOT NULL DEFAULT 1');
        
        $this->addSql('UPDATE user SET preferences_email_notification_global = preference_notification');
        $this->addSql('UPDATE user SET preferences_email_notification_issue_created = preference_notification_issue_created');
        $this->addSql('UPDATE user SET preferences_email_notification_issue_updated = preference_notification_issue_updated');
        $this->addSql('UPDATE user SET preferences_email_notification_comment_created = preference_notification_comment_created');
        $this->addSql('UPDATE user SET preferences_email_notification_comment_updated = preference_notification_comment_updated');
        $this->addSql('UPDATE user SET preferences_email_notification_comment_only_on_tag = preference_notification_comment_only_on_tag');
        
        $this->addSql('ALTER TABLE user DROP preference_notification, DROP preference_notification_issue_created, DROP preference_notification_issue_updated, DROP preference_notification_comment_created, DROP preference_notification_comment_updated, DROP preference_notification_comment_only_on_tag');
        
        $this->addSql('ALTER TABLE user ALTER preferences_email_notification_global DROP DEFAULT, ALTER preferences_email_notification_issue_created DROP DEFAULT, ALTER preferences_email_notification_issue_updated DROP DEFAULT, ALTER preferences_email_notification_comment_created DROP DEFAULT, ALTER preferences_email_notification_comment_updated DROP DEFAULT, ALTER preferences_email_notification_comment_only_on_tag DROP DEFAULT, ALTER preferences_push_notification_global DROP DEFAULT, ALTER preferences_push_notification_issue_created DROP DEFAULT, ALTER preferences_push_notification_issue_updated DROP DEFAULT, ALTER preferences_push_notification_comment_created DROP DEFAULT, ALTER preferences_push_notification_comment_updated DROP DEFAULT, ALTER preferences_push_notification_comment_only_on_tag DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD preference_notification TINYINT(1) NOT NULL DEFAULT 1, ADD preference_notification_issue_created TINYINT(1) NOT NULL DEFAULT 1, ADD preference_notification_issue_updated TINYINT(1) NOT NULL DEFAULT 1, ADD preference_notification_comment_created TINYINT(1) NOT NULL DEFAULT 1, ADD preference_notification_comment_updated TINYINT(1) NOT NULL DEFAULT 1, ADD preference_notification_comment_only_on_tag TINYINT(1) NOT NULL DEFAULT 1');
        
        // Copy values back from email notification columns to old columns
        $this->addSql('UPDATE user SET preference_notification = preferences_email_notification_global');
        $this->addSql('UPDATE user SET preference_notification_issue_created = preferences_email_notification_issue_created');
        $this->addSql('UPDATE user SET preference_notification_issue_updated = preferences_email_notification_issue_updated');
        $this->addSql('UPDATE user SET preference_notification_comment_created = preferences_email_notification_comment_created');
        $this->addSql('UPDATE user SET preference_notification_comment_updated = preferences_email_notification_comment_updated');
        $this->addSql('UPDATE user SET preference_notification_comment_only_on_tag = preferences_email_notification_comment_only_on_tag');
        
        $this->addSql('ALTER TABLE user DROP preferences_email_notification_global, DROP preferences_email_notification_issue_created, DROP preferences_email_notification_issue_updated, DROP preferences_email_notification_comment_created, DROP preferences_email_notification_comment_updated, DROP preferences_email_notification_comment_only_on_tag, DROP preferences_push_notification_global, DROP preferences_push_notification_issue_created, DROP preferences_push_notification_issue_updated, DROP preferences_push_notification_comment_created, DROP preferences_push_notification_comment_updated, DROP preferences_push_notification_comment_only_on_tag');
        
        $this->addSql('ALTER TABLE user ALTER preference_notification DROP DEFAULT, ALTER preference_notification_issue_created DROP DEFAULT, ALTER preference_notification_issue_updated DROP DEFAULT, ALTER preference_notification_comment_created DROP DEFAULT, ALTER preference_notification_comment_updated DROP DEFAULT, ALTER preference_notification_comment_only_on_tag DROP DEFAULT');
    }
}
