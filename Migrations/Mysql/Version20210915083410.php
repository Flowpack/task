<?php
declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210915083410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert the exception field to text';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE flowpack_task_domain_model_taskexecution CHANGE exception exception LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}
