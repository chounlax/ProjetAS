<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261011181907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sécurité : suspension des comptes, historique des connexions et des mots de passe';
    }

    public function up(Schema $schema): void
    {
        // comptes existants : actifs, date d'inscription inconnue (NULL)
        $this->addSql('CREATE TABLE login_history (id INT AUTO_INCREMENT NOT NULL, connected_at DATETIME NOT NULL, success TINYINT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_LOGIN_HISTORY_DATE (connected_at), INDEX IDX_37976E36A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE password_history (id INT AUTO_INCREMENT NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_F352144A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE login_history ADD CONSTRAINT FK_37976E36A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE password_history ADD CONSTRAINT FK_F352144A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD is_active TINYINT DEFAULT 1 NOT NULL, ADD created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE login_history DROP FOREIGN KEY FK_37976E36A76ED395');
        $this->addSql('ALTER TABLE password_history DROP FOREIGN KEY FK_F352144A76ED395');
        $this->addSql('DROP TABLE login_history');
        $this->addSql('DROP TABLE password_history');
        $this->addSql('ALTER TABLE user DROP is_active, DROP created_at');
    }
}
