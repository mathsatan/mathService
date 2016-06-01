<div  id="user_table">
<h1><?echo L_LOGIN_PAGE?></h1>
<form action='/users/sign_in' method='post'>
    <table>
    <tr><td class="rightcol"><?echo L_USER_LOGIN; ?>:</td> <td><input type='text' name='login'></td></tr>
    <tr><td class="rightcol"><?echo L_USER_PASS; ?>:</td> <td><input type='password' name='password'></td></tr>
    <tr><td class="center_button" colspan="2"><input type='submit' value="<? echo L_SUBMIT; ?>"></td></tr>
    </table>
        <input name="try_login" type="hidden" value="1">
</form><br>
    <a href="/users/load_restore_pass"><?echo E_USER_FORGOT_PASS; ?></a>
</div>
