<?PHP
function getPath() {
	return content.'/files';
}
function countFiles() {
	return count(scandir(getPath()))-2;
}
function listFiles($page=1,$perPage=30) {
	if ($page < 1) $page = 1;
	$end = $page * $perPage;
	$start = $end - $perPage;
	$i = 0;
	$list = array();
	foreach (scandir(getPath()) as $file) {
		if ($file == '.' || $file == '..') continue;
		if ($i < $start) {
			$i++;
			continue;
		}
		if ($i >= $end) continue;
		$list[$file]['name'] = $file;
		$list[$file]['path'] = getPath().'/'.$file;
		$list[$file]['size'] = filesize(getPath().'/'.$file);
		$i++;
	}
	return $list;
}
?>
