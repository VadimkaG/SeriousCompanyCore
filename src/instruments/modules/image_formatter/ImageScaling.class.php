<?php
namespace modules\image_formatter;
class ImageScaling extends \PathExecutor {

	private $MODE;
	private $W;
	private $H;
	private $IMAGE_PATH; 
	private $IMAGE_TYPE; 

	public function getTitle() {
		return "Изображение";
	}
	public function validate() {
		$path_arr = array();
		if ($this->path->getParent() != null && $this->path->getParent()->executor() instanceof ImageScaling) {
			$path_arr = array($this->path->getAlias());
			$parent = $this->path->getParent();
			if ($parent->executor() instanceof ImageScaling) {
				if ($parent->getParent()->executor() instanceof ImageScaling)
					$path_arr[] = $parent->getAlias();
				$i = 0;
				while (($parent = $parent->getParent())->executor() instanceof ImageScaling) {
					if ($i > 100) break;
					if ($parent->getParent()->executor() instanceof ImageScaling)
						$path_arr[] = $parent->getAlias();
					$i++;
				}
			}
		}
		$mode_ind = count($path_arr)-1;
		if ($mode_ind < 1) throw new \PathNotValidatedException("Не верное использование.");
		$mode = $path_arr[$mode_ind];

		$mode = explode("_",$mode,2);
		$this->MODE = $mode[0];
		if (count($mode) < 2) throw new \PathNotValidatedException("Не верное использование параметра функции.");
		$scaleRaw = explode("x",$mode[1],2);
		if (count($scaleRaw) > 1) {
			$this->W = (int)$scaleRaw[0];
			$this->H = (int)$scaleRaw[1];
		} else {
			$this->W = (int)$mode[1];
			$this->H = NULL;
		}
		unset($mode);
		unset($scaleRaw);
		unset($path_arr[$mode_ind]);

		$this->IMAGE_PATH = "";
		foreach ($path_arr as $alias) {
			$this->IMAGE_PATH = "/".$alias.$this->IMAGE_PATH;
		}
		if ($this->IMAGE_PATH == "") $this->IMAGE_PATH = "/";
		if (!file_exists(root.$this->IMAGE_PATH))
			return false;
		$this->IMAGE_TYPE = getimagesize(root.$this->IMAGE_PATH);
		if ($this->IMAGE_TYPE[2] != IMAGETYPE_JPEG && $this->IMAGE_TYPE[2] != IMAGETYPE_PNG) throw new \PathNotValidatedException("Не удалось определить тип изображения.");
		return true;
	}
	public function response() {
		$image = null;
		switch ($this->IMAGE_TYPE[2]) {
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg(root.$this->IMAGE_PATH);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng(root.$this->IMAGE_PATH);
				break;
		}

		switch ($this->MODE) {
			/**
			 * Масштабировать изображение до определенных размеров
			 * Если указать только ширину, то высота подберется автоматически
			 */
			case "s":
				if ($this->H == null) {
					$newSize = $this->scaleByWidth($this->W,$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
					$newImage = imagecreatetruecolor($newSize[0],$newSize[1]);
					imagecopyresampled($newImage,$image,0,0,0,0,$newSize[0],$newSize[1],$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
					imagedestroy($image);
					$image = &$newImage;
				} else {
					$newImage = imagecreatetruecolor($this->W,$this->H);
					imagecopyresampled($newImage,$image,0,0,0,0,$this->W,$this->H,$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
					imagedestroy($image);
					$image = &$newImage;
				}
				break;
			/**
			 * Если изображение больше заданных размеров, то масштабировать его
			 * Если указать только ширину, то высота подберется автоматически
			 */
			case "sm":
				$newSize = null;
				if (
					( $this->H == null && $this->IMAGE_TYPE[0] > $this->W )
					||
					( $this->IMAGE_TYPE[0] > $this->W && $this->IMAGE_TYPE[1] <= $this->H )
					||
					( $this->H != null && $this->IMAGE_TYPE[0] > $this->W && $this->IMAGE_TYPE[1] > $this->H )
				) {
					$newSize = $this->scaleByWidth($this->W,$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
					if ($this->H != null && $newSize[1] > $this->H)
						$newSize = $this->scaleByHeight($this->H,$newSize[0],$newSize[1]);
				} elseif ($this->H != null && $this->IMAGE_TYPE[0] <= $this->W && $this->IMAGE_TYPE[1] > $this->H)
					$newSize = $this->scaleByHeight($this->H,$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
				if ($newSize) {
					$newImage = imagecreatetruecolor($newSize[0],$newSize[1]);
					imagecopyresampled($newImage,$image,0,0,0,0,$newSize[0],$newSize[1],$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
					imagedestroy($image);
					$image = &$newImage;
				}
				break;
			/**
			 * Если изображение больше заданных размеров, то масштабировать его
			 * Указывается только высота.
			 */
			case "smh":
				if ($this->IMAGE_TYPE[1] <= $this->W) break;
			/**
			 * Масштабировать изображение до определенных размеров
			 * Указываться только высота.
			 */
			case "sh":
				$newSize = $this->scaleByHeight($this->W,$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
				$newImage = imagecreatetruecolor($newSize[0],$newSize[1]);
				imagecopyresampled($newImage,$image,0,0,0,0,$newSize[0],$newSize[1],$this->IMAGE_TYPE[0],$this->IMAGE_TYPE[1]);
				imagedestroy($image);
				$image = &$newImage;
		}
		/**/

		if ($image != null)
		switch ($this->IMAGE_TYPE[2]) {
			case IMAGETYPE_JPEG:
				header('Content-Type: '.$this->IMAGE_TYPE["mime"]);
				imagejpeg($image,null,75);
				imagedestroy($image);
				break;
			case IMAGETYPE_PNG:
				header('Content-Type: '.$this->IMAGE_TYPE["mime"]);
				imagepng($image,null,75);
				imagedestroy($image);
				break;
		}
	}
	/**
	 * Уменьшить изображение до определенной ширины
	 */
	private function scaleByWidth($width,$image_width,$image_height) {
		$coefficienWidth = $image_width / $width;
		$height = $image_height / $coefficienWidth;
		$size = array($width,$height);
		$size["width"] = &$size[0];
		$size["height"] = &$size[1];
		return $size;
	}
	/**
	 * Уменьшить изображение до определенной высоты
	 */
	private function scaleByHeight($height,$image_width,$image_height) {
		$coefficienHeight = $image_height / $height;
		$width = $image_width / $coefficienHeight;
		$size = array($width,$height);
		$size["width"] = &$size[0];
		$size["height"] = &$size[1];
		return $size;
	}
}
