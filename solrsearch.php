<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

require implode(DIRECTORY_SEPARATOR, [
	__DIR__, 'vendor', 'autoload.php'
]);

use Solarium\Client as SolariumClient;

class SolrSearch extends Module
{
	private $db;

	public function __construct()
	{
		$this->name = 'solrsearch';
		$this->tab = 'search_filter';
		$this->version = '2.0.0';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Quick search with solr');
		$this->description = $this->l('Use solr to search your catalog.');
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

		$hostAndPort = explode(':', _DB_SERVER_);
		$dsn = [
			'mysql:dbname='._DB_NAME_,
			'host='.$hostAndPort[0]
		];
		if (isset($hostAndPort[1])) {
			$dsn[] = 'post='.$hostAndPort[1];
		}

		$pdo = new PDO(implode(';', $dsn), _DB_USER_, _DB_PASSWD_);

		$this->db = new PrestaShop\PrestaShop\Module\SolrSearch\DatabaseWrapper(
			$pdo,
			_DB_PREFIX_
		);
	}

	public function install()
	{
		return parent::install() && $this->registerHook('productSearchProvider') && $this->registerHook('afterSaveProduct');
	}

	public function hookAfterSaveProduct(array $product)
	{
		if (isset($product['id_product'])) {
			$this->doIndex([$product['id_product']]);
		}
	}

	private function testSolrConnection()
	{
		$client = new SolariumClient($this->getSolrConfig());
		$ping = $client->createPing();
		try {
			$client->ping($ping);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function getContent()
	{
		$defaults = ['action' => null];
		$params = Tools::getValue('solrsearch');
		if (!is_array($params)) {
			$params = [];
		}
		$params = array_merge($defaults, $params);

		$errors = [];
		$successes = [];

		if ('reindex' === $params['action']) {
			if ($this->reindexAction()) {
				$successes[] = $this->l('Yay, successfully indexed all products!');
			} else {
				$errors[] = $this->l('Bloody hell it did not work!');
			}
		}

		if (($solrConfig = Tools::getValue('solrConfig'))) {
			$this->storeSolrConfig($solrConfig);
		}

		$fieldsForSchema = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'solr-schema-fields.xml');
		$this->smarty->assign('fieldsForSchema', $fieldsForSchema);
		$this->smarty->assign('solrConfig', $this->retrieveSolrConfig());

		$solrConnectionOK = $this->testSolrConnection();
		$solrSchemaOK = $this->getIndexer()->testSchema();
		$solrOK = $solrConnectionOK && $solrSchemaOK;

		Configuration::updateValue('SOLRSEARCH_SOLR_READY', $solrOK);

		$this->smarty->assign('solrConnectionOK', $solrConnectionOK);
		$this->smarty->assign('solrSchemaOK', $solrSchemaOK);
		$this->smarty->assign('solrOK', $solrOK);
		$this->smarty->assign('errors', $errors);
		$this->smarty->assign('successes', $successes);

		return $this->display(__FILE__, 'views/configuration.tpl');
	}

	private function storeSolrConfig(array $conf)
	{
		Configuration::updateValue('SOLRSEARCH_SOLR_HOST', $conf['host']);
		Configuration::updateValue('SOLRSEARCH_SOLR_PORT', $conf['port']);
		Configuration::updateValue('SOLRSEARCH_SOLR_PATH', $conf['path']);
		Configuration::updateValue('SOLRSEARCH_SOLR_CORE', $conf['core']);
		return $conf;
	}

	private function retrieveSolrConfig()
	{
		$defaults = [
			'host' => '127.0.0.1',
			'port' => 8080,
			'path' => '/solr/',
			'core' => null
		];

		$actual = [
			'host' => Configuration::get('SOLRSEARCH_SOLR_HOST'),
			'port' => (int)Configuration::get('SOLRSEARCH_SOLR_PORT'),
			'path' => Configuration::get('SOLRSEARCH_SOLR_PATH'),
			'core' => Configuration::get('SOLRSEARCH_SOLR_CORE')
		];

		$conf = $defaults;

		foreach ($actual as $key => $value) {
			if ($value) {
				$conf[$key] = $value;
			}
		}

		return $conf;
	}

	private function getSolrConfig()
	{
		return [
			'endpoint' => [
				'localhost' => $this->retrieveSolrConfig()
			]
		];
	}

	private function getIndexer()
	{
		return new PrestaShop\PrestaShop\Module\SolrSearch\Indexer(
			$this->db,
			$this->getSolrConfig()
		);
	}

    private function doIndex(array $id_products = [])
    {
		if (Configuration::get('SOLRSEARCH_SOLR_READY')) {
			try {
				$this->getIndexer()->index($id_products);
				return true;
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
    }

	public function reindexAction()
	{
		return $this->doIndex();
	}

	public function hookProductSearchProvider($params)
	{
		$query = $params['query'];
		if ($query->getSearchString()) {
			return new PrestaShop\PrestaShop\Module\SolrSearch\SearchProvider(
				$this->getSolrConfig()
			);
		}
	}
}
