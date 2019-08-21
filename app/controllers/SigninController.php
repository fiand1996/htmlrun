<?php
/**
 * Login Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class SigninController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $AuthUser = $this->getVariable("AuthUser");
        if ($AuthUser) {
            header("Location: " . APPURL);
            exit;
        }

        if (Input::post("action") == "signin") {
            $this->login();
        }

        $this->view("signin", "site");
    }

    private function login()
    {
        $username = Input::post("username");
        $password = Input::post("password");
        $remember = Input::post("remember");

        if ($username && $password) {
            $User = Controller::model("User", $username);

            if ($User->isAvailable() &&
                $User->get("is_active") == 1 &&
                password_verify($password, $User->get("password"))) {
                $exp = $remember ? time() + 86400 * 30 : 0;
                setcookie("nplh", $User->get("id") . "." . md5($User->get("password")), $exp, "/");

                if ($remember) {
                    setcookie("nplrmm", "1", $exp, "/");
                } else {
                    setcookie("nplrmm", "1", time() - 30 * 86400, "/");
                }

                Event::trigger("user.signin", $User);

                header("Location: " . APPURL);
                exit;
            }
        }

        return $this;
    }
}
