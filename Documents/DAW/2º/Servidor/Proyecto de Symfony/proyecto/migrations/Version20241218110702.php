<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218110702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pregunta ADD respuesta1 VARCHAR(255) DEFAULT NULL, ADD respuesta2 VARCHAR(255) DEFAULT NULL, ADD respuesta3 VARCHAR(255) DEFAULT NULL, ADD respuesta4 VARCHAR(255) DEFAULT NULL, ADD solucion INT DEFAULT NULL, CHANGE enunciado texto VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE respuesta ADD respuesta_id INT NOT NULL, DROP texto');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pregunta DROP respuesta1, DROP respuesta2, DROP respuesta3, DROP respuesta4, DROP solucion, CHANGE texto enunciado VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE respuesta ADD texto VARCHAR(255) NOT NULL, DROP respuesta_id');
    }
}
