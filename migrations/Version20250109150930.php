<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250109150930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE editor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_game (id INT AUTO_INCREMENT NOT NULL, editor_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, release_date DATE NOT NULL, description VARCHAR(512) NOT NULL, INDEX IDX_24BC6C506995AC4C (editor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_game_category (video_game_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_A672CAD716230A8 (video_game_id), INDEX IDX_A672CAD712469DE2 (category_id), PRIMARY KEY(video_game_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_game ADD CONSTRAINT FK_24BC6C506995AC4C FOREIGN KEY (editor_id) REFERENCES editor (id)');
        $this->addSql('ALTER TABLE video_game_category ADD CONSTRAINT FK_A672CAD716230A8 FOREIGN KEY (video_game_id) REFERENCES video_game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_game_category ADD CONSTRAINT FK_A672CAD712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_game DROP FOREIGN KEY FK_24BC6C506995AC4C');
        $this->addSql('ALTER TABLE video_game_category DROP FOREIGN KEY FK_A672CAD716230A8');
        $this->addSql('ALTER TABLE video_game_category DROP FOREIGN KEY FK_A672CAD712469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE editor');
        $this->addSql('DROP TABLE video_game');
        $this->addSql('DROP TABLE video_game_category');
    }
}
