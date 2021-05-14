<?php

require_once './library/SistemaLibrary.php';

Helper::deleteFile(Config::getData('path') . '/src/config/appConfig.js', false, false);
$con = Connection::getConnection();
$con->executeQuery('update app_usuario set link_public_usuario= null');

echo '<h1 class="text-center">Limpeza efetuada</h1>';
echo ((!file_exists($js)) ? "<p>Arquivo $js removido</p>" : "");
echo ((!file_exists($appConfig)) ? "<p>Arquivo $appConfig removido</p>" : "");

AppLibraryController::clearUploadFile();
AppLibraryController::clearTrash(31);

header("Location:logout");

