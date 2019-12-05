<?PHP
namespace modules\phpmailer;
if (!function_exists('loadModule') || !function_exists('loadInstuments')) die("Ошибка: Ядро не импортировано");
if (!class_exists('\modules\Module')) loadInstuments('module');
if (!class_exists('\modules\Module')) die("Ошибка: Система модулей не инициализирована");
require_once __DIR__.'/PHPMailer.php';
require_once __DIR__.'/SMTP.php';
require_once __DIR__.'/Exception.php';
class index extends \modules\Module {
	const VERSION = '1.0';
	private $mailconfig;
	public function install(&$db,&$tn) {
		
	}
	function init() {
		$this->mailconfig = getConfig("phpmailer");
		if (
			!isset($this->mailconfig["server"])
			||
			!isset($this->mailconfig["auth_username"])
			||
			!isset($this->mailconfig["auth_password"])
			||
			!isset($this->mailconfig["secure"])
			||
			!isset($this->mailconfig["port"])
		) return false;
		return true;
	}
	/**
	 * Отправляет письмо на почту
	 * @param $html - Тело письма
	 * @param $title - Заголовок письма
	 * @param $recipients - array - Получатели
	 * @return boolean - Отправилось ли сообщение
	 */
	function send($html, $title, $recipients) {
		if (is_array($recipients) && count($recipients) < 1) return true;
		else if (!is_string($recipients)) return false;
		$mail = new \PHPMailer\PHPMailer\PHPMailer();
		try {
			
			// Для отладки
			//$mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
			
			$mail->isSMTP();
			$mail->CharSet = "UTF-8";
			$mail->SMTPAuth = true;
			
			$mail->Host       = $this->mailconfig["server"];
			$mail->Username   = $this->mailconfig["auth_username"];
			$mail->Password   = $this->mailconfig["auth_password"];
			$mail->SMTPSecure = $this->mailconfig["secure"];
			$mail->Port       = $this->mailconfig["port"];
			
			if (isset($this->mailconfig["name"]) && $this->mailconfig["name"] != "") {
				if ($this->mailconfig["name"] == "%TITLE%") $this->mailconfig["name"] = $this->config['Title'];
				$mail->setFrom($this->mailconfig["auth_username"], $this->mailconfig["name"]);
			} else
				$mail->setFrom($this->mailconfig["auth_username"]);
			
			if (is_array($recipients))
				foreach ($recipients as $recipient) {
					if (!is_string($recipient)) continue;
					$mail->addAddress($recipient);
				}
			else
				$mail->addAddress($recipients);
			
			$mail->isHTML(true);
			$mail->Subject = $title;
			$mail->Body    = $html;
			
			if ($mail->send()) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}
}
