@echo off
c:
cd /xampp
php phpdoc.phar -d D:\Dropbox\ads\webs\logos_server\src -t D:\Dropbox\ads\webs\logos_server\_build\install\doc
rem php apigen.phar -s D:\Dropbox\ads\webs\logos_server\src -t D:\Dropbox\ads\webs\logos_server\_build\install\doc

timeout /t 30
