#!/bin/sh
 
################################################################################
# Chronologist - web-based time tracking database
# Copyright (C) 2003 by Sylvain LAFRASSE.
################################################################################
# LICENSE:
# This file is part of Chronologist.
# 
# Chronologist is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Chronologist is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Chronologist; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
################################################################################
# FILE NAME   : backup.sh
# DESCRIPTION : Database backuping script.
# AUTHORS     : Sylvain LAFRASSE.
################################################################################

USER_NAME="root"
USER_PASSWORD=""

DATABASE_NAMES="mysql titi"

# On se place dans le repertoire ou le script a ete lance.
DIRNAME=`dirname "${0}"`
cd "$DIRNAME"

# Ajout des binaires dans MySQL dans le PATH
export PATH=$PATH:/usr/local/mysql/bin

#BACKUP_NAME=`date +%Y`.`date +%m`.`date +%d`.`date +%H`.`date +%M`
BACKUP_NAME="backup"
mkdir $BACKUP_NAME
cd $BACKUP_NAME


for DATABASE in $DATABASE_NAMES
do
    mysqldump --opt -u $USER_NAME -p $USER_PASSWORD -a --add-drop-table --add-locks $DATABASE > $DATABASE.sql
#    iconv -f iso-8859-15 -t Mac  $DATABASE.sql > $DATABASE-Mac.sql
done


cd ..
tar -chzf $BACKUP_NAME.tar.gz $BACKUP_NAME
rm -Rf $BACKUP_NAME
