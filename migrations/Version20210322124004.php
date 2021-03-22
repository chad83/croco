<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210322124004 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dom (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, file_type VARCHAR(80) DEFAULT NULL, file_size INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job (id INT AUTO_INCREMENT NOT NULL, site VARCHAR(255) NOT NULL, date_started DATETIME NOT NULL, date_finished DATETIME DEFAULT NULL, status VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, dom_id INT DEFAULT NULL, job_id INT NOT NULL, title VARCHAR(255) NOT NULL, status_code INT DEFAULT NULL, INDEX IDX_140AB62069893F8F (dom_id), INDEX IDX_140AB620BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_page (page_source INT NOT NULL, page_target INT NOT NULL, INDEX IDX_93CEAAFA305C896D (page_source), INDEX IDX_93CEAAFA29B9D9E2 (page_target), PRIMARY KEY(page_source, page_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62069893F8F FOREIGN KEY (dom_id) REFERENCES dom (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('ALTER TABLE page_page ADD CONSTRAINT FK_93CEAAFA305C896D FOREIGN KEY (page_source) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_page ADD CONSTRAINT FK_93CEAAFA29B9D9E2 FOREIGN KEY (page_target) REFERENCES page (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62069893F8F');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620BE04EA9');
        $this->addSql('ALTER TABLE page_page DROP FOREIGN KEY FK_93CEAAFA305C896D');
        $this->addSql('ALTER TABLE page_page DROP FOREIGN KEY FK_93CEAAFA29B9D9E2');
        $this->addSql('DROP TABLE dom');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE page_page');
        $this->addSql('DROP TABLE site');
    }
}
