<?php

namespace Carnage\Phactor\Doctrine;

use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\DomainMessage;
use Carnage\Phactor\Persistence\EventStore as EventStoreInterface;
use Carnage\Phactor\Persistence\NotFoundException;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class OrmEventStore
 * @package Carnage\Cqorms\Persistence\EventStore
 * @TODO handle versioning.
 */
final class OrmEventStore implements EventStoreInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function eventsMatching(Criteria $criteria): Iterable
    {
        $repository = $this->entityManager->getRepository(DomainMessage::class);
        return $repository->matching($criteria);
    }

    public function load(ActorIdentity $identity): Iterable
    {
        $repository = $this->entityManager->getRepository(DomainMessage::class);
        $events = $repository->findBy(['actorId' => $identity->getId(), 'actorClass' => $identity->getClass()]);

        if (empty($events)) {
            throw new NotFoundException('Not found');
        }

        return $events;
    }

    public function save(ActorIdentity $identity, DomainMessage ...$messages)
    {
        $this->entityManager->beginTransaction();

        foreach ($messages as $message) {
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
        $this->entityManager->commit();
    }
}