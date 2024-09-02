<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305074337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoices (id INT AUTO_INCREMENT NOT NULL, paid TINYINT(1) NOT NULL, slug VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', paid_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operations (id INT AUTO_INCREMENT NOT NULL, services_id INT DEFAULT NULL, invoices_id INT DEFAULT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) DEFAULT NULL, quote VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', finished_at DATETIME DEFAULT NULL, last_modified_at DATETIME NOT NULL, surface DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION DEFAULT NULL, INDEX IDX_28145348AEF5A6C1 (services_id), UNIQUE INDEX UNIQ_281453482454BA75 (invoices_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operations_users (operations_id INT NOT NULL, users_id INT NOT NULL, INDEX IDX_EB26A3FF9384C38A (operations_id), INDEX IDX_EB26A3FF67B3B43D (users_id), PRIMARY KEY(operations_id, users_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE services (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, price VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, zipcode VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', active_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_28145348AEF5A6C1 FOREIGN KEY (services_id) REFERENCES services (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_281453482454BA75 FOREIGN KEY (invoices_id) REFERENCES invoices (id)');
        $this->addSql('ALTER TABLE operations_users ADD CONSTRAINT FK_EB26A3FF9384C38A FOREIGN KEY (operations_id) REFERENCES operations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operations_users ADD CONSTRAINT FK_EB26A3FF67B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_28145348AEF5A6C1');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_281453482454BA75');
        $this->addSql('ALTER TABLE operations_users DROP FOREIGN KEY FK_EB26A3FF9384C38A');
        $this->addSql('ALTER TABLE operations_users DROP FOREIGN KEY FK_EB26A3FF67B3B43D');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE operations');
        $this->addSql('DROP TABLE operations_users');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE services');
        $this->addSql('DROP TABLE users');
    }
}
