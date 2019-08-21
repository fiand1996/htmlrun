<?php
/**
 * Raw Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class RawController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $Route = $this->getVariable("Route");
        $filename = $Route->params->filename;
        $filepath = BASEPATH . "/storage/snippets/" . $filename . "/renderext+";
        $Snippet = Controller::model("Snippet", $filename);
        $AuthUser = $this->getVariable("AuthUser");

        if (!is_file($filepath) || !$Snippet->isAvailable()) {
            show_404();
        }

        $user_id = $AuthUser ? $AuthUser->get("id") : null;

        if ($Snippet->get("user_id") !== $user_id &&
            $Snippet->get("is_public") == 0) {
            show_404();
        }

        $this->setVariable("Snippet", $Snippet);

        if (isset($Route->params->action)) {
            $this->download();
        }

        header("Content-Type: text/plain");
        echo @file_get_contents($filepath);
        exit;

    }

    private function download()
    {
        $Route = $this->getVariable("Route");
        if ($Route->params->action !== "download") {
            show_404();
        }

        if (!Input::server("HTTP_REFERER") ||
            parse_url(Input::server("HTTP_REFERER"))['host'] !=
            parse_url(APPURL)['host']) {
            $this->resp->status = 0;
            $this->resp->message = "HTMLRun requires a referrer to render this. Your browserisn't sending one.";
            $this->jsonecho();
        }

        $Snippet = $this->getVariable("Snippet");

        $filename = $Snippet->get("filename");
        $basepath = BASEPATH . "/storage/snippets/" . $filename . "/";
        $zippath = BASEPATH . "/storage/temp/downloads/";
        $zipfilepath = $zippath . md5(time() . random_string(15)) . ".zip";

        $zip = new ZipArchive;

        if ($zip->open($zipfilepath, ZipArchive::CREATE) === true) {

            $template = @file_get_contents(APPPATH . "/inc/download.template.inc.php");
            $htmltemplate = @file_get_contents($basepath . "htmlext+");

            $search = [
                '{{html}}', '{{resources}}',
            ];

            $resources = json_decode($Snippet->get("resources"));
            $resourceslist = "";
            $i = 0;
            foreach ($resources as $resource) {
                $i++;
                if (stripos($resource, ".css") !== false) {
                    $resourceslist .= '<link href="' . $resource . '" rel="stylesheet" type="text/css" />';
                    if ($i !== count($resources)) {
                        $resourceslist .= "\n          ";
                    }
                } else if (stripos($resource, ".js") !== false) {
                    $resourceslist .= '<script type="text/javascript" src="' . $resource . '"></script>';
                    if ($i !== count($resources)) {
                        $resourceslist .= "\n          ";
                    }
                }
            }

            $replace = [
                $htmltemplate,
                $resourceslist,
            ];

            $template = str_replace($search, $replace, $template);

            $htmlfilepath = $zippath . md5(time() . random_string(15)) . ".htmlextemp";
            $filename = url_slug($Snippet->get("title"), '_') . "_HTMLRun.zip";

            @file_put_contents($htmlfilepath, $template);

            $zip->addFile($htmlfilepath, 'index.html');
            $zip->addFile($basepath . "cssext+", 'style.css');
            $zip->addFile($basepath . "jsext+", 'script.js');
            $zip->close();

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('filename: ' . $filename);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($zipfilepath));
            ob_clean();
            flush();
            readfile($zipfilepath);
            @delete($htmlfilepath);
            @delete($zipfilepath);
            exit;
        }

        $this->resp->status = 0;
        $this->resp->message = "Failed to render assets :(";
        $this->jsonecho();
    }
}
