<?php

namespace PrestaShop\PrestaShop\Module\SolrSearch;

class Indexer
{
    private $db;

    public function __construct(DatabaseWrapper $db)
    {
        $this->db = $db;
    }

    public function index()
    {

    }
}
