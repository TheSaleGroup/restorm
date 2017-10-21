<?php
/*
 * The MIT License
 *
 * Copyright 2017 Rob Treacy <robert.treacy@thesalegroup.co.uk>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace TheSaleGroup\Restorm\Entity;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TheSaleGroup\Restorm\EntityManager;
use ProxyManager\Proxy\GhostObjectInterface;
use TheSaleGroup\Restorm\Event\PreBuildEvent;
use TheSaleGroup\Restorm\Entity\EntityMetadata;

/**
 * Description of Proxy
 *
 * @author Rob Treacy <robert.treacy@thesalegroup.co.uk>
 */
class Proxy implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            PreBuildEvent::NAME => [
                ['buildProxy', -20],
            ],
        );
    }

    public function buildProxy(PreBuildEvent $event)
    {
        if (!$event->isPartialData() || $event->getEntity()) {
            return;
        }

        $proxyOptions = array(
            'skippedProperties' => [],
        );

        $entityMapping = $this->entityManager->getEntityMappingRegister()->getEntityMapping($event->getEntityClass());
        $properties = $entityMapping->getProperties();

        foreach ($properties as $propertyName => $propertyOptions) {
            $dataPropertyName = $propertyOptions['map_from'] ?? $propertyName;
            if (!property_exists($event->getData(), $dataPropertyName)) {
                continue;
            }

            $proxyOptions['skippedProperties'][] = $this->getPropertyProxyName($event->getEntityClass(), $dataPropertyName);
        }

        $initializer = function (
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            & $initializer,
            array $properties
            ) use ($event) {
            $initializer = null;

            $identifierValue = $this->entityManager->getEntityMetadataRegister()->getEntityMetadata($ghostObject)->getIdentifierValue();
            $this->entityManager->getRepository($event->getEntityClass())->findOne($identifierValue);

            return true;
        };

        $proxyEntity = $this->entityManager->getProxyFactory()->createProxy($event->getEntityClass(), $initializer, $proxyOptions);
        $event->setEntity($proxyEntity);

        $entityMetadata = new EntityMetadata($proxyEntity, $entityMapping);
        $this->entityManager->getEntityMetadataRegister()->addEntityMetadata($entityMetadata);
    }

    private function getPropertyProxyName(string $entityClass, string $property): string
    {
        return sprintf("\0%s\0%s", $entityClass, $property);
    }
}
