<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201115201720 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dislike (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, post_id INT DEFAULT NULL, INDEX IDX_FE3BECAAA76ED395 (user_id), INDEX IDX_FE3BECAA4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dislike ADD CONSTRAINT FK_FE3BECAAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE dislike ADD CONSTRAINT FK_FE3BECAA4B89032C FOREIGN KEY (post_id) REFERENCES posts (id)');
        $this->addSql('ALTER TABLE categories CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE posts CHANGE views views INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dislike');
        $this->addSql('ALTER TABLE categories CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE posts CHANGE views views INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE created_at created_at DATETIME NOT NULL');
    }
}
