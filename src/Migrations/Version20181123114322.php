<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181123114322 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bought_template (id INT AUTO_INCREMENT NOT NULL, offer_id INT NOT NULL, template_id INT NOT NULL, INDEX IDX_562F9A8853C674EE (offer_id), INDEX IDX_562F9A885DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE position (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, reach INT NOT NULL, remaining INT NOT NULL, has_time TINYINT(1) NOT NULL, max_quantity INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE position_template (id INT AUTO_INCREMENT NOT NULL, position_id INT NOT NULL, template_id INT NOT NULL, count INT NOT NULL, INDEX IDX_B3474D34DD842E46 (position_id), INDEX IDX_B3474D345DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, md5 VARCHAR(32) NOT NULL, client_email VARCHAR(100) NOT NULL, client_name VARCHAR(100) NOT NULL, message VARCHAR(1000) NOT NULL, status VARCHAR(32) NOT NULL, viewed DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offer_template (id INT AUTO_INCREMENT NOT NULL, template_id INT NOT NULL, offer_id INT NOT NULL, status VARCHAR(32) NOT NULL, INDEX IDX_EE1DAFE15DA0FB8 (template_id), INDEX IDX_EE1DAFE153C674EE (offer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, reach INT NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bought_template ADD CONSTRAINT FK_562F9A8853C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE bought_template ADD CONSTRAINT FK_562F9A885DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('ALTER TABLE position_template ADD CONSTRAINT FK_B3474D34DD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('ALTER TABLE position_template ADD CONSTRAINT FK_B3474D345DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('ALTER TABLE offer_template ADD CONSTRAINT FK_EE1DAFE15DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('ALTER TABLE offer_template ADD CONSTRAINT FK_EE1DAFE153C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE position_template DROP FOREIGN KEY FK_B3474D34DD842E46');
        $this->addSql('ALTER TABLE bought_template DROP FOREIGN KEY FK_562F9A8853C674EE');
        $this->addSql('ALTER TABLE offer_template DROP FOREIGN KEY FK_EE1DAFE153C674EE');
        $this->addSql('ALTER TABLE bought_template DROP FOREIGN KEY FK_562F9A885DA0FB8');
        $this->addSql('ALTER TABLE position_template DROP FOREIGN KEY FK_B3474D345DA0FB8');
        $this->addSql('ALTER TABLE offer_template DROP FOREIGN KEY FK_EE1DAFE15DA0FB8');
        $this->addSql('DROP TABLE bought_template');
        $this->addSql('DROP TABLE position');
        $this->addSql('DROP TABLE position_template');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE offer_template');
        $this->addSql('DROP TABLE template');
    }
}