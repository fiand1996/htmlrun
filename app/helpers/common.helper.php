<?php
/**
 * Common Helper
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */

/**
 * Special version of htmlspecialchars
 * @param  string $string 
 * @return string 
 */
function htmlchars($string = "")
{
    return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
}

/**
 * Delete file or folder (with content)
 * @param string $path Path to file or folder
 */
function delete($path)
{
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file) {
            delete(realpath($path) . '/' . $file);
        }

        return rmdir($path);
    } else if (is_file($path) === true) {
        return unlink($path);
    }

    return false;
}

/**
 * Get an array of timezones
 * @return array
 */
function getTimezones()
{
    $timezoneIdentifiers = DateTimeZone::listIdentifiers();
    $utcTime = new DateTime('now', new DateTimeZone('UTC'));
 
    $tempTimezones = array();
    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new DateTimeZone($timezoneIdentifier);
 
        $tempTimezones[] = array(
            'offset' => (int)$currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier
        );
    }
 
    // Sort the array by offset,identifier ascending
    usort($tempTimezones, function($a, $b) {
        return ($a['offset'] == $b['offset'])
            ? strcmp($a['identifier'], $b['identifier'])
            : $a['offset'] - $b['offset'];
    });
 
    $timezoneList = array();
    foreach ($tempTimezones as $tz) {
        $sign = ($tz['offset'] > 0) ? '+' : '-';
        $offset = gmdate('H:i', abs($tz['offset']));
        $timezoneList[$tz['identifier']] = '(UTC ' . $sign . $offset . ') ' .
            $tz['identifier'];
    }
 
    return $timezoneList;
}

/**
 * Validate date
 * @param  string  $date   date string
 * @param  string  $format 
 * @return boolean         
 */
function isValidDate($date, $format = 'Y-m-d H:i:s')
{
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * Create SEO friendly url slug from string
 * @param  string $string 
 * @return string         
 */
function url_slug($string = "", $dashes = "-")
{
    if (!is_string($string)) {
        $string = "";
    }

    $s = trim(mb_strtolower($string));
    
    // Replace azeri characters
    $s = str_replace(
        array("ü", "ö", "ğ", "ı", "ə", "ç", "ş"), 
        array("u", "o", "g", "i", "e", "c", "s"), 
        $s);
    
    // Replace cyrilic characters
    $cyr = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м',
                 'н','о','п','р','с','т','у', 'ф','х','ц','ч','ш','щ','ъ', 
                 'ы','ь', 'э', 'ю','я');
    $lat = array('a','b','v','g','d','e','io','zh','z','i','y','k','l',
                 'm','n','o','p','r','s','t','u', 'f', 'h', 'ts', 'ch',
                 'sh', 'sht', 'a', 'i', 'y', 'e','yu', 'ya');
    $s = str_replace($cyr, $lat, $s);

    // Replace all other characters
    $s = preg_replace("/[^a-z0-9]/", "$dashes", $s);

    // Replace consistent dashes
    $s = preg_replace("/-{2,}/", "$dashes", $s);

    return trim($s, "$dashes");
}

/**
 * Generate human readable random text
 * @param  integer $length length of the returned string
 * @return string          Random string
 */
function readableRandomString($length = 6)
{  
    $string     = '';
    $vowels     = array("a","e","i","o","u");  
    $consonants = array(
        'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 
        'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
    );  
    // Seed it
    srand((double) microtime() * 1000000);
    $max = $length/2;
    for ($i = 1; $i <= $max; $i++)
    {
        $string .= $consonants[rand(0,19)];
        $string .= $vowels[rand(0,4)];
    }
    return $string;
}

/**
 * Add a new option or update if it exists
 * @param string  $option_name  Name of the option
 * @param string|int  $option_value Value of the option
 */
function save_option($option_name, $option_value) 
{
    if (!is_string($option_name)) {
        // Option name must be string
        return false;
    }

    if ($option_value === false || $option_value === null) {
        $option_value = "";
    }

    // Save to the database
    $opt = \Controller::model("Option", $option_name);
    $opt->set("option_name", $option_name)
        ->set("option_value", $option_value)
        ->save();

    return true;
}

/**
 * Get the value of the given option
 * @param  string  $option_name   
 * @param  boolean $default_value If option is not available, 
 *                                then return $default_value
 * @return [mixed]                Either option value or $default_value
 */
function get_option($option_name, $default_value = false)
{
    if (!is_string($option_name)) {
        // Option name must be string
        return $default_value;
    }
    
    $opt = \Controller::model("Option", $option_name);
    if (!$opt->isAvailable()) {
        return $default_value;
    }

    // Return the value
    return $opt->get("option_value");
}

function show_404() {
     header("HTTP/1.0 404 Not Found");
     require_once(APPPATH.'/views/404.php');
     exit;
}

function exceptionHandler($exception) {

    // these are our templates
    $traceline = "#%s %s(%s): %s(%s)";
    $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

    // alter your trace as you please, here
    $trace = $exception->getTrace();
    foreach ($trace as $key => $stackPoint) {
        // I'm converting arguments to their type
        // (prevents passwords from ever getting logged as anything other than 'string')
        $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
    }

    // build your tracelines
    $result = array();
    foreach ($trace as $key => $stackPoint) {
        $result[] = sprintf(
            $traceline,
            $key,
            $stackPoint['file'],
            $stackPoint['line'],
            $stackPoint['function'],
            implode(', ', $stackPoint['args'])
        );
    }
    // trace always ends with {main}
    $result[] = '#' . ++$key . ' {main}';

    // write tracelines into main template
    $msg = sprintf(
        $msg,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        implode("\n", $result),
        $exception->getFile(),
        $exception->getLine()
    );

    // log or echo as you please
     error_log($msg , 3 , BASEPATH . "/storage/log/error.log");

}

function render_errors($messages){
     if ($messages){
          $html = '<div class=\'errorMsg\'><ul>';
          foreach ($messages as $msg){
               if(is_array($msg)){
                    foreach ($msg as $k=>$v){
                         $html .=  '<li>'. $v . '</li>';
                    }
               }else{
                    $html .=  $msg ;
               }
          }
          return $html .= '</ul></div>';     
     }         
     
     return '';
}

function random_string($length=6) {
     $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
     $code = "";
     while (strlen($code) < $length) {
          $code .= $chars[mt_rand(0,strlen($chars)-1)];
     }
     return $code;
}

function cleanScript($script) {
     //$script = str_replace('$GLOBALS', '$_POST', $script);
     //$script = str_replace('$_GET', '$_POST', $script);

     return trim($script);
}