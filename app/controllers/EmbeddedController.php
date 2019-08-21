<?php
/**
 * Embedded Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class EmbeddedController extends Controller
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
        $AuthUser = $this->getVariable("AuthUser");

        if (!is_file($basepath . "renderext+") || 
            !$Snippet->isAvailable()) {
            show_404();
        }

        $user_id = $AuthUser ? $AuthUser->get("id") : null;
        
        if ($Snippet->get("user_id") !== $user_id && 
            $Snippet->get("is_public") == 0) {
            show_404();
        }

        $html = @file_get_contents($basepath . "htmlext+");
        $css = @file_get_contents($basepath . "cssext+");
        $js = @file_get_contents($basepath . "jsext+");

        $this->setVariable("html", htmlspecialchars($html))
            ->setVariable("css", htmlspecialchars($css))
            ->setVariable("js", htmlspecialchars($js))
            ->setVariable("Snippet", $Snippet);

        $this->view("embedded", "site");
    }
}
