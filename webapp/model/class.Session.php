<?php
/**
 * Session
 *
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 *
 */
class Session {
    /**
     *
     * @var mixed
     */
    private $data;
    /**
     *
     * @var str
     */
    private $salt = "ab194d42da0dff4a5c01ad33cb4f650a7069178b";

    /**
     * Constructor
     * @return Session
     */
    public function __construct() {
        if (isset($_SESSION)) {
            $data = $_SESSION;
        }
    }

    /**
     * @return bool Is user logged into ThinkTank
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * @param str $pwd Password
     * @return str MD5-hashed password
     */
    private function md5pwd($pwd) {
        return md5($pwd);
    }

    /**
     *
     * @param str $pwd Password
     * @return str SHA1-hashed password
     */
    private function sha1pwd($pwd) {
        return sha1($pwd);
    }
    /**
     *
     * @param str $pwd
     * @return str Salted SHA1 password
     */
    private function saltedsha1($pwd) {
        return sha1(sha1($pwd.$this->salt).$this->salt);
    }

    /**
     * Encrypt password
     * @param str $pwd password
     * @return str Encrypted password
     */
    public function pwdCrypt($pwd) {
        return $this->saltedsha1($pwd);
    }

    /**
     * Check password
     * @param str $pwd Password
     * @param str $result Result
     * @return bool Whether or submitted password matches check
     */
    public function pwdCheck($pwd, $result) {
        if ($this->saltedsha1($pwd) == $result || $this->sha1pwd($pwd) == $result || $this->md5pwd($pwd) == $result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Complete login action
     * @param str $data
     */
    public function completeLogin($data) {
        $_SESSION['user'] = $data['mail'];
    }

    /**
     * Log out
     */
    public function logout() {
        unset($_SESSION['user']);
    }
}
