<?php

namespace Phactor\ReadModel;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

interface Repository
{
    public function add($element): void;

    public function remove($element): void;

    public function get($key);

    public function matching(Criteria $criteria): Collection;

    public function commit(): void;
}
