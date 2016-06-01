<?php
define('MODEL_USERS_PHP', 0);

if (file_exists('app/core/template.php')) {
    if (!defined('TEMPLATE_PHP')) include 'app/core/template.php';
} else {
    throw new MVCException(E_CLASS_NOT_FOUND);
}

/* Обеспечивает работу с пользователями */
class ModelUsers extends Model
{
    private $RESULT = null;

    public function __construct(){
    }

    public static function getUserCount(){
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);

            $sth = $dbh->prepare("SELECT COUNT(*) FROM mvc_users;");
            $sth->execute();
            $count = $sth->fetch();
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
        return $count['COUNT(*)'];
    }

    public function getUsers($rowCount = 10, $offset = 0)
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT * FROM mvc_users LIMIT :offset, :num;");
            $sth->bindParam(':offset', $offset, PDO::PARAM_INT);
            $sth->bindParam(':num', $rowCount, PDO::PARAM_INT);
            $sth->execute();

            $this->RESULT['users'] = $sth->fetchAll();
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }

    public function deleteUser($id)
    {
        if (!is_numeric($id) || $id < 0) throw new MVCException(E_INCORRECT_DATA);
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("DELETE FROM mvc_users WHERE  user_id = :id;");
            $this->RESULT['is_success'] = $sth->execute(array(':id' => $id));
            $dbh = null;
        }catch (PDOException $e) {
            throw $e;
        }
    }

    public function updateUser($id, $login, $pass, $mail, $status, $isActive)
    {
        $this->checkUserData( $login, $pass, $mail, $status, $isActive);

        try{
            $user = $this->isUserExist($login);
        }catch (PDOException $e){
            throw $e;
        }

        if (($user['user_id'] != $id) && ($login === $user['login']))
            throw new MVCException(E_LOGIN_ALREADY_EXIST);

        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            if (empty($pass))  {
                $sth = $dbh->prepare("UPDATE mvc_users SET login = :login, email = :mail, status = :status, is_active = :is_active WHERE user_id = :id;");
                $this->RESULT['is_success'] = $sth->execute(array(':id' => $id, ':login' => $login, ':mail' => $mail, ':status' => $status, ':is_active' => $isActive));
            }
            else{
                $sth = $dbh->prepare("UPDATE mvc_users SET login = :login, pass = :pass, email = :mail, status = :status, is_active = :is_active WHERE user_id = :id;");
                $this->RESULT['is_success'] = $sth->execute(array(':id' => $id, ':login' => $login, ':pass' => MD5($pass), ':mail' => $mail, ':status' => $status, ':is_active' => $isActive));
            }
            $dbh = null;
        }catch (PDOException $e) {
            throw $e;
        }
    }

    public function signUp($login, $pass, $mail, $isAdmin = 0, $isActive = 1) {
        if (!file_exists('app/core/sendmailsmtp.php')){
            throw new MVCException(E_CLASS_NOT_FOUND);
        }include 'app/core/sendmailsmtp.php';

        try{
            $this->checkUserData( $login, $pass, $mail, $isAdmin, $isActive);
            $this->RESULT['user'] = $this->isUserExist($login);
        }catch (PDOException $e1){
            throw $e1;
        }catch (MVCException $e2){
            throw $e2;
        }

        if (!empty($this->RESULT['user']))
            throw new MVCException(E_LOGIN_ALREADY_EXIST);

        try{
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("INSERT INTO mvc_users (user_id, login, pass, email, status, is_active) VALUES (NULL, :login, MD5(:pass), :mail, :status, :is_active)");
            $this->RESULT['is_success'] = $sth->execute(array(':login' => $login, ':pass' => $pass, ':mail' => $mail, ':status' => $isAdmin, ':is_active' => $isActive));
            $dbh = null;

            $mailSMTP = new SendMailSmtp(SYSTEM_EMAIL, SYSTEM_PASSWORD, 'ssl://smtp.yandex.ru', COMPANY, 465);
            $headers= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
            $headers .= "From: ".SYSTEM_EMAIL."\r\n"; // от кого письмо
            $headers .= "To: ".$mail."\r\n"; // кому письмо

            $text = new Template('txt/', 'reg_success.htx');
            $text->addKey('login', $login);
            $text->addKey('pass', $pass);
            $text->addKey('company_name', COMPANY);

            $this->RESULT['is_success'] = $mailSMTP->send($mail, 'Password restore', $text->parseTemplate(), $headers);
            unset($text);
        }catch (PDOException $e1){
            throw $e1;
        }catch (TemplateException $e2){
            throw $e2;
        }
    }

    public function signIn($login, $pass)
    {
        if (empty($login) || empty($pass))
            throw new MVCException(E_EMPTY_FIELD);

        try{
            $user = $this->isUserExist($login);
        }
        catch(PDOException $e){
            throw $e;
        }

        if ($user === false)
            throw new MVCException(E_USER_NOT_FOUND);

        if ($user['is_active'] == 0)
            throw new MVCException(E_USER_NOT_ACTIVE);

        if ($user['login'] === $login && $user['pass'] === md5($pass)) {
            $this->RESULT['user_info'] = $user;
        }
        else
            throw new MVCException(E_WRONG_LOGIN_OR_PASS);
    }

    public function getUserInfo($id)
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT * FROM mvc_users WHERE user_id = :user_id");
            $sth->execute(array(':user_id' => $id));
            $this->RESULT['user_info'] = $sth->fetch();
            $dbh = null;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    protected function isUserExist($login)  /* userExist */
    {
        if (empty($login)) return false;

        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT * FROM mvc_users WHERE login = :login");
            $sth->execute(array(':login' => $login));
            $user = $sth->fetch();
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
        if (empty($user))
            return false;
        else
            return $user;
    }

    protected function checkUserData($login, $pass, $mail, $isAdmin, $isActive)
    {
        if (empty($login) /*|| empty($pass)*/|| empty($mail))
            throw new MVCException(E_EMPTY_FIELD);

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
            throw new MVCException(E_INVALID_EMAIL);

        if ((!is_numeric($isAdmin)) || !is_numeric($isActive))
            throw new MVCException(E_INCORRECT_DATA);
    }

    public function genNewPassword($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new MVCException(E_INVALID_EMAIL);
        }
        if (!file_exists('app/core/sendmailsmtp.php')){
            throw new MVCException(E_CLASS_NOT_FOUND);
        }include 'app/core/sendmailsmtp.php';

        try{
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT user_id, login FROM mvc_users WHERE email = :email");
            $sth->execute(array(':email' => $email));
            $user = $sth->fetch();
            if (empty($user['user_id'])){
                $this->RESULT['is_success'] = false;
                throw new MVCException(E_EMAIL_NOT_EXIST);
            }
            $newPass = uniqid();
            $sth = $dbh->prepare("UPDATE mvc_users SET pass = :pass WHERE user_id = :id;");
            $this->RESULT['is_success'] = $sth->execute(array(':pass' => MD5($newPass), ':id' => $user['user_id']));
            $dbh = null;

            $mailSMTP = new SendMailSmtp(SYSTEM_EMAIL, SYSTEM_PASSWORD, 'ssl://smtp.yandex.ru', COMPANY, 465);
            $headers= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";
            $headers .= "From: ".SYSTEM_EMAIL."\r\n";
            $headers .= "To: ".$email."\r\n";

            $text = new Template('txt/', 'password_restore.htx');
            $text->addKey('new_pass', $newPass);
            $text->addKey('login', $user['login']);
            $text->addKey('company_name', COMPANY);

            $this->RESULT['is_success'] = $mailSMTP->send($email, 'Password restore', $text->parseTemplate(), $headers);
            unset($text);
        }catch (MVCException $e1){
            throw $e1;
        }catch (PDOException $e2){
            throw $e2;
        }catch (TemplateException $e3){
            throw $e3;
        }
    }

    public function getData()
    {
        return $this->RESULT;
    }
}