<?php
/**
 * Embed Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class EmbedController extends Controller
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

        header("Etag: " . md5_file($filepath));
        header("Pragma: public");
        header("Cache-Control: public");
        header("Accept-Ranges: bytes");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        header("Content-Type: text/javascript");
        header("Vary: Accept-Encoding");

        $this->view("embed", "site");
    }
}
