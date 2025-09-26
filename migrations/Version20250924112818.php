<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924112818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_authentication_logs (ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, login_at DATETIME DEFAULT NULL, location JSON NOT NULL, id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, INDEX IDX_74E076AAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_authentication_logs ADD CONSTRAINT FK_74E076AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_authentication_logs DROP FOREIGN KEY FK_74E076AAA76ED395');
        $this->addSql('DROP TABLE user_authentication_logs');
    }
}
