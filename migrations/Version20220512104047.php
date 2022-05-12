<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220512104047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate ADD delete_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD delete_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD delete_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE result ADD delete_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE survey ADD delete_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD last_connection DATETIME DEFAULT NULL, ADD delete_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate DROP delete_date');
        $this->addSql('ALTER TABLE course DROP delete_date');
        $this->addSql('ALTER TABLE question DROP delete_date');
        $this->addSql('ALTER TABLE result DROP delete_date');
        $this->addSql('ALTER TABLE survey DROP delete_date');
        $this->addSql('ALTER TABLE user DROP last_connection, DROP delete_date');
    }
}
