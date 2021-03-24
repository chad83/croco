<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210324062008 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dom ADD job_id INT NOT NULL, ADD parent_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dom ADD CONSTRAINT FK_61F9D063BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('CREATE INDEX IDX_61F9D063BE04EA9 ON dom (job_id)');
        $this->addSql('CREATE INDEX IDX_61F9D0635223F47 ON dom (file_type)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F8694309E4 ON job (site)');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62069893F8F');
        $this->addSql('DROP INDEX IDX_140AB62069893F8F ON page');
        $this->addSql('ALTER TABLE page DROP dom_id');
        $this->addSql('CREATE INDEX IDX_140AB6204F139D0C ON page (status_code)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dom DROP FOREIGN KEY FK_61F9D063BE04EA9');
        $this->addSql('DROP INDEX IDX_61F9D063BE04EA9 ON dom');
        $this->addSql('DROP INDEX IDX_61F9D0635223F47 ON dom');
        $this->addSql('ALTER TABLE dom DROP job_id, DROP parent_url');
        $this->addSql('DROP INDEX IDX_FBD8E0F8694309E4 ON job');
        $this->addSql('DROP INDEX IDX_140AB6204F139D0C ON page');
        $this->addSql('ALTER TABLE page ADD dom_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62069893F8F FOREIGN KEY (dom_id) REFERENCES dom (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_140AB62069893F8F ON page (dom_id)');
    }
}
