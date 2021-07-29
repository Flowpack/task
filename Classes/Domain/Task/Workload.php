<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

class Workload implements WorkloadInterface
{
    /**
     * @var array
     */
    protected array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
