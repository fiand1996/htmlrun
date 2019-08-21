<?php
/**
 * View Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class ViewController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        if (!Input::server("HTTP_REFERER") || 
            parse_url(Input::server("HTTP_REFERER"))['host'] != 
            parse_url(APPURL)['host']) {
            $this->resp->status = 0;
            $this->resp->message = "HTMLRun requires a referrer to render this. Your browserisn't sending one.";
            $this->jsonecho();
        }

        $Route = $this->getVariable("Route");
        $filename = $Route->params->filename;
        $filepath = BASEPATH . "/storage/snippets/" . $filename . "/renderext+";

        $Snippet = Controller::model("Snippet", $filename);

        if (!is_file($filepath) || !$Snippet->isAvailable()) {
            $filepath = BASEPATH . "/storage/temp/snippets/" . $filename . "/renderextemp+";
        }

        if (!is_file($filepath)) {
            $filepath = APPPATH . "/inc/template.inc.php";
        }

        $template = @file_get_contents($filepath);
        $search = [
            '{{html}}', '{{css}}', '{{js}}', '{{resources}}',
        ];

        $template = str_replace($search, "", $template);

        header("Content-Type: text/html");
        echo $template;
        exit;
    }
}
