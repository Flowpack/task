<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

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

    public function jsonSerialize()
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
