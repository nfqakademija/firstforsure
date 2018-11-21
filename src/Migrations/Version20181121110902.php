<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181121110902 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bought_template DROP FOREIGN KEY FK_562F9A88A918D097');
        $this->addSql('DROP INDEX IDX_562F9A88A918D097 ON bought_template');
        $this->addSql('ALTER TABLE bought_template ADD template_id INT NOT NULL, CHANGE bought_template_id offer_id INT NOT NULL');
        $this->addSql('ALTER TABLE bought_template ADD CONSTRAINT FK_562F9A8853C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE bought_template ADD CONSTRAINT FK_562F9A885DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('CREATE INDEX IDX_562F9A8853C674EE ON bought_template (offer_id)');
        $this->addSql('CREATE INDEX IDX_562F9A885DA0FB8 ON bought_template (template_id)');
        $this->addSql('ALTER TABLE template ADD status VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bought_template DROP FOREIGN KEY FK_562F9A8853C674EE');
        $this->addSql('ALTER TABLE bought_template DROP FOREIGN KEY FK_562F9A885DA0FB8');
        $this->addSql('DROP INDEX IDX_562F9A8853C674EE ON bought_template');
        $this->addSql('DROP INDEX IDX_562F9A885DA0FB8 ON bought_template');
        $this->addSql('ALTER TABLE bought_template ADD bought_template_id INT NOT NULL, DROP offer_id, DROP template_id');
        $this->addSql('ALTER TABLE bought_template ADD CONSTRAINT FK_562F9A88A918D097 FOREIGN KEY (bought_template_id) REFERENCES offer_template (id)');
        $this->addSql('CREATE INDEX IDX_562F9A88A918D097 ON bought_template (bought_template_id)');
        $this->addSql('ALTER TABLE template DROP status');
    }
}
