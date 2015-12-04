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

        $solrQuery = $query->getSearchString();
        $solrQuery .= ' AND id_lang:'.$context->getIdLang();
        $solrQuery .= ' AND id_shop:'.$context->getIdShop();

        $solariumQuery = $this->solarium->createSelect();
        $solariumQuery->setQuery($solrQuery);
        $solariumQuery->setStart(
            ($query->getPage() - 1) * $query->getResultsPerPage()
        );
        $solariumQuery->setRows($query->getResultsPerPage());

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
