<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105151305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue_label (id INT AUTO_INCREMENT NOT NULL, jira_label VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user ADD issue_label_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E4110592 FOREIGN KEY (issue_label_id) REFERENCES issue_label (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649E4110592 ON user (issue_label_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE issue_label');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E4110592');
        $this->addSql('DROP INDEX IDX_8D93D649E4110592 ON user');
        $this->addSql('ALTER TABLE user DROP issue_label_id');
    }
}
