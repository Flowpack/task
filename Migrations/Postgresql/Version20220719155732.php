<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220719155732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial PostgreSQL migration';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSqlPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSqlPlatform'."
        );

        $this->addSql('CREATE TABLE flowpack_task_domain_model_taskexecution (persistence_object_identifier VARCHAR(40) NOT NULL, taskidentifier VARCHAR(255) NOT NULL, workload TEXT NOT NULL, handlerclass VARCHAR(255) NOT NULL, scheduletime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, starttime TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, endtime TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, duration DOUBLE PRECISION DEFAULT NULL, status VARCHAR(255) NOT NULL, result VARCHAR(255) DEFAULT NULL, exception TEXT DEFAULT NULL, attempts INT NOT NULL, PRIMARY KEY(persistence_object_identifier))');
        $this->addSql('COMMENT ON COLUMN flowpack_task_domain_model_taskexecution.workload IS \'(DC2Type:object)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSqlPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSqlPlatform'."
        );

        $this->addSql('DROP TABLE flowpack_task_domain_model_taskexecution');
    }
}
