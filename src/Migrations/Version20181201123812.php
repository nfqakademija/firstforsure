<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181201123812 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F8D9F6D38 ON message (order_id)');
        $this->addSql('ALTER TABLE user CHANGE last_login last_login DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F8D9F6D38');
        $this->addSql('DROP INDEX IDX_B6BD307F8D9F6D38 ON message');
        $this->addSql('ALTER TABLE user CHANGE last_login last_login DATETIME NOT NULL');
    }
}
