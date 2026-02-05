<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260205100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change User-IssueLabel relationship from ManyToOne to ManyToMany';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_issue_label (user_id INT NOT NULL, issue_label_id INT NOT NULL, INDEX IDX_USER_ISSUE_LABEL_USER (user_id), INDEX IDX_USER_ISSUE_LABEL_LABEL (issue_label_id), PRIMARY KEY(user_id, issue_label_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_issue_label ADD CONSTRAINT FK_USER_ISSUE_LABEL_USER FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_issue_label ADD CONSTRAINT FK_USER_ISSUE_LABEL_LABEL FOREIGN KEY (issue_label_id) REFERENCES issue_label (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO user_issue_label (user_id, issue_label_id) SELECT id, issue_label_id FROM user WHERE issue_label_id IS NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E4110592');
        $this->addSql('DROP INDEX IDX_8D93D649E4110592 ON user');
        $this->addSql('ALTER TABLE user DROP issue_label_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD issue_label_id INT DEFAULT NULL');
        $this->addSql('INSERT INTO user (id, issue_label_id) SELECT uil.user_id, uil.issue_label_id FROM user_issue_label uil ON DUPLICATE KEY UPDATE issue_label_id = uil.issue_label_id');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E4110592 FOREIGN KEY (issue_label_id) REFERENCES issue_label (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649E4110592 ON user (issue_label_id)');
        $this->addSql('DROP TABLE user_issue_label');
    }
}
