<?php
/**
 * Signup Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class SignupController extends Controller
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

        if (Input::post("action") == "signup") {
            $this->signup();
        }

        $this->view("signup", "site");
    }

    private function signup()
    {
        $required_fields = [
            "fullname", "username", "email",
            "password", "password-confirm",
        ];

        $required_ok = true;
        foreach ($required_fields as $field) {
            if (!Input::post($field)) {
                $required_ok = false;
            }
        }

        if (!$required_ok) {
            $errors[] = "All fields are required";
        }

        if (empty($errors)) {

            if (mb_strlen(Input::post("username")) < 6) {
                $errors[] = "Username must be at least 6 character length!";
            } else {
                $User = Controller::model("User", Input::post("username"));
                if ($User->isAvailable()) {
                    $errors[] = "Username is not available!";
                }
            }

            if (!filter_var(Input::post("email"), FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email is not valid!";
            } else {
                $User = Controller::model("User", Input::post("email"));
                if ($User->isAvailable()) {
                    $errors[] = "Email is not available!";
                }
            }

            if (mb_strlen(Input::post("password")) < 6) {
                $errors[] = "Password must be at least 6 character length!";
            } else if (Input::post("password-confirm") != Input::post("password")) {
                $errors[] = "Password confirmation didn't match!";
            }
        }

        if (empty($errors)) {

            $User->set("email", strtolower(Input::post("email")))
                ->set("password", password_hash(Input::post("password"), PASSWORD_DEFAULT))
                ->set("username", Input::post("username"))
                ->set("fullname", Input::post("fullname"))
                ->set("is_active", 1)
                ->save();

            Event::trigger("user.signup", $User);

            setcookie("nplh", $User->get("id") . "." . md5($User->get("password")), 0, "/");

            header("Location: " . APPURL);
            exit;
        }

        $this->setVariable("FormErrors", $errors);

        return $this;
    }
}
