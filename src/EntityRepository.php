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

namespace Robwasripped\Restorm;

use Robwasripped\Restorm\EntityManager;
use Robwasripped\Restorm\Query\QueryBuilder;

/**
 * Description of Repository
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class EntityRepository implements RepositoryInterface
{
    /**
     *
     * @var EntityManager
     */
    protected $entityManager;
    
    /**
     * @var string
     */
    protected $entityClass;

    public function __construct(EntityManager $entityManager, string $entityClass)
    {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
    }

    public function find(array $filters, $offset = 0, $limit = null)
    {
        ;
    }

    public function findOne($id)
    {
        $entityMapping = $this->entityManager->getEntityMappingRegister()->getEntityMapping($this->entityClass);
        
        $identifierField = $entityMapping->getIdentifierName();
        
        $query = $this->getQueryBuilder()
            ->get($this->entityClass)
            ->where(['id' => $id])
            ->getQuery();
        
        var_dump($query);
    }

    public function findAll($offset = 0, $limit = null)
    {
        ;
    }
    
    protected function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->entityManager->getConnectionRegister(), $this->entityManager->getEntityMappingRegister());
    }
}
