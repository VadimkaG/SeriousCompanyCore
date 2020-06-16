<?
namespace modules\admin;
class AdminPage extends \PathExecutor {
	/**
	 * Запустить обработку страницы
	 * Переопределяемый метод
	 */
	public function response() {
		
		require_once(__DIR__.'/Layout.class.php');
		require_once(__DIR__.'/Block.class.php');
		require_once(__DIR__.'/Table.class.php');
		require_once(__DIR__.'/Form.class.php');
		require_once(__DIR__.'/DataTransfer.class.php');
		require_once(__DIR__.'/PopupWindow.class.php');
		require_once(__DIR__.'/ContentAjax.class.php');
		require_once(__DIR__.'/AjaxDatalist.class.php');
		require_once(__DIR__.'/Pager.class.php');
		
		$page = new Layout($this->path);
		$this->prestruct($page);
		$page->setTitle($this->getTitle());
		$page->addFunction($this,'sucture');
		$page->response();
	}
	/**
	 * Подготовка к инициализации лайаута и шаблона страницы
	 * Переопределяемый метод
	 * @param $layout - Layout страницы
	 */
	public function prestruct(&$layout) {}
	/**
	 * Время для построения конткнта
	 * Переопределяемый метод
	 */
	public function sucture() {}
	
}
