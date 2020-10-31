<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201030014510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories ADD formatted_created_at VARCHAR(255) NOT NULL, ADD formatted_updated_at VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE posts ADD formatted_created_at VARCHAR(255) NOT NULL, ADD formatted_updated_at VARCHAR(255) NOT NULL, CHANGE views views INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE users ADD formatted_created_at VARCHAR(255) NOT NULL, ADD formatted_updated_at VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories DROP formatted_created_at, DROP formatted_updated_at, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE posts DROP formatted_created_at, DROP formatted_updated_at, CHANGE views views INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE users DROP formatted_created_at, DROP formatted_updated_at, CHANGE created_at created_at DATETIME NOT NULL');
    }
}
