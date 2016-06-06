<?php
define('MODEL_CALCULATE_PHP', 0);

/* Считает интеграл/производную */
class ModelCalculate extends Model {
    private $RESULT = null;
    private $output = null;
    private $ret = null;

    public function __construct(){
        $this->output = array();
        $this->ret;
    }

    private function printErrors($errors, $return){
        $allErrors =  'MathService.exe has exited with code: '.$return.'<br>';
        $allErrors .= 'An exception has been caught: <br>';
        foreach($errors as $msg){
            $allErrors .= $msg.'<br>';
        }
        return $allErrors;
    }

    public function integrate($expression, $a = 0, $b = 1){
        if (empty($expression)){
            throw new MVCException(E_EMPTY_FIELD);
        }
        if (!is_numeric($a) || !is_numeric($b)) {
            throw new MVCException(E_WRONG_RANGE);
        }

        exec(PROGRAM_PATH.'MathService.exe integral '.$expression.' '.$a.' '.$b, &$this->output, &$this->ret);

        if ($this->ret != 0){
            $this->RESULT['calc_result']['calc_error'] = $this->printErrors($this->output, $this->ret);
        }else{
            $this->RESULT['calc_result']['operation_type'] = 'integral';
            $this->RESULT['calc_result']['a'] = $a;
            $this->RESULT['calc_result']['b'] = $b;
            $this->RESULT['calc_result']['latex_function'] = $this->output[0];
            $this->RESULT['calc_result']['integral_value'] = $this->output[1];
            $this->RESULT['calc_result']['time'] = $this->output[2];
        }
    }
    public function differentiate($expression, $order = 1) {
        if (empty($expression)){
            throw new MVCException(E_EMPTY_FIELD);
        }
        if (!is_numeric($order)) {
            throw new MVCException(E_WRONG_ORDER);
        }
        exec(PROGRAM_PATH.'MathService.exe derivative '.$expression.' '.$order, &$this->output, &$this->ret);

        if ($this->ret != 0){
            $this->RESULT['calc_result']['calc_error'] = $this->printErrors($this->output, $this->ret);
        }else{
            $this->RESULT['calc_result']['operation_type'] = 'derivative';
            $this->RESULT['calc_result']['latex_function'] = $this->output[0];
            $this->RESULT['calc_result']['latex_derivative_function'] = $this->output[1];
            $this->RESULT['calc_result']['order'] = $order;
            $this->RESULT['calc_result']['time'] = $this->output[2];
        }
    }

    public function getData() {
        return $this->RESULT;
    }
}