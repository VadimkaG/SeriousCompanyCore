<?PHP
namespace modules\accounts;
if (!function_exists('loadModule')) die("Ошибка: Ядро не импортировано");
class AuthFailedException extends \Exception {}
class AccountQueryFailedException extends AuthFailedException {}
class AccountNotFoundException extends AuthFailedException {}
class AccountExistsException extends AuthFailedException {}
class NotEnoughAdminsException extends AuthFailedException {}
class PermissionExistsException extends AuthFailedException {}
class GroupNotFoundException extends AuthFailedException {}
class AccountCryptException extends AuthFailedException {}
