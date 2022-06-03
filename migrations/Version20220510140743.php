<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220510140743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_C8B28E44A76ED395 ON candidate (user_id)');
        $this->addSql('ALTER TABLE course ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB9A76ED395 ON course (user_id)');
        $this->addSql('ALTER TABLE question ADD survey_id INT NOT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494EB3FE509D ON question (survey_id)');
        $this->addSql('ALTER TABLE result ADD survey_id INT NOT NULL, ADD candidate_id INT NOT NULL');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC113B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC11391BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)');
        $this->addSql('CREATE INDEX IDX_136AC113B3FE509D ON result (survey_id)');
        $this->addSql('CREATE INDEX IDX_136AC11391BD8781 ON result (candidate_id)');
        $this->addSql('ALTER TABLE survey ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AD5F9BFCA76ED395 ON survey (user_id)');
        $this->addSql('ALTER TABLE visible_course ADD user_id INT NOT NULL, ADD course_id INT NOT NULL');
        $this->addSql('ALTER TABLE visible_course ADD CONSTRAINT FK_B7B2F55DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE visible_course ADD CONSTRAINT FK_B7B2F55D591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_B7B2F55DA76ED395 ON visible_course (user_id)');
        $this->addSql('CREATE INDEX IDX_B7B2F55D591CC992 ON visible_course (course_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44A76ED395');
        $this->addSql('DROP INDEX IDX_C8B28E44A76ED395 ON candidate');
        $this->addSql('ALTER TABLE candidate DROP user_id');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9A76ED395');
        $this->addSql('DROP INDEX IDX_169E6FB9A76ED395 ON course');
        $this->addSql('ALTER TABLE course DROP user_id');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EB3FE509D');
        $this->addSql('DROP INDEX IDX_B6F7494EB3FE509D ON question');
        $this->addSql('ALTER TABLE question DROP survey_id');
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC113B3FE509D');
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC11391BD8781');
        $this->addSql('DROP INDEX IDX_136AC113B3FE509D ON result');
        $this->addSql('DROP INDEX IDX_136AC11391BD8781 ON result');
        $this->addSql('ALTER TABLE result DROP survey_id, DROP candidate_id');
        $this->addSql('ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFCA76ED395');
        $this->addSql('DROP INDEX IDX_AD5F9BFCA76ED395 ON survey');
        $this->addSql('ALTER TABLE survey DROP user_id');
        $this->addSql('ALTER TABLE visible_course DROP FOREIGN KEY FK_B7B2F55DA76ED395');
        $this->addSql('ALTER TABLE visible_course DROP FOREIGN KEY FK_B7B2F55D591CC992');
        $this->addSql('DROP INDEX IDX_B7B2F55DA76ED395 ON visible_course');
        $this->addSql('DROP INDEX IDX_B7B2F55D591CC992 ON visible_course');
        $this->addSql('ALTER TABLE visible_course DROP user_id, DROP course_id');
    }
}
