<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210728112626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE flowpack_task_domain_model_taskexecution (persistence_object_identifier VARCHAR(40) NOT NULL, taskidentifier VARCHAR(255) NOT NULL, workload LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', handlerclass VARCHAR(255) NOT NULL, scheduletime DATETIME NOT NULL, starttime DATETIME DEFAULT NULL, endtime DATETIME DEFAULT NULL, duration DOUBLE PRECISION DEFAULT NULL, status VARCHAR(255) NOT NULL, result VARCHAR(255) DEFAULT NULL, exception VARCHAR(255) DEFAULT NULL, attempts INT NOT NULL, PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE flowpack_task_domain_model_taskexecution');
    }
}
