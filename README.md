# roundcube-passwd-driver-gandi
Round Cube Passwd plugin Driver for GANDI API

## Prerequisites Configuration
To Configure this driver you need to setup the following variables inside your RoundCube Passwd Plugin `config.inc.php` file:
File to edit: ``$ROUND_CUBE_HOME/plugins/passwd/config.inc.php``
```
// Gandi Driver options
// ---------------------
// put token number from Gandi server
$config['password_gandi_api_apikey'] = 'yourapikey_from_gandi'; // put apikey number from Gandi server
$config['password_gandi_api_email_domain'] = 'yourdomain.fqdn'; // put domain name Gandi server
```

## Installation
1. Configure the prerequisites as described above inside your Passwd Plugin `config.inc.php`
2. Copy the file `gandi.php` inside `$ROUND_CUbE_HOME/plugins/passwd/drivers/`
3. Enable your Gandi Driver inside `config.inc.php` as per below:
``` 
// Password Plugin options 
// -----------------------
// A driver to use for password change. Default: "sql".
// See README file for list of supported driver names.
$config['password_driver'] = 'gandi'; 
```

## Optional configurations
You can also configure the minimum length of the password and a driver/function for checking its strength as per below (inside `config.inc.php`):
```
// A driver to use for checking password strength. Default: null (disabled).
// See README file for list of supported driver names.
$config['password_strength_driver'] = null;

// Determine whether current password is required to change password.
// Default: false.
$config['password_confirm_current'] = true;

// Require the new password to be a certain length.
// set to blank to allow passwords of any length
$config['password_minimum_length'] = 8;

// Require the new password to have at least the specified strength score.
// Note: Password strength is scored from 1 (week) to 5 (strong).
$config['password_minimum_score'] = 0;

// Enables logging of password changes into logs/password
$config['password_log'] = true;
```

That's all Folks!