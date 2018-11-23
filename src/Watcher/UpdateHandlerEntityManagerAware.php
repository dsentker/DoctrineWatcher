<?php

namespace Watcher;

use Doctrine\ORM\EntityManager;

/**
 * Interface UpdateHandlerEntityManagerAware
 *
 * @package Watcher
 */
interface UpdateHandlerEntityManagerAware extends UpdateHandler
{

    public function setEntityManager(EntityManager $em);

}