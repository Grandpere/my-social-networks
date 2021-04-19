<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210413210704 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD firstname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD lastname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD display_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD active BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD enabled BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD facebook_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP firstname');
        $this->addSql('ALTER TABLE "user" DROP lastname');
        $this->addSql('ALTER TABLE "user" DROP display_name');
        $this->addSql('ALTER TABLE "user" DROP active');
        $this->addSql('ALTER TABLE "user" DROP enabled');
        $this->addSql('ALTER TABLE "user" DROP facebook_id');
    }
}
