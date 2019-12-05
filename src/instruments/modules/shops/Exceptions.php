<?PHP
namespace modules\shops;
if (!function_exists('loadModule')) die("Ошибка: Ядро не импортировано");
class ShopErrorException extends \Exception {}
class ShopQueryFailedException extends ShopErrorException {}
class ShopNotExistsException extends ShopErrorException {}
class ShopExistsException extends ShopErrorException {}
class GoodNotExistsException extends ShopErrorException {}
class AliasExistsException extends ShopErrorException {}
