<?PHP
/**
 * Сохранить данные в базу
 * Эти данные автоматически подгружаются в
 * $config при инициализации ядра
 * @name - Имя переменной
 * @value - Значение (текст)
 */
function saveSetting($name,$value) {
	global $config;
	$out=$config['data']->query("select Count(*) from ".$config['tablenames']['SiteConfig']." where option_name='".trim($name)."'");
	$row = $out->fetch_assoc();
	if ($row['Count(*)']!=0) {
		$config['data']->query("update ".$config['tablenames']['SiteConfig']." set option_value='".$value."' where option_name='".trim($name)."'");
	} else {
		$config['data']->query("insert into ".['tablenames']['SiteConfig']." (option_name,option_value) values ('".$name."','".$value."')");
	}
}
/**
 * Список настроек
 * @return array
 */
function listSettings() {
	global $config;
	$out=$config['data']->query("select * from ".$config['tablenames']['SiteConfig']);
	$list = array();
	while ($row = $out->fetch_array(MYSQLI_ASSOC)) {
		$list[] = $row;
	}
	return $list;
}
