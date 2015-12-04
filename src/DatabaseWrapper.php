<?php

namespace PrestaShop\PrestaShop\Module\SolrSearch;

use Exception;
use PDO;

class DatabaseWrapper
{
    private $pdo;
    private $tablesPrefix;

    public function __construct(PDO $pdo, $tablesPrefix)
    {
        $this->pdo = $pdo;
        $this->tablesPrefix = $tablesPrefix;
    }

    private function addTablesPrefix($sql)
    {
        return str_replace('ps_', $this->tablesPrefix, $sql);
    }

    public function query($sql, array $params = [])
    {
        $stm = $this->pdo->prepare($this->addTablesPrefix($sql));
        $ok  = $stm->execute($params);
        if (!$ok) {
            throw new Exception(implode(' ', $stm->errorInfo()));
        }
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
}
