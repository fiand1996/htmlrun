<?php
/**
 * Notfound Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class NotfoundController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $this->view("404", "site");
    }
}
