<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\QueryBus\Query\Currency\FetchAllCurrenciesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Shop\Currency;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllCurrenciesQueryHandler
 */
class FetchAllCurrenciesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCurrenciesQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Currency::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllCurrenciesQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $query = $this->createCurrenciesQuery();

        $currencies = array_map(function ($currency) {
            return $this->responseParser->parse($currency);
        }, $query->getArrayResult());

        return array_filter($currencies);
    }

    /**
     * @return Query
     */
    private function createCurrenciesQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('currencies');
        $queryBuilder->select([
            'currencies.id as id',
            'currencies.name as name',
            'currencies.currency as currency'
        ]);

        $query = $queryBuilder->getQuery();
        $query->execute();

        return $query;
    }
}