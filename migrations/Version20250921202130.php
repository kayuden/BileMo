<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921202130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(180) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8D93D64919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE product CHANGE model model VARCHAR(150) DEFAULT NULL, CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE currency currency VARCHAR(10) DEFAULT NULL, CHANGE release_date release_date DATE DEFAULT NULL, CHANGE stock_status stock_status VARCHAR(50) DEFAULT NULL, CHANGE os os VARCHAR(50) DEFAULT NULL, CHANGE color color VARCHAR(20) DEFAULT NULL, CHANGE screen_size screen_size VARCHAR(20) DEFAULT NULL, CHANGE resolution resolution VARCHAR(20) DEFAULT NULL, CHANGE battery battery VARCHAR(50) DEFAULT NULL, CHANGE camera camera VARCHAR(50) DEFAULT NULL, CHANGE weight weight VARCHAR(50) DEFAULT NULL, CHANGE dimensions dimensions VARCHAR(100) DEFAULT NULL, CHANGE image_url image_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE product CHANGE model model VARCHAR(150) DEFAULT \'NULL\', CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE currency currency VARCHAR(10) DEFAULT \'NULL\', CHANGE release_date release_date DATE DEFAULT \'NULL\', CHANGE stock_status stock_status VARCHAR(50) DEFAULT \'NULL\', CHANGE os os VARCHAR(50) DEFAULT \'NULL\', CHANGE color color VARCHAR(20) DEFAULT \'NULL\', CHANGE screen_size screen_size VARCHAR(20) DEFAULT \'NULL\', CHANGE resolution resolution VARCHAR(20) DEFAULT \'NULL\', CHANGE battery battery VARCHAR(50) DEFAULT \'NULL\', CHANGE camera camera VARCHAR(50) DEFAULT \'NULL\', CHANGE weight weight VARCHAR(50) DEFAULT \'NULL\', CHANGE dimensions dimensions VARCHAR(100) DEFAULT \'NULL\', CHANGE image_url image_url VARCHAR(255) DEFAULT \'NULL\'');
    }
}
