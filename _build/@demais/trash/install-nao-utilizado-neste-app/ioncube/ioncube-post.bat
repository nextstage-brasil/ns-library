@echo OFF
    del C:\app_encoded\logos_server-encoded.zip /q
    "C:\Program Files\7-Zip\7z.exe" a C:\app_encoded\logos_server-encoded.zip C:\app_encoded\logos_server-encoded\*  -xr!*/phpunit/* -xr!*/.*/ -xr!*.dev* -xr!*.git* -xr!*/test/* -xr!*/teste/* -xr!*teste.* -xr!*.test -xr!*.phpintel* -xr!*.trash* -xr!*_build* -xr!*.build* -xr!*.github* -xr!*nbproject* -xr!*.gitignore* -xr!*XPTO* -xr!*_OLD* -xr!*/samples/* -xr!*/docs/* -xr!*/.github/* -xr!*/example/* -xr!*/demo/* -xr!info.php -xr!*teste.php -xr!*composer.lock* -x!sch.php -x!ingest/ -x!storage/ -x!st/ -x!_app/ -x!app/ -x!test/ -x!sch.php -x!ingest/ -x!storage/ -x!app/ -x!test/ -x!ns-st -x!ns-app > nul
    rmdir C:\app_encoded\logos_server-encoded /s /q
	