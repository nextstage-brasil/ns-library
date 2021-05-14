@echo OFF

REM ionCube Encoder batch file
REM Product: Logos - Sistema de Gestão
REM Project: D:\OneDrive\ads\ionCubeProjects\logos.iep
REM Date: 2020-05-19 20:13:09

"C:/Program Files (x86)/ionCube Cerberus PHP Encoder 10.2.2/ioncube_encoder72.exe" --exclude "*.*" --encode "*.php" --encode "*.inc" --ignore "*~" --ignore "~*" --ignore "*.bak" --ignore "*.tmp" --ignore "*.iep" --ignore "*.git" --ignore ".svn/" --ignore ".*/" --ignore "*.swp" --ignore "*XPTO*" --ignore "*OLD*" --add-comment "Copyright (C) 2019 Datacloud Brasil" --add-comment " " --add-comment "This can not be copied or distributed without the express permission" --allow-encoding-into-source --dynamic-key-errors "normal" --no-short-open-tags --no-doc-comments --obfuscation-key "vPoJ8oFL6TageHpsWdAsYMaBOzHTmw9s" --obfuscation-exclusion-file "D:/Dropbox/ads/webs/suporte/_build/ioncube/obfuscation-excluded.txt" --obfuscate "linenos" --include-if-property "include_key_property='waifmWAAh9wSTRzgAHLjZYLUNcrNCG4t'" --property "include_key_property='waifmWAAh9wSTRzgAHLjZYLUNcrNCG4t'" --disable-auto-prepend-append --loader-event "license-corrupt=Invalide licence" --loader-event "license-header-invalid=Invalide licence (H)" --loader-event "license-not-found=Invalid licence (J)" --loader-event "license-server-invalid=Invalid licence to server" --replace-target --ignore "@/_build/" --ignore "@/nbproject/" --ignore "@/ns-app/" "D:/Dropbox/ads/webs/logos" -o "C:/app_encoded/logos-encoded" %*
