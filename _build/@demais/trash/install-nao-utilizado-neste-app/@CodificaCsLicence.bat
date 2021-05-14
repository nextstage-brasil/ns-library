@echo off

php D:\Dropbox\ads\webs\permisso\_build\install\cfg\@codifica.php  

explorer D:\Dropbox\ads\webs\permisso\_build\install\licence
del d:\Dropbox\ads\webs\permisso.lic /q
move d:\Dropbox\ads\webs\permisso\_build\install\licence\localhost_permisso.lic d:\Dropbox\ads\webs\permisso.lic