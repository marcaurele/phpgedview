<?php
/**
 * Update the database schema from version 3 to version 4
 *
 * Version 0: empty database
 * Version 1: create the pgv_site_setting table
 * Version 2: create the user tables, as per PhpGedView 4.2.1
 * Version 3: create the genealogy tables, as per PhpGedView 4.2.1
 *   From Version 4 onwards, we make incremental changes, rather than
 *   trying to introspect/update.  It's more efficient, flexible,
 *   and allows us to update column types, indexes, etc.
 * Version 4: Performance tuning: update column definitions for pgv_dates
 *
 * The script should assume that it can be interrupted at
 * any point, and be able to continue by re-running the script.
 * Fatal errors, however, should be allowed to throw exceptions,
 * which will be caught by the framework.
 * It shouldn't do anything that might take more than a few
 * seconds, for systems with low timeout values.
 *
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2009 Greg Roach
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 */

if (!defined('PGV_PHPGEDVIEW')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

define('PGV_SCHEMA_3_4', '');

try {
	// These columns may have been created in an earlier versions of PGV.  Update them to the preferred type.
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_day        ".self::$COL_DAY ." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_mon        ".self::$COL_MON ." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_month      ".self::$CHAR_TYPE."(5) NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_year       ".self::$COL_YEAR." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_julianday1 ".self::$COL_JD  ." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_julianday2 ".self::$COL_JD  ." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_fact       ".self::$COL_TAG ." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_gid        ".self::$COL_XREF." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_file       ".self::$COL_FILE." NOT NULL");
	self::exec("ALTER TABLE {$TBLPREFIX}dates MODIFY d_type       ".self::$COL_CAL ." NOT NULL");
	if (self::column_exists("{$TBLPREFIX}dates", "d_datestamp")) {
		self::exec("ALTER TABLE {$TBLPREFIX}dates DROP COLUMN d_datestamp");
	}
} catch (PDOException $ex) {
	// Not all databases can alter column datatypes - either when the column contains data, or at all.
	// Ignore any errors, failing to update any of these will not break anything.
}

// Update the version to indicate sucess
set_site_setting($schema_name, $next_version);

