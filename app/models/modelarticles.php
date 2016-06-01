<?php
define('MODEL_ARTICLES_PHP', 0);

class ModelArticles extends Model
{
    private $RESULT = null;

    public function addComment($userId, $artId, $text)
    {
        if (empty($userId) || empty($artId) || empty($text)){
            throw new MVCException(E_EMPTY_FIELD);
        }

        try {   // SET time_zone = '+03:00' SET GLOBAL time_zone = '+03:00'
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("INSERT INTO mvc_comments (comment_id, user_id, article_id, comment_text, comment_date) VALUES (NULL, :user_id, :article_id, :comment_text, CURRENT_TIMESTAMP);");
            $RESULT['is_success'] = $sth->execute(array(':user_id' => $userId, ':article_id' => $artId, ':comment_text' => $text));
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }

    public function deleteComment($commentId) {
        if (!is_numeric($commentId) || $commentId < 1){
            throw new MVCException(E_WRONG_ID);
        }

        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("DELETE FROM mvc_comments WHERE comment_id = :id;");
            $RESULT['is_success'] = $sth->execute(array(':id' => $commentId));
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }

    public static function getCategories($catId = null)
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            $cat = null;
            if ($catId != null) {
                $sth = $dbh->prepare("SELECT * FROM mvc_categories WHERE cat_id = :catId");
                $sth->execute(array(':catId' => $catId));
                $cat = $sth->fetch();
            }
            else {
                $sth = $dbh->prepare("SELECT * FROM mvc_categories");
                $sth->execute();
                $cat = $sth->fetchAll();
            }
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
        return $cat;
    }

    public function getArticleById($articleId)
    {
        if (empty($articleId) || !is_numeric($articleId)){
            throw new MVCException(E_WRONG_ID);
        }
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT * FROM mvc_articles WHERE article_id = :art_id");
            $sth->execute(array(':art_id' => $articleId));
            $this->RESULT['article'] = $sth->fetch();

            $cat = $this->getCategories($this->RESULT['article']['cat_id']);
            $this->RESULT['cat_name'] = $cat['cat_name'];

            $sth = $dbh->prepare("SELECT login, user_id FROM mvc_users WHERE user_id IN (SELECT user_id FROM mvc_comments WHERE article_id = :art_id )");
            $sth->execute(array(':art_id' => $articleId));
            while($user = $sth->fetch())
            {
                $this->RESULT['users'][$user['user_id']] = $user['login'];
            }

            $sth = $dbh->prepare("SELECT * FROM mvc_comments WHERE article_id = :art_id ORDER BY comment_date");
            $sth->execute(array(':art_id' => $articleId));

            $this->RESULT['comments'] = $sth->fetchAll();
            for($i = 0; $i < count($this->RESULT['comments']); $i++)
            {
                $this->RESULT['comments'][$i]['user_name'] = $this->RESULT['users'][$this->RESULT['comments'][$i]['user_id']];
            }

            unset($this->RESULT['users']);

            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }

    public function getArticlesByCat($catId)    // только заголовки статей по категории
    {
        if(!is_numeric($catId)) {
            throw new MVCException(E_WRONG_ID);
        }
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT article_id, str_article_id, article_title FROM mvc_articles WHERE cat_id = :cat_id");
            $sth->execute(array(':cat_id' => $catId));

            while($article = $sth->fetch()) {
                $id  = $article['article_id'];
                $this->RESULT[$id]  = array('article_title' => $article['article_title'], 'str_article_id' => $article['str_article_id']);
            }

            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }

    public function getArticles($item_count = 10, $offset = 0, $sort = 'DESC')
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_EMULATE_PREPARES => false);
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);

            $num = $this->getArticlesNumber();
            if ($item_count > $num || $item_count < 1) $item_count = $num;
            unset($num);
            $sth = $dbh->prepare("SELECT * FROM mvc_articles ORDER BY (date_format(article_date, '%Y-%m-%d %H:%i:%s')) ".$sort." LIMIT :offset, :row_num;");
            $sth->bindParam(':row_num', $item_count);
            $sth->bindParam(':offset', $offset);
            $sth->execute();
            $articles = $sth->fetchAll();

            $ids = array();
            foreach($articles as $article){
                if (!in_array($article['user_id'], $ids, true)){
                    array_push($ids, $article['user_id']);
                }
            }
            foreach($ids as $id){
                $sth = $dbh->prepare("SELECT login FROM mvc_users WHERE user_id = :id;");
                $sth->execute(array(':id' => $id));
                $login = $sth->fetch();
                foreach($articles as &$article){
                    if ($article['user_id'] == $id){
                        $article['user_login'] = $login['login'];
                    }
                }
            }
            $this->RESULT['articles'] = $articles;
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }
    public function getArticlesHeaders($item_count = 100, $offset = 0, $sort = 'ASC')
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_EMULATE_PREPARES => false);
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);

            $num = $this->getArticlesNumber();

            if ($item_count > $num || $item_count < 1) $item_count = $num;
            unset($num);
            $sth = $dbh->prepare("SELECT article_id, str_article_id, article_title, cat_id FROM mvc_articles ORDER BY (date_format(article_date, '%Y-%m-%d %H:%i:%s')) ".$sort." LIMIT :offset, :num;");
            $sth->bindParam(':offset', $offset);
            $sth->bindParam(':num', $item_count);

            $sth->execute();
            $this->RESULT['articles_menu'] = $sth->fetchAll();
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }


    public function deleteArticle($id)
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);

            $sth = $dbh->prepare("DELETE FROM mvc_articles WHERE article_id = :art_id");
            $this->RESULT['is_success'] = $sth->execute(array(':art_id' => $id));
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
    }

    public function updateArticle($article_id, $new_title, $new_text, $new_cat_id, $new_date, $new_str_id, $tags, $desc)
    {
        if (empty($article_id) || empty($new_title) || empty($new_text) || empty($new_cat_id)) throw new MVCException(E_EMPTY_FIELD);

        if (!preg_match('/^[a-z0-9]+([_|-]?[a-z0-9]+)*$/i', $new_str_id)){    // check regexp
            throw new MVCException(E_WRONG_STR_ID);
        }

        if (!preg_match("/^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01]) [0-2][0-9](\:[0-5][0-9]){2}$/", $new_date)){
           throw new MVCException(E_WRONG_DATE);
        }

        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);

            $sth = $dbh->prepare("UPDATE mvc_articles SET article_title = :title, article_text = :text, cat_id = :cat, article_date = :new_date, str_article_id = :str_id, tags = :tags, description = :description WHERE article_id = :id;");
            $this->RESULT['is_success'] = $sth->execute(array(':id' => $article_id, ':title' => $new_title, ':text' => $new_text, ':cat' => $new_cat_id, ':new_date' => $new_date, ':str_id' => $new_str_id, ':tags' => $tags, ':description' => $desc));

            $dbh = null;
        }catch (PDOException $e) {
            throw $e;
        }
    }

    public function getData(){
        return $this->RESULT;
    }

    public function getArticlesNumber()
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT COUNT(*) FROM mvc_articles");
            $sth->execute();
            $number = $sth->fetch();
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
        return $number['COUNT(*)'];
    }

    public function insertArticle($title, $text, $cat_id, $date, $user_id, $str_id, $tags, $desc)
    {
        if (empty($title) || empty($text) || empty($cat_id) || empty($date) || empty($user_id)) throw new MVCException(E_EMPTY_FIELD);

        if (!preg_match("/^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01]) [0-2][0-9](\:[0-5][0-9]){2}$/", $date)){
            throw new MVCException(E_WRONG_DATE);
        }

        if (!preg_match('/^[a-z0-9]+([_|-]?[a-z0-9]+)*$/i', $str_id)){
            throw new MVCException(E_WRONG_STR_ID);
        }
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("INSERT INTO mvc_articles (user_id, cat_id, article_title, article_text, article_date, str_article_id, tags, description) VALUES (:user_id, :cat_id, :title, :text, :article_date, :str_id, :tags, :description)");
            $this->RESULT['is_success'] = $sth->execute(array(':user_id' => $user_id, ':cat_id' => $cat_id, ':title' => $title, ':text' => $text, ':article_date' => $date, ':str_id' => $str_id, ':tags' => $tags, ':description' => $desc));
            $dbh = null;
        }catch (PDOException $e) {
            throw $e;
        }
    }

    public static function getArticleIntId($articleId)
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT article_id FROM mvc_articles WHERE str_article_id = :id");
            $sth->execute(array(':id' => $articleId));
            $strId = $sth->fetch();
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
        return $strId['article_id'];
    }

    public static function getCatIntId($catId)
    {
        try {
            $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            $dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATA_BASE, LOGIN, PASS, $opt);
            $sth = $dbh->prepare("SELECT cat_id FROM mvc_categories WHERE str_cat_id = :id");
            $sth->execute(array(':id' => $catId));
            $strId = $sth->fetch();
            $dbh = null;
        }
        catch(PDOException $e) {
            throw $e;
        }
        return $strId['cat_id'];
    }

}