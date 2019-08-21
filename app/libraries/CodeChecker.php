<?php
/**
 * CodeChecker Library
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class CodeChecker
{
     function __construct()
     {
          $GLOBALS = [];
          $_POST = [];
          $_GET = [];
          //$_SERVER = [];
          $_COOKIE = [];
          $_FILES = [];
     }

    public function run($filedir)
    {
        try {
            $error = php_check_syntax($filedir, true);
            if (is_array($error) && isset($error['line'])) {
                if ($error['line'] !== 1 && $error['msg'] !== "unexpected end of file") {
                    $output = "Parse Error: at line no " . $error['line'] . "<br>";
                    $output .= "Message: " . $error['msg'] . "<br>";
                    return $output;
                }
            }
            
            $error = php_check_runtime($filedir, true);
            if (is_array($error) && isset($error['line'])) {
                $output = "Error: at line no " . $error['line'] . "<br>";
                $output .= "Message: " . $error['method'] . " has been disabled for security reasons<br>";
                return $output;
            }
            
            ob_start();
            require_once($filedir);
            $output = ob_get_clean();
            return $output;
        }
        catch (Exception $e) {
            $output = $message = "Error: at line no " . $e->getLine() . "<br>";
            $output .= $message = "Message: " . $e->getMessage() . "<br>";
            return $output;
        }
        
        return false;
    }
}