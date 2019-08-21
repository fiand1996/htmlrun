<?php 
/**
 * Main App Core
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class App
{
    protected $router;
    protected $controller;
    protected $plugins;

    // An array of the URL routes
    protected static $routes = [];

    /**
     * summary
     */
    public function __construct()
    {
        $this->controller = new Controller;
    }

    /**
     * Adds a new route to the App:$routes static variable
     * App::$routes will be mapped on a route 
     * initializes on App initializes
     * 
     * Format: ["METHOD", "/uri/", "Controller"]
     * Example: App:addRoute("GET|POST", "/post/?", "Post");
     */
    public static function addRoute()
    {
        $route = func_get_args();
        if ($route) {
            self::$routes[] = $route;
        }
    }

    /**
     * Get App::$routes
     * @return array An array of the added routes
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Get IP info
     * @return stdClass 
     */
    private function ipinfo()
    {
        $client = empty($_SERVER['HTTP_CLIENT_IP']) 
                ? null : $_SERVER['HTTP_CLIENT_IP'];
        $forward = empty($_SERVER['HTTP_X_FORWARDED_FOR']) 
                 ? null : $_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = empty($_SERVER['REMOTE_ADDR']) 
                ? null : $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } else if (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }


        if (!isset($_SESSION[$ip])) {
            $res = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip), true);

            $ipinfo = [
                "request" => "", // Requested Ip Address
                "status" => "", // Status code (200 for success)
                "credit" => "",
                "city" => "",
                "region" => "",
                "areaCode" => "",
                "dmaCode" => "",
                "countryCode" => "",
                "countryName" => "",
                "continentCode" => "",
                "latitude" => "",
                "longitude" => "",
                "regionCode" => "",
                "regionName" => "",
                "currencyCode" => "",
                "currencySymbol" => "",
                "currencySymbol_UTF8" => "",
                "currencyConverter" => "",
                "timezone" => "", // Will be used only in registration
                                  // process to detect user's 
                                  // timezone automatically
                "neighbours" => [], // Neighbour country codes (ISO 3166-1 alpha-2)
                "languages" => [] // Spoken languages in the country
                                  // Will be user to auto-detect user language
            ];
            if (is_array($res)) {
                foreach ($res as $key => $value) {
                    $key = explode("_", $key, 2);
                    if (isset($key[1])) {
                        $ipinfo[$key[1]] = $value;
                    }
                }
            }

            $_SESSION[$ip] = $ipinfo;
        }

        return json_decode(json_encode($_SESSION[$ip]));
    }

    /**
     * Create database connection
     * @return App 
     */
    private function db()
    {
        $config = [
            'driver' => 'mysql', 
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => DB_ENCODING,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];

        new \Pixie\Connection('mysql', $config, 'DB');
        return $this;
    }


    /**
     * Check and get authorized user data
     * Define $AuthUser variable
     */
    private function auth()
    {
        $AuthUser = null;
        if (Input::cookie("nplh")) {
            $hash = explode(".", Input::cookie("nplh"), 2);

            if (count($hash) == 2) {
                $User = Controller::Model("User", (int)$hash[0]);

                if ($User->isAvailable() &&
                    $User->get("is_active") == 1 &&
                    md5($User->get("password")) == $hash[1]) 
                {
                    $AuthUser = $User;

                    if (Input::cookie("nplrmm")) {
                        setcookie("nplh", $User->get("id").".".md5($User->get("password")), time()+86400*30, "/");
                        setcookie("nplrmm", "1", time()+86400*30, "/");
                    }
                }
            }
        }

        return $AuthUser;
    }

    /**
     * Analize route and load proper controller
     * @return App
     */
    private function route()
    {
        // Initialize the router
        $router = new AltoRouter();
        $router->setBasePath(BASEROUTE);

        // Load plugin/theme routes first
        // TODO: Update router.map in modules to App::addRoute();
        $GLOBALS["_ROUTER_"] = $router;
        \Event::trigger("router.map", "_ROUTER_");
        $router = $GLOBALS["_ROUTER_"];

        // Load global routes
        include APPPATH."/config/routes.config.php";
        
        // Map the routes
        $router->addRoutes(App::getRoutes());

        // Match the route
        $route = $router->match();
        $route = json_decode(json_encode($route));

        if ($route) {
            if (is_array($route->target)) {
                require_once $route->target[0];
                $controller = $route->target[1];
            } else {
                $controller = $route->target."Controller";
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            $controller = "NotfoundController";
        }

        $this->controller = new $controller;
        $this->controller->setVariable("Route", $route);
    }

    /**
     * Process
     */
    public function process()
    {
        // Define global variables
        $GLOBALS["PaymentGateways"] = [];

        /**
         * Create database connection
         */
        $this->db();

        /**
         * Get IP Info
         */
        $IpInfo = $this->ipinfo();

        /**
         * Auth.
         */
        $AuthUser = $this->auth();

        /**
         * Analize the route
         */
        $this->route();
        $this->controller->setVariable("IpInfo", $IpInfo);
        $this->controller->setVariable("AuthUser", $AuthUser);
        $this->controller->index();
    }
}