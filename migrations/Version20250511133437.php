<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511133437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD preference_notification TINYINT(1) NOT NULL, ADD preference_notification_issue_created TINYINT(1) NOT NULL, ADD preference_notification_issue_updated TINYINT(1) NOT NULL, ADD preference_notification_comment_created TINYINT(1) NOT NULL, ADD preference_notification_comment_updated TINYINT(1) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP preference_notification, DROP preference_notification_issue_created, DROP preference_notification_issue_updated, DROP preference_notification_comment_created, DROP preference_notification_comment_updated
        SQL);
    }
}
