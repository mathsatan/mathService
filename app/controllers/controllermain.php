<?php
if (file_exists('app/models/modelarticles.php')) {
    if (!defined('MODEL_ARTICLES_PHP')) include 'app/models/modelarticles.php';
} else {
    throw new MVCException(E_MODEL_FILE_DOESNT_EXIST);
}
if (file_exists('app/models/modelcalculate.php')) {
    if (!defined('MODEL_CALCULATE_PHP')) include 'app/models/modelcalculate.php';
} else {
    throw new MVCException(E_MODEL_FILE_DOESNT_EXIST);
}

class ControllerMain extends Controller
{
    public function __construct() {
        parent::__construct();
    }

	public function action_index()
    {
        if ($this->msg !== false) {
            $data['message'] = $this->msg;
            $data['msg_type'] = 'classic';
        } else $data['message'] = '';

        try {
            $this->model = new ModelArticles();
            $this->model->getArticles(NEWS_COUNT);
            $res = $this->model->getData();
            $data['articles'] = $res['articles'];
            $data['categories'] = ModelArticles::getCategories();
            $this->model->getArticlesHeaders();
            $res = $this->model->getData();
            $data['articles_menu'] = $res['articles_menu'];
            unset($res);
            unset($this->model);
        } catch (PDOException $e) {
            $data['message'] = $e->getMessage();
        }
        $this->view->generate('mainview.php', 'templateview.php', $data);
	}

    public function action_change_lang()
    {
        $_SESSION['lang'] = 'en';
        $this->view->generate('mainview.php', 'templateview.php');
    }

    public function action_load_calc() {
        try {
            $data['categories'] = ModelArticles::getCategories();
            $this->model = new ModelArticles();
            $this->model->getArticlesHeaders();
            $res = $this->model->getData();
            $data['articles_menu'] = $res['articles_menu'];
            unset($res);
            unset($this->model);
        } catch (PDOException $e) {
            $data['message'] = $e->getMessage();
        }
        $this->view->generate('calc.php', 'templateview.php', $data);
    }

    public function action_calc() {
        try {
            $data['categories'] = ModelArticles::getCategories();
            $this->model = new ModelArticles();
            $this->model->getArticlesHeaders();
            $res = $this->model->getData();
            $data['articles_menu'] = $res['articles_menu'];
            unset($this->model);
            unset($res);

            $this->model = new ModelCalculate();
            if ($_POST['operation_type'] == 'integrate'){
                $this->model->integrate($_POST['expression'], $_POST['a'], $_POST['b']);
            }elseif ($_POST['operation_type'] == 'derivative'){
                $this->model->differentiate($_POST['expression'], $_POST['order']);
            }else{
                $data['message'] = E_WRONG_OPERATION_TYPE;
            }
            $res = $this->model->getData();

            if (!empty($res['calc_result']['calc_error'])){
                $data['message'] = $res['calc_result']['calc_error'];
               // $this->view->generate('mainview/calc.htx', 'templateview.php', $data);
            }

            $data['calc_result'] = $res['calc_result'];
            unset($res);
            unset($this->model);
        } catch (PDOException $e1) {
            $data['message'] = $e1->getMessage();
        } catch (MVCException $e2) {
            $data['message'] = $e2->getMessage();
        }
        $this->view->generate('calc_res.php', 'templateview.php', $data);
    }

    public function action_about()
    {
        try {
            $data['categories'] = ModelArticles::getCategories();
            $this->model = new ModelArticles();
            $this->model->getArticlesHeaders();
            $res = $this->model->getData();
            $data['articles_menu'] = $res['articles_menu'];
            unset($res);
            unset($this->model);
        } catch (PDOException $e) {
            $data['message'] = $e->getMessage();
        }
        $this->view->generate('mainview/about.htx', 'templateview.php', $data);
    }
}