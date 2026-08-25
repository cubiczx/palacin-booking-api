<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260825141001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE experiences (title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, provider_id VARCHAR(255) NOT NULL, id VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE reservations (session_id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, seats INT NOT NULL, contact_email VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL, id VARCHAR(255) NOT NULL, price_cents INT NOT NULL, price_currency VARCHAR(3) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE sessions (experience_id VARCHAR(255) NOT NULL, date DATETIME NOT NULL, capacity INT NOT NULL, available_seats INT NOT NULL, id VARCHAR(255) NOT NULL, price_cents INT NOT NULL, price_currency VARCHAR(3) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_experience_day ON sessions (experience_id, date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE experiences');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('DROP TABLE sessions');
    }
}
