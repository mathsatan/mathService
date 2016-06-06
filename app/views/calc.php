<div class="article">
    <h1>Калькулятор</h1>
    <p>Здесь можно численно посчитать интеграл или аналитически вычислить производную функции.
    Корректное вычисление интеграла гарантируется при условии, что функция \(y = f(x)\) непрерывна на отрезке \([a; b]\)<br>
    Внимение! Функция пишется без пробелов<br>
    Позволяется использовать:</p>
    <ul>
        <li>Операции: "+", "-", "**", "*", "/", "abs", "sin", "cos", "tg", "ctg", "ln", "arcsin", "arccos", "arctg", "arcctg"</li>
        <li>Скобки "(", ")"</li>
        <li>Целые и действительные числа (Например: \(5\), \(3.14159265\), \(1.618\), \(0.5\))</li>
    </ul>
    <h3>Численное интегрирование</h3>
    <form name="form1" method="POST" action="/main/calc">
        <table class="wide_elem">
            <tr>
                <td class="rightcol"> \(f(x) = \)</td>
                <td><input name="expression" type="text" size="25" maxlength="128" value="4/(1+x**2)" /></td>
            </tr>
            <tr>
                <td class="rightcol">\(a = \)</td>
                <td><input name="a" type="text" size="10" maxlength="10" value="0" /></td>
            </tr>
           <tr>
                <td class="rightcol">\(b = \)</td>
                <td><input name="b" type="text" size="10" maxlength="10" value="1" /></td>
            </tr>
            <tr><td colspan="2" class="center_button"><input type="submit" name="enter" value="<?echo L_SUBMIT?>"/></td></tr>
        </table>
        <input type="hidden" name="operation_type" value="integrate" />
    </form><br>
    <h3>Производная</h3>
    <form name="form2" method="POST" action="/main/calc">
        <table class="wide_elem"><tr>
            <td class="rightcol">\(f(x) = \)</td>
            <td><input name="expression" type="text" size="25" maxlength="128" value="2*x" /></td></tr>
            <tr>
                <td class="rightcol">\(Порядок \)</td>
                <td><select name="order">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </tr>
            <tr><td colspan="2" class="center_button"><input type="submit" name="enter" value="<?echo L_SUBMIT?>"/></td></tr>
        </table>
        <input type="hidden" name="operation_type" value="derivative" />
    </form>
</div>