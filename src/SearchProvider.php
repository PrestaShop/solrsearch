<?php

namespace PrestaShop\PrestaShop\Module\SolrSearch;

use Solarium\Client as SolariumClient;
use PrestaShop\PrestaShop\Core\Business\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Business\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Business\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Business\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Business\Product\Search\PaginationResult;

class SearchProvider implements ProductSearchProviderInterface
{
    private $solarium;

    public function __construct(array $solrConfig)
    {
        $this->solarium = new SolariumClient($solrConfig);
    }

    public function runQuery(
        ProductSearchContext $context,
        ProductSearchQuery $query
    ) {
        $result = new ProductSearchResult;
        $pagination = new PaginationResult;

        $str = $query->getSearchString();

        $solariumQuery = $this->solarium->createSelect();
        $solariumQuery->setQuery($str);
        $solariumResult = $this->solarium->select($solariumQuery);

        $documents = $solariumResult->getDocuments();

        $result->setProducts(array_map(function ($doc) {
            return ['id_product' => $doc->id_product];
        }, $documents));

        $pagination->setTotalResultsCount($solariumResult->getNumFound());
        $pagination->setResultsCount(count($documents));

        $result->setPaginationResult($pagination);
        return $result;
    }
}
