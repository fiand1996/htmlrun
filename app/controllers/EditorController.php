<?php
/**
 * Editor Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class EditorController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $Route = $this->getVariable("Route");
        $filename = $Route->params->filename;

        $basepath = BASEPATH . "/storage/snippets/" . $filename . "/";

        $Snippet = Controller::model("Snippet", $filename);

        if (!is_file($basepath . "renderext+") || !$Snippet->isAvailable()) {
            show_404();
        }

        $User = Controller::model("User", $Snippet->get("user_id"));
        $AuthUser = $this->getVariable("AuthUser");

        $user_id = $AuthUser ? $AuthUser->get("id") : null;
        
        if ($Snippet->get("user_id") !== $user_id && 
            $Snippet->get("is_public") == 0) {
            show_404();
        }

        $html = @file_get_contents($basepath . "htmlext+");
        $css = @file_get_contents($basepath . "cssext+");
        $js = @file_get_contents($basepath . "jsext+");

        $Snippets = Controller::model("Snippets");

        $this->setVariable("html", htmlspecialchars($html))
            ->setVariable("css", htmlspecialchars($css))
            ->setVariable("js", htmlspecialchars($js))
            ->setVariable("Snippet", $Snippet)
            ->setVariable("User", $User);

        switch (Input::post("action")) {
            case "run":
                $this->run();
                break;
            case "autorun":
                $this->autoRun();
                break;
            case "update":
                $this->update();
                break;
            case "updateinfo":
                $this->updateInfo();
                break;
            default:
                break;
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

    private function autoRun()
    {
        $Snippet = $this->getVariable("Snippet");

        $this->resp->status = 1;
        $this->resp->result = APPURL . "/view/" . $Snippet->get("filename");
        $this->jsonecho();
    }

    private function update()
    {
        $AuthUser = $this->getVariable("AuthUser");

        $this->resp->status = 0;

        if (!$AuthUser) {
            $this->resp->message = "Unauthorized!";
            $this->jsonecho();
        }

        $Snippet = $this->getVariable("Snippet");

        if (!$Snippet->isAvailable() ||
            $Snippet->get("user_id") != $AuthUser->get("id")) {
            $this->resp->message = "Snippet not avaiable!";
            $this->jsonecho();
        }

        $basepath = BASEPATH . "/storage/snippets/" . $Snippet->get("filename") . "/";

        @file_put_contents($basepath . "htmlext+", cleanScript(Input::post("html")));
        @file_put_contents($basepath . "cssext+", cleanScript(Input::post("css")));
        @file_put_contents($basepath . "jsext+", cleanScript(Input::post("js")));

        $template = @file_get_contents(APPPATH . "/inc/template.inc.php");

        $resources = Input::post("resources") ? Input::post("resources") : [];
        $resourceslist = "";

        foreach ($resources as $resource) {
            if (stripos($resource, ".css") !== false) {
                $resourceslist .= '<link href="' . $resource . '" rel="stylesheet" type="text/css" />' . "\n";
            } else if (stripos($resource, ".js") !== false) {
                $resourceslist .= '<script type="text/javascript" src="' . $resource . '"></script>' . "\n";
            }
        }

        $search = [
            '{{html}}', '{{css}}', '{{js}}', '{{resources}}',
        ];

        $replace = [
            Input::post("html"),
            Input::post("css"),
            Input::post("js"),
            $resourceslist,
        ];

        $template = str_replace($search, $replace, $template);
        @file_put_contents($basepath . "renderext+", cleanScript($template));

        $Snippet->set("modified", date("Y-m-d H:i:s"))
                 ->set("resources", json_encode($resources))
                 ->save();

        $this->resp->status = 1;
        $this->resp->message = "Success update snippet!";
        $this->jsonecho();
    }

    private function updateInfo()
    {
        $AuthUser = $this->getVariable("AuthUser");

        $this->resp->status = 0;

        if (!$AuthUser) {
            $this->resp->message = "Unauthorized!";
            $this->jsonecho();
        }

        $Snippet = $this->getVariable("Snippet");

        if (!$Snippet->isAvailable() ||
            $Snippet->get("user_id") != $AuthUser->get("id")) {
            $this->resp->message = "Snippet not avaiable!";
            $this->jsonecho();
        }

        if (!Input::post("title")) {
            $this->resp->message = "Title is requred!";
            $this->jsonecho();
        }

        $Snippet->set("title", Input::post("title"))
            ->set("description", Input::post("description"))
            ->save();

        $this->resp->status = 1;
        $this->resp->message = "Success update snippet!";
        $this->jsonecho();
    }
}
