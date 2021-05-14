@echo off

rem configurações básicas:
set deployname=aws
set keyfile=E:\ads\@keys\keys\nextstage_aws_key.ppk
set userhost=ubuntu@logos.usenextstep.com.br
set destino=/home/logos/build/

echo #### ATENCAO: DEPLOY EM PRODUCAO!! ####
SET /P AREYOUSURE=Continuar com deploy em PRODUCAO %userhost%? (y/[n])?
IF /I "%AREYOUSURE%" NEQ "y" GOTO END

rem Não é necessário alterar aqui para baxio

php D:\Dropbox\ads\webs\logos_server\_build\install\builder.php

echo Enviar para servidor: %deployname%
pscp -P 22 -i %keyfile% c:\app_encoded\logos_server-package.zip %userhost%:%destino%

echo "Instalar no servidor: deploy-%deployname%.sh"
plink -batch -i %keyfile% %userhost% -m d:\Dropbox\ads\webs\logos_server\_build\install\deploy-%deployname%.sh

timeout /t 60