#version: Transferhub 1.0-beta
#
#prerequisits: 
# - todo

#cleaning root directory
sudo rm -rf .git
sudo rm -rf *
sudo rm -rf .*

#get code
sudo git clone https://github.com/emonturiol/pfc.git .

#folder permissions
sudo chmod -R 777 *

#install vendor dependencies
sudo composer install

#create settings.php
cp sites/default/default.settings.php sites/default/settings.php
sudo chmod 777 sites/default/settings.php

#restart apache
sudo service apache2 restart

