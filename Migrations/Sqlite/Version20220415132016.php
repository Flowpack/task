<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220415132016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE flowpack_task_domain_model_taskexecution (persistence_object_identifier VARCHAR(40) NOT NULL, taskidentifier VARCHAR(255) NOT NULL, workload CLOB NOT NULL --(DC2Type:object)
        , handlerclass VARCHAR(255) NOT NULL, scheduletime DATETIME NOT NULL, starttime DATETIME DEFAULT NULL, endtime DATETIME DEFAULT NULL, duration DOUBLE PRECISION DEFAULT NULL, status VARCHAR(255) NOT NULL, result VARCHAR(255) DEFAULT NULL, exception CLOB DEFAULT NULL, attempts INTEGER NOT NULL, PRIMARY KEY(persistence_object_identifier))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE flowpack_task_domain_model_taskexecution');
    }
}
