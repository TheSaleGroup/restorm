<?php
/*
 * The MIT License
 *
 * Copyright 2017 Rob Treacy <email@roberttreacy.com>.
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

namespace Robwasripped\Restorm\Configuration;

use Symfony\Component\Yaml\Yaml;
use Robwasripped\Restorm\Mapping\EntityMappingRegister;
use Robwasripped\Restorm\Mapping\EntityMapping;
use Robwasripped\Restorm\Connection\ConnectionRegister;
use Robwasripped\Restorm\Connection\GuzzleConnection;
use Robwasripped\Restorm\Mapping\EntityBuilder;

/**
 * Description of Configuration
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class Configuration
{
    /**
     * @var Configuration
     */
    private static $instance;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var EntityBuilder
     */
    private $entityBuilder;

    /**
     * @var EntityMappingRegister
     */
    private $entityMappingRegister;

    /**
     * @var ConnectionRegister
     */
    private $connectionRegister;

    private function __construct(array $configuration)
    {
        $this->configuration = $configuration;

        $this->initialise();
    }

    public static function buildFromYaml(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception(sprintf('The configuration file "%s" cannot be found.', $filePath));
        }

        $configurationArray = Yaml::parse(file_get_contents($filePath), Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE);

        return self::$instance = new Configuration($configurationArray);
    }

    private function initialise()
    {
        $this->entityMappingRegister = $this->buildEntityMappingRegister();
        $this->entityBuilder = $this->buildEntityBuilder();
        $this->connectionRegister = $this->buildConnectionRegister();
    }

    function getEntityBuilder(): EntityBuilder
    {
        return $this->entityBuilder;
    }

    public function getEntityMappingRegister(): EntityMappingRegister
    {
        return $this->entityMappingRegister;
    }

    public function getConnectionRegister(): ConnectionRegister
    {
        return $this->connectionRegister;
    }

    private function buildEntityBuilder(): EntityBuilder
    {
        return new EntityBuilder($this->entityMappingRegister);
    }

    private function buildEntityMappingRegister(): EntityMappingRegister
    {
        $entityMappingRegister = new EntityMappingRegister;

        $entityMappingConfigurations = $this->configuration['entity_mappings'] ?? [];

        foreach ($entityMappingConfigurations as $entityClass => $entityConfiguration) {
            $entityMapping = new EntityMapping($entityClass, $entityConfiguration['repository_class'], $entityConfiguration['properties'], $entityConfiguration['paths'], $entityConfiguration['connection']);

            $entityMappingRegister->addEntityMapping($entityMapping);
        }

        return $entityMappingRegister;
    }

    private function buildConnectionRegister(): ConnectionRegister
    {
        $connectionRegister = new ConnectionRegister;

        $connectionConfigurations = $this->configuration['connections'] ?? [];

        foreach ($connectionConfigurations as $connectionName => $connectionConfiguration) {
            $connection = new GuzzleConnection($connectionConfiguration);
            $connectionRegister->registerConnection($connectionName, $connection);
        }

        return $connectionRegister;
    }
}
