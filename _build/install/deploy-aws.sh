#!/bin/bash

# Configuração do diretorio HOME da aplicação. A pasta www estara abaixo disso
DIR="/home/logos"
OWNER="logos"

# Não sera necessário alteracoes aqui para baixo
BUILD="$DIR/build"
PACKAGE="$BUILD/logos_server-package.zip"
TMPDIR="/tmp/ns_install"
VERSAO=$(cat "$DIR/www/version") 

# validar se existe o build aqui
if [ ! -f $PACKAGE ]; then
    echo "Instalação abortada. Pacote não localizado!"
    exit
fi

# diretorios necessários
sudo mkdir "$TMPDIR"
sudo mkdir "$DIR/.trash"
sudo chown $OWNER "$DIR/.trash"

# limpeza dos conteudos anterioes
sudo cp -r "$DIR/www/ns-app/" "$TMPDIR/"
sudo cp -r "$DIR/www/ns-st/" "$TMPDIR"
# sudo mv "$DIR/www" "$DIR/.trash/www-$VERSAO"
sudo rm -R "$DIR/www"

# criação dos diretórios
sudo mkdir "$DIR/www"

# deploy
sudo chown "$OWNER" $PACKAGE
sudo unzip -o $PACKAGE -d "$DIR/www" > /dev/null;
# sudo mv "$DIR/www/.htaccess-server" "$DIR/www/.htaccess"
sudo rm $PACKAGE

# devolver arquivos
sudo mv "$TMPDIR/ns-app" "$DIR/www/"
sudo mv "$TMPDIR/ns-st" "$DIR/www/"
sudo mkdir "$DIR/www/ns-app"
sudo mkdir "$DIR/www/ns-app/tmp"

# definir propriedade
sudo chown -R "${OWNER}:www-data" "$DIR/www"
sudo chmod -R 0777 "$DIR/www/ns-app"
sudo chmod -R 0775 "$DIR/www/cron"
sudo chmod -R 0777 "$DIR/www/ns-st"

# crontab
sudo crontab -l -u $OWNER | echo "" | sudo crontab -u $OWNER -
sudo crontab -l -u $OWNER | cat - "$DIR/www/cron/crontab" | sudo crontab -u $OWNER -

# finalizar
VERSAO=$(cat "$DIR/www/version") 
sudo rm -R $TMPDIR
# sudo service apache2 restart && sudo service php7.2-fpm restart
clear
echo "Versão $VERSAO instalada com sucesso!"
exit