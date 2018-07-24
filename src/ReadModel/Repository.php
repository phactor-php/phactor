<?php

namespace Carnage\Phactor\ReadModel;

use Doctrine\Common\Collections\Criteria;

interface Repository
{
    public function add($element);

    public function remove($element);

    public function get($key);

    public function matching(Criteria $criteria);

    public function commit();
}