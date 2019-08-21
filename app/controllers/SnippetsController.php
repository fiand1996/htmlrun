<?php
/**
 * Snippets Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class SnippetsController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $Route = $this->getVariable("Route");
        $username = $Route->params->username;

        $User = Controller::model("User", $username);
        $AuthUser = $this->getVariable("AuthUser");
        
        if (!$User->isAvailable()) {
            show_404();
        }

        $Snippets = Controller::model("Snippets");
        $user_id = $AuthUser ? $AuthUser->get("id") : null;
        
        $Snippets->setPageSize(6)
                 ->setPage(Input::get("page"))
                 ->where("user_id", "=", $User->get("id"));

        if ($User->get("id") !== $user_id) {
          $Snippets->where("is_public", "=", 1);
        }

        $Snippets->orderBy("id", "DESC")
                 ->fetchData();

        $this->setVariable("Snippets", $Snippets)
             ->setVariable("User", $User);

        if (Input::post("action") == "remove") {
            $this->remove();
        }

        $this->view("snippets", "site");
    }

    private function remove()
    {
        $AuthUser = $this->getVariable("AuthUser");

        $this->resp->status = 0;

        if (!$AuthUser) {
            $this->resp->message = "Unauthorized!";
            $this->jsonecho();
        }

        if (!Input::post("id")) {
            $this->resp->message = "Id is requred!";
            $this->jsonecho();
        }

        $Snippet = Controller::model("Snippet", Input::post("id"));

        if (!$Snippet->isAvailable() ||
            $Snippet->get("user_id") != $AuthUser->get("id")) {
            $this->resp->message = "Snippet not avaiable!";
            $this->jsonecho();
        }

        $basepath = BASEPATH . "/storage/snippets/" . $Snippet->get("filename") . "/";

        if (file_exists($basepath)) {
            @delete($basepath);
        } 

        $Snippet->delete();

        $this->resp->status = 1;
        $this->resp->message = "Success delete snippet!";
        $this->jsonecho();
    }
}
