<?php

namespace DSentker\Watcher;

use Doctrine\ORM\EntityManager;

interface UpdateHandlerEntityManagerAware extends UpdateHandler
{

    public function setEntityManager(EntityManager $em);

}