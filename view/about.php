<?php
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
include Config::getData('pathView') . '/template/template1.php';
?>

<div class="row">
    <div class="col-sm-12 cs-container">
        <h3>Sobre</h3>
        <p><?= Config::getData('title') ?></p>
            
    </div>
</div>

<?php
include Config::getData('pathView') . '/template/template2.php';
