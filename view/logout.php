<?php
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}


foreach ($_SESSION as $key => $value) {
    unset($_SESSION[$key]); // garantir o fechamento total de todas
}
unset($_SESSION['user']);
unset($_SESSION['login_vars']); // setada em temaplte1.php
unset($_SESSION['nav']);

?>
<script>
    sessionStorage.setItem('user', 'FALSE');
    sessionStorage.setItem('CS_TK_CST', 'FALSE');
    window.location = 'login';
</script>