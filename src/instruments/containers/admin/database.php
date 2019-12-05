<?
namespace page\containers\Admin;
class Database extends \page\Container {
	
	private $requestHeader = array();
	private $request = array();
	
	public function init() {
		if (isset($_POST['SQL']) && $_POST['SQL'] != '') {
			$query = $this->config['data']->query($_POST['SQL']);
			if ($query != null && !is_bool($query)) {
				$first = true;
				while ($arr = $query->fetch_array(MYSQLI_ASSOC)) {
					$this->request[] = $arr;
					if ($first == true) {
						foreach ($arr as $key=>$val) {
							$this->requestHeader[] = $key;
						}
						$first = false;
					}
				}
			}
		}
		require_once(containers.'/admin/layout.php');
		$page = new Layout('layout');
		$page->addFunction($this,'proc',array('last'=>$this->config['data']->getLastQuery(), 'error'=>$this->config['data']->getError()));
		$page->setTitle("База данных");
		$page->init();
	}
	function Block_line($func,&$c,&$data) {
		if (is_array($data)) {
			foreach($data as $d) {
				$c->$func(array('content'=>$d));
			}
		}
	}
	function Block_request($func,&$c,&$data) {
		$c->$func(array(),$this->requestHeader);
		foreach($this->request as $line) {
			$c->$func(array(),$line);
		}
	}
	function Block_answear($func,&$c,&$data) {
		if (count($this->requestHeader) > 0)
			$c->$func();
	}
}
