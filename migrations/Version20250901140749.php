<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901140749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE log_entry (id INT AUTO_INCREMENT NOT NULL, log_type VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, log_at DATETIME DEFAULT NULL, datas JSON DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_B5F762DA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE log_entry ADD CONSTRAINT FK_B5F762DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_entry DROP FOREIGN KEY FK_B5F762DA76ED395');
        $this->addSql('DROP TABLE log_entry');
    }
}
