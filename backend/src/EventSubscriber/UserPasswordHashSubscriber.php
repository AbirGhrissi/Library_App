<?php

namespace App\EventSubscriber;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordHashSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['hashPassword'],
            BeforeEntityUpdatedEvent::class => ['hashPassword'],
        ];
    }

    public function hashPassword($event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof User)) {
            return;
        }

        if ($entity->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $entity,
                $entity->getPlainPassword()
            );
            $entity->setPassword($hashedPassword);
        }
    }
}
