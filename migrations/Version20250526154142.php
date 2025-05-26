<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526154142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD default_issue_type_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE56955561 FOREIGN KEY (default_issue_type_id) REFERENCES issue_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_2FB3D0EE56955561 ON project (default_issue_type_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE56955561
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_2FB3D0EE56955561 ON project
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP default_issue_type_id
        SQL);
    }
}
