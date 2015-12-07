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

    public function query($sql, array $params = [], callable $withRow = null)
    {
        $stm = $this->pdo->prepare($this->addTablesPrefix($sql));
        $ok  = $stm->execute($params);
        if (!$ok) {
            throw new Exception(implode(' ', $stm->errorInfo()));
        }
        if (null !== $withRow) {
            while (($row = $stm->fetch(PDO::FETCH_ASSOC))) {
                $withRow($row);
            }
        } else {
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
