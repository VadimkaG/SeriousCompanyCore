<?PHP
namespace modules\accounts;
class AuthFailedException extends \Exception {}
class AccountNotFoundException extends AuthFailedException {}
class AccountExistsException extends AuthFailedException {}
class NotEnoughAdminsException extends AuthFailedException {}
class PermissionExistsException extends AuthFailedException {}
class GroupNotFoundException extends AuthFailedException {}
class AccountCryptException extends AuthFailedException {}
