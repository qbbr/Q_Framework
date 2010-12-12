<?php
/**
 * Авторизация пользователей
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Authorization
{

    /**
     * проверка авторизации
     *
     * @static
     * @access public
     * @param integer $userId id пользователя
     * @param string $userPsswd пароль
     * @return boolean
     */
    static public function check($userId, $userPsswd)
    {
        if (empty($userId) || empty($userPsswd)) {
            return false;
        }

        $user = Doctrine::getTable('Users')->find($userId);

        if (!$user || !$user->is_active) {
            return false;
        } elseif ($user->password == $userPsswd) {
            $_SESSION['id'] = $user->id;
            $_SESSION['hash'] = $user->password;

            $user = $user->toArray();
            unset($user['password']);
            Q_Registry::set('user', $user);

            return true;
        }

        return false;
    }


    /**
     * проверка авторизации по mail`у
     *
     * @static
     * @access public
     * @param string $userMail mail
     * @param string $userPsswd пароль
     * @return array
     */
    static public function checkByMail($userMail, $userPsswd)
    {
        if (empty($userMail) || empty($userPsswd)) {
            return false;
        } else {
            $user = Doctrine::getTable('Users')->findOneBy('email', $userMail);

            $userPsswd = sha1(md5(SALT . $userPsswd) . SALT);

            if (!$user || !$user->is_active) {
                return false;
            } elseif ($user->password == $userPsswd) {

                $_SESSION['id'] = $user->id;
                $_SESSION['hash'] = md5(SALT2 . $user->password . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

                $user = $user->toArray();
                unset($user['password']);
                Q_Registry::set('user', $user);

                return true;
            }
        }

        return false;
    }


    /**
     * проверка авторизации по сессии
     * 
     * @return boolean
     */
    static public function checkBySession()
    {
        if (!isset($_SESSION['id']) || !isset($_SESSION['hash'])
           || empty($_SESSION['id']) || empty($_SESSION['hash'])
           || !is_numeric($_SESSION['id'])) {
            return false;
        }

        $user = Doctrine::getTable('Users')->find($_SESSION['id']);

        if (!$user) return false;

        $hash = md5(SALT2 . $user->password . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

        if (!$user || !$user->is_active) {
            return false;
        } elseif ($hash == $_SESSION['hash']) {

            $user = $user->toArray();
            unset($user['password']);
            Q_Registry::set('user', $user);

            return true;
        }

        return false;
    }


    static private function sessionStart()
    {
       
    }


    /**
     * выход
     *
     * @static
     * @access public
     * @return boolean
     */
    static public function logout()
    {
        if (isset($_SESSION['id'])) {
            unset($_SESSION['id']);
        }

        if (isset($_SESSION['hash'])) {
            unset($_SESSION['hash']);
        }

        return true;
    }

}