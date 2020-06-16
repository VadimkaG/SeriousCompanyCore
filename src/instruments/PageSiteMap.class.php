<?php
class PageSiteMap extends \PathExecutor {
	function response() {
		$xml = new \XMLnode("urlset");
		$xml->setAttr("xmlns","http://www.sitemaps.org/schemas/sitemap/0.9");
		$links = $this->getData();
		if (!is_array($links)) $links = array();
		foreach ($links as $params) {
			if (is_array($params)) {
				$url = $xml->addChild(new \XMLnode("url"));
				foreach ($params as $key=>$param) {
					if ($key == "loc") $param = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].$param;
					$url->addChild(new \XMLnode($key,$param));
				}
			}
		}
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
	}
	/**
	 * Получить данные
	 * Переопределяемый метод
	 * @return array()
	 */
	function getData() {
		return getConfig("sitemap");
	}
}
