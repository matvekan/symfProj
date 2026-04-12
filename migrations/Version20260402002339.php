<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260402002339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing password_reset columns and user email unique index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset ADD email VARCHAR(255) NOT NULL, ADD used_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE password_reset DROP email, DROP used_at');
    }
}
