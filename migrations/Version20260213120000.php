<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260213120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add preferenceNotificationIssueDeleted column to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD preference_notification_issue_deleted JSON NOT NULL DEFAULT \'["in_app","email"]\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP preference_notification_issue_deleted');
    }
}
