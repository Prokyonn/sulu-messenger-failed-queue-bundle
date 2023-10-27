<?php

declare(strict_types=1);

namespace Tailr\SuluMessengerFailedQueueBundle\Infrastructure\Doctrine\Repository;

use Doctrine\DBAL\Query;
use Doctrine\ORM\EntityManagerInterface;

use Tailr\SuluMessengerFailedQueueBundle\Domain\Query\SearchCriteria;
use Tailr\SuluMessengerFailedQueueBundle\Domain\Repository\FailedQueueRepositoryInterface;

use function Psl\Str\Byte\lowercase;
use function Psl\Str\is_empty;

final class DoctrineFailedQueueRepository implements FailedQueueRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $tableName = 'messenger_messages',
    ) {
    }

    public function findMessageIds(SearchCriteria $criteria): array
    {
        $queryBuilder = $this->buildQuery($criteria);
        $queryBuilder->setMaxResults($criteria->limit());
        $queryBuilder->setFirstResult($criteria->offset());
        $queryBuilder->orderBy(
            'm.'.('createdAt' === $criteria->sortColumn()) ? 'created_at' : $criteria->sortColumn(),
            $criteria->sortDirection()
        );

        /** @var int[] $result */
        $result = $queryBuilder->executeQuery()->fetchFirstColumn();

        return $result;
    }

    public function count(SearchCriteria $criteria): int
    {
        $queryBuilder = $this->buildQuery($criteria);
        $queryBuilder->resetQueryPart('select')->select('count(m.id)');

        return (int) $queryBuilder->executeQuery()->fetchOne();
    }

    private function buildQuery(SearchCriteria $criteria): Query\QueryBuilder
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder->select('m.id')
            ->from($this->tableName, 'm')
            ->andWhere($queryBuilder->expr()->eq('queue_name', ':queueName'))
            ->setParameter('queueName', 'failed');
        if (!is_empty($searchString = $criteria->searchString())) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('lower(m.body)', ':search'))
                ->setParameter('search', '%'.lowercase($searchString).'%');
        }

        return $queryBuilder;
    }
}