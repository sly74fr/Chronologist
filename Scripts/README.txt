cd /usr/local/mysql/bin/
/usr/local/mysql/bin/mysql -u root
CREATE DATABASE titi;
USE mysql;
GRANT ALL ON titi.* TO titi@localhost IDENTIFIED BY 'titi' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON titi.* TO titi@localhost;

quit
/usr/local/mysql/bin/mysql -u titi -p -D titi < /Users/lafrasse/Sites/Chronologist\ 2/Scripts/backup/titi.sql 

