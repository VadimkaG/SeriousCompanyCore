<?php
class PageRobots extends \PathExecutor {
	function response() {
		$data = $this->getData();
		if (!is_array($data)) $data = array();
		header('Content-type: text/plain');
		foreach ($data as $UserAgent=>$params) {
			if (!is_array($params)) continue;
			echo "User-agent: ".$UserAgent."\n";
			if (isset($params["Allow"]) && is_array($params["Allow"]))
				foreach($params["Allow"] as $link) {
					echo "Allow: ".$link."\n";
				}
			if (isset($params["Disallow"]) && is_array($params["Disallow"]))
				foreach($params["Disallow"] as $link) {
					echo "Disallow: ".$link."\n";
				}
			if (isset($params["Clean-param"]) && is_array($params["Clean-param"]))
				foreach($params["Clean-param"] as $param=>$link) {
					echo "Clean-param: ".$param." ".$link."\n";
				}
			if (isset($params["Sitemap"]))
				echo "Sitemap: ".$_SERVER["HTTP_HOST"].(string)$params["Sitemap"];
		}
	}
	/**
	 * Получить данные
	 * Переопределяемый метод
	 * @return array()
	 */
	function getData() {
		return getConfig("robots");
	}
}
