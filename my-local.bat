@echo OFF

cls
SET local=%~dp0
php %local%\local.php

echo Completado. Clique para encerrar.
timeout /t 10



