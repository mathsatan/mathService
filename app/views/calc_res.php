<h1><?echo L_CALC?></h1>
<?php
/*
if (!empty($data['calc_result']['calc_error'])){
    echo '<div class="article">'.$data['calc_result']['calc_error'].'</div><br>';
}*/

if ($data['calc_result']['operation_type'] == 'integral'){
    echo '$$\int_{'.$data['calc_result']['a'].'}^{'.$data['calc_result']['b'].'}'.$data['calc_result']['latex_function'].'dx = '.$data['calc_result']['integral_value'].'$$<br>';
    echo '<div class="time">time: '.$data['calc_result']['time'].' ms</div><br>';
}elseif ($data['calc_result']['operation_type'] == 'derivative'){
    echo '$$f(x) = '.$data['calc_result']['latex_function'].'$$<br>';
    if ($data['calc_result']['order'] > 1) {
        echo '$$\frac{\mathrm{d}^'.$data['calc_result']['order'].' }{\mathrm{d} x^'.$data['calc_result']['order'].'}f(x) = '.$data['calc_result']['latex_derivative_function'].'$$<br>';
    }else{
        echo '$$\frac{\mathrm{d}}{\mathrm{d} x}f(x) = '.$data['calc_result']['latex_derivative_function'].'$$<br>';
    }

    echo '<div class="time">'.L_TIME.': '.$data['calc_result']['time'].' '.L_MSEC.'</div><br>';
}
echo '<br><a href="/main/load_calc">'.L_BACK.'</a>';
?>