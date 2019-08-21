<?php
/**
 * Index Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class IndexController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $Snippet = Controller::model("Snippet", null);

        $this->setVariable("Snippet", $Snippet);

        if (Input::post("action") == "run") {
            $this->run();
        } elseif (Input::post("action") == "save") {
            $this->save();
        }
        $this->view("editor", "site");
    }

    private function run()
    {
        $IpInfo = $this->getVariable("IpInfo");
        $filename = md5(Input::server("HTTP_USER_AGENT") . $IpInfo->request . date("Y-m-d"));

        $basepath = BASEPATH . "/storage/temp/snippets/" . $filename . "/";

        if (!file_exists($basepath)) {
            mkdir($basepath);
        } 

        @file_put_contents($basepath . "htmlextemp+", Input::post("html"));
        @file_put_contents($basepath . "cssextemp+", Input::post("css"));
        @file_put_contents($basepath . "jsextemp+", Input::post("js"));

        $template = @file_get_contents(APPPATH . "/inc/template.inc.php");

        $search = [
            '{{html}}', '{{css}}', '{{js}}', '{{resources}}',
        ];

        $resources = Input::post("resources") ? Input::post("resources") : [];
        $resourceslist = "";

        foreach ($resources as $resource) {
            if (stripos($resource, ".css") !== false) {
                $resourceslist .= '<link href="' . $resource . '" rel="stylesheet" type="text/css" />' . "\n";
            } else if (stripos($resource, ".js") !== false) {
                $resourceslist .= '<script type="text/javascript" src="' . $resource . '"></script>' . "\n";
            }
        }

        $replace = [
            Input::post("html"),
            Input::post("css"),
            Input::post("js"),
            $resourceslist,
        ];

        $template = str_replace($search, $replace, $template);
        @file_put_contents($basepath . "renderextemp+", cleanScript($template));

        $this->resp->status = 1;
        $this->resp->result = APPURL . "/view/" . $filename;
        $this->jsonecho();
    }

    private function save()
    {
        $AuthUser = $this->getVariable("AuthUser");

        if (!$AuthUser) {
            $this->resp->message = "Unauthorized!";
            $this->jsonecho();
        }

        $filename = random_string(15);

        $basepath = BASEPATH . "/storage/snippets/" . $filename . "/";

        if (!file_exists($basepath)) {
            mkdir($basepath);
        } 

        @file_put_contents($basepath . "htmlext+", Input::post("html"));
        @file_put_contents($basepath . "cssext+", Input::post("css"));
        @file_put_contents($basepath . "jsext+", Input::post("js"));

        $template = @file_get_contents(APPPATH . "/inc/template.inc.php");

        $search = [
            '{{html}}', '{{css}}', '{{js}}', '{{resources}}',
        ];

        $resources = Input::post("resources") ? Input::post("resources") : [];
        $resourceslist = "";

        foreach ($resources as $resource) {
            if (stripos($resource, ".css") !== false) {
                $resourceslist .= '<link href="' . $resource . '" rel="stylesheet" type="text/css" />';
            } else if (stripos($resource, ".js") !== false) {
                $resourceslist .= '<script type="text/javascript" src="' . $resource . '"></script>';
            }
        }

        $replace = [
            Input::post("html"),
            Input::post("css"),
            Input::post("js"),
            $resourceslist,
        ];

        $template = str_replace($search, $replace, $template);
        @file_put_contents($basepath . "renderext+", cleanScript($template));

        $title = Input::post("title") ? Input::post("title") : $filename;
        $Snippet = Controller::model("Snippet");
        
        $Snippet->set("user_id", $AuthUser->get("id"))
                 ->set("filename", $filename)
                 ->set("filepath", $basepath . "renderext+")
                 ->set("title", $title)
                 ->set("resources", json_encode($resources))
                 ->set("description", Input::post("description"))
                 ->save();

        $this->resp->status = 1;
        $this->resp->result = APPURL . "/editor/" . $filename;
        $this->jsonecho();
    }
}
