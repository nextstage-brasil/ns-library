@echo OFF

cls

echo Building
call d:\dropbox\ads\webs\logos_server\_build\ioncube\package_BAT.bat
echo Limpando arquivos
del d:\Dropbox\ads\webs\logos_server\*XPTO* /s > nul
del d:\Dropbox\ads\webs\logos_server\ns-app\45h\*.php /s > nul


echo Encoder
REM call d:\dropbox\ads\webs\logos_server\_build\ioncube\encoder-to-production.bat

echo Package
REM call D:\OneDrive\ads\ionCubeProjects\post-encoding\logos.bat

echo Completado. Clique para encerrar.

timeout /t 30