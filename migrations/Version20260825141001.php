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
        $this->addSql('CREATE TABLE experiences (title VARCHAR(255) NOT NULL, description CLOB NOT NULL, provider_id VARCHAR NOT NULL, id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE reservations (session_id VARCHAR NOT NULL, user_id VARCHAR NOT NULL, seats INTEGER NOT NULL, contact_email VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL, id VARCHAR NOT NULL, price_cents INTEGER NOT NULL, price_currency VARCHAR(3) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE sessions (experience_id VARCHAR NOT NULL, date DATETIME NOT NULL, capacity INTEGER NOT NULL, available_seats INTEGER NOT NULL, id VARCHAR NOT NULL, price_cents INTEGER NOT NULL, price_currency VARCHAR(3) NOT NULL, PRIMARY KEY (id))');
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
