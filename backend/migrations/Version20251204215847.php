<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204215847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_author (book_id INT NOT NULL, author_id INT NOT NULL, INDEX IDX_9478D34516A2B381 (book_id), INDEX IDX_9478D345F675F31B (author_id), PRIMARY KEY (book_id, author_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE book_category (book_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_1FB30F9816A2B381 (book_id), INDEX IDX_1FB30F9812469DE2 (category_id), PRIMARY KEY (book_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE book_author ADD CONSTRAINT FK_9478D34516A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_author ADD CONSTRAINT FK_9478D345F675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_category ADD CONSTRAINT FK_1FB30F9816A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_category ADD CONSTRAINT FK_1FB30F9812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE author CHANGE birth_date birth_date DATE DEFAULT NULL, CHANGE nationality nationality VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY `FK_CBE5A33112469DE2`');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY `FK_CBE5A331F675F31B`');
        $this->addSql('DROP INDEX IDX_CBE5A331F675F31B ON book');
        $this->addSql('DROP INDEX IDX_CBE5A33112469DE2 ON book');
        $this->addSql('ALTER TABLE book DROP author_id, DROP category_id, CHANGE publication_date publication_date DATE DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE borrowing CHANGE returned_at returned_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE cart CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE description description VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE publisher CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_author DROP FOREIGN KEY FK_9478D34516A2B381');
        $this->addSql('ALTER TABLE book_author DROP FOREIGN KEY FK_9478D345F675F31B');
        $this->addSql('ALTER TABLE book_category DROP FOREIGN KEY FK_1FB30F9816A2B381');
        $this->addSql('ALTER TABLE book_category DROP FOREIGN KEY FK_1FB30F9812469DE2');
        $this->addSql('DROP TABLE book_author');
        $this->addSql('DROP TABLE book_category');
        $this->addSql('ALTER TABLE author CHANGE birth_date birth_date DATE DEFAULT \'NULL\', CHANGE nationality nationality VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE book ADD author_id INT NOT NULL, ADD category_id INT NOT NULL, CHANGE publication_date publication_date DATE DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT `FK_CBE5A33112469DE2` FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT `FK_CBE5A331F675F31B` FOREIGN KEY (author_id) REFERENCES author (id)');
        $this->addSql('CREATE INDEX IDX_CBE5A331F675F31B ON book (author_id)');
        $this->addSql('CREATE INDEX IDX_CBE5A33112469DE2 ON book (category_id)');
        $this->addSql('ALTER TABLE borrowing CHANGE returned_at returned_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE cart CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE category CHANGE description description VARCHAR(500) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE publisher CHANGE address address VARCHAR(255) DEFAULT \'NULL\', CHANGE phone phone VARCHAR(20) DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE phone phone VARCHAR(20) DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
