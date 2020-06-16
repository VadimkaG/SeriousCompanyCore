<?
class XMLnode {
	
	protected $element;
	protected $child;
	protected $content;
	protected $attributes;
	
	public function __construct($element,$content = null) {
		require_once(__DIR__.'/Attributes.class.php');
		$this->attributes = new Attributes();
		$this->child = array();
		$this->element = (string)$element;
		$this->setContent($content);
	}
	/**
	 * Получить тип элемента
	 * @return string
	 */
	public function getElement() {
		return $this->element;
	}
	/**
	 * Установить тип элемента
	 * @param $element - string
	 * @return $this
	 */
	public function setElement($element) {
		$this->element = (string)$element;
		return $this;
	}
	/**
	 * Установить атрибут
	 * @param $key Ключ (string)
	 * @param $value - Значение (string)
	 * @return $this
	 */
	public function setAttr($key,$value) {
		$this->attributes->set((string)$key,(string)$value);
		return $this;
	}
	/**
	 * Добавить класс
	 * @param $class - string
	 */
	public function addClass($class) {
		$this->attributes->addClass((string)$class);
		return $this;
	}
	/**
	 * Получить атрибуты
	 * @return array()
	 */
	public function getAttr() {
		return $this->attributes;
	}
	/**
	 * Получить контент
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}
	/**
	 * Установить контент
	 * @param $content - string
	 * @return $this
	 */
	public function setContent($content) {
		if (!is_string($content) && $content == null)
			$this->content = null;
		else
			$this->content = (string)$content;
		return $this;
	}
	/**
	 * Получить наследников
	 * @return array()
	 */
	public function getChilds() {
		return $this->child;
	}
	/**
	 * Существует ли наследник с ключем
	 * @param $key - Ключ (string)
	 * @return boolean
	 */
	public function hasChild($key) {
		if (isset($this->child[(string)$key])) return true;
		return false;
	}
	/**
	 * Получить конкретного наследника
	 * @param $key - Идентификатор наследника (string)
	 */
	public function getChild($key) {
		if (isset($this->child[(string)$key]))
			return $this->child[(string)$key];
		else
			return null;
	}
	/**
	 * Добавить наследника
	 * @param $child
	 * @param $key - string, default null
	 */
	public function addChild($child,$key = null) {
		if ($key == null)
			return $this->child[] = $child;
		else 
			return $this->child[(string)$key] = $child;
	}
	/**
	 * Удалить наследника
	 * @param $key - string
	 * @return $this
	 */
	public function delChild($key) {
		unset($this->child[$key]);
		return $this;
	}
	/**
	 * Очистить список наследников
	 * @return $this
	 */
	public function clearChilds() {
		unset($this->child);
		$this->child = array();
		return $this;
	}
	/**
	 * Преобразовать в xml
	 * @return string
	 */
	public function __toString() {
		if (!is_string($this->content) && count($this->child) < 1)
			return "<" . $this->element . $this->attributes . " />";
		$child_str = "";
		foreach ($this->child as $item) {
			$child_str .= (string)$item;
		}
		return "<" . $this->element . $this->attributes . ">" . $child_str . $this->content . "</" . $this->element . ">";
	}
}
