<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305121304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tiempo DROP FOREIGN KEY FK_B96A1359D86650F');
        $this->addSql('DROP INDEX IDX_B96A1359D86650F ON tiempo');
        $this->addSql('ALTER TABLE tiempo CHANGE user_id_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tiempo ADD CONSTRAINT FK_B96A135A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B96A135A76ED395 ON tiempo (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tiempo DROP FOREIGN KEY FK_B96A135A76ED395');
        $this->addSql('DROP INDEX IDX_B96A135A76ED395 ON tiempo');
        $this->addSql('ALTER TABLE tiempo CHANGE user_id user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tiempo ADD CONSTRAINT FK_B96A1359D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B96A1359D86650F ON tiempo (user_id_id)');
    }
}
