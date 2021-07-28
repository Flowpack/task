<?php
/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

namespace Flowpack\Task\Domain\Task;

interface WorkloadInterface extends \JsonSerializable
{
    public function getData(): array;

}
