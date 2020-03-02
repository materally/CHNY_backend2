<?php

class Session {

    static function createNotification($type, $value)
    {
        if(!isset($_SESSION[$type])) {
            $_SESSION[$type] = $value;
        }
    }

    static function readNotification()
    {
        if(isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            echo "<div class='notification-message'>$message</div>";
        }
        if(isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            echo "<div class='notification-error'>$error</div>";
        }
    }

    static function deleteNotification()
    {
        if(isset($_SESSION['message'])) {
            unset($_SESSION['message']);
        }
        if(isset($_SESSION['error'])) {
            unset($_SESSION['error']);
        }
    }
    

}