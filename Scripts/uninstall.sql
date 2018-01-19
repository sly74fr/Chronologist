-- -----------------------------------------------------------------------------
-- Chronologist - web-based time tracking database
-- Copyright (C) 2003 by Sylvain LAFRASSE.
-- -----------------------------------------------------------------------------
-- LICENSE:
-- This file is part of Chronologist.
--
-- Chronologist is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- Chronologist is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with Chronologist; if not, write to the Free Software
-- Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
-- -----------------------------------------------------------------------------
-- FILE NAME   : uninstall.sql
-- DESCRIPTION : Default database removing schema.
-- AUTHORS     : Sylvain LAFRASSE.
-- -----------------------------------------------------------------------------


DROP DATABASE IF EXISTS titi;

USE mysql;
DELETE FROM user WHERE user='titi';