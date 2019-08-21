<?php 

// Default timezone
date_default_timezone_set("UTC"); 

// Defaullt multibyte encoding
mb_internal_encoding("UTF-8"); 

// Application version name
define("APP_VERSION", "4.2.0"); 

// ASCII Secure random crypto key
define("CRYPTO_KEY", "def00000d459ad4482a1a6d23a907db6c65c81fd9");

// General purpose salt
define("SALT", "7FwupCc5YpXJtnsz");

// Path to instagram sessions directory
define("SESSIONS_PATH", BASEPATH . "/storage/sessions");

// Path to temporary files directory
define("TEMP_PATH", ROOTPATH . "/assets/uploads/temp");

// Path to php executable  usr/bin/php or F:\wamp\bin\php\php5.3.9
define("PHP_PATH", "E:\Laragon\bin\php\php-7.2.11-Win32-VC15-x64\php"); 

// Extension of php file
define("FILE_EXTENSION", ".exphp+");

// Extension of php temp file
define("TEMP_EXTENSION", ".extemp+");
