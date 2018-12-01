<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181201113753 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, offer_id INT NOT NULL, template_id INT NOT NULL, status VARCHAR(32) NOT NULL, INDEX IDX_F529939853C674EE (offer_id), INDEX IDX_F52993985DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939853C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993985DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('DROP TABLE bought_template');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bought_template (id INT AUTO_INCREMENT NOT NULL, offer_id INT NOT NULL, template_id INT NOT NULL, INDEX IDX_562F9A8853C674EE (offer_id), INDEX IDX_562F9A885DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bought_template ADD CONSTRAINT FK_562F9A8853C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE bought_template ADD CONSTRAINT FK_562F9A885DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('DROP TABLE `order`');
    }
}
