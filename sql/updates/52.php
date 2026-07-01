<?php

# Reference: query used to find other tables referencing accountgroups.groupname
# before widening it -
# SELECT DISTINCT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME
# FROM information_schema.KEY_COLUMN_USAGE
# WHERE REFERENCED_TABLE_NAME = 'accountgroups' AND REFERENCED_COLUMN_NAME = 'groupname';

# 1. Drop the FK on the child table (chartmaster.group_ -> accountgroups.groupname)
DropConstraint('chartmaster', 'chartmaster_ibfk_1');

# 2. Widen the parent column
ChangeColumnSize('groupname', 'accountgroups', 'VARCHAR(90)', ' NOT NULL ', '', '90');

# 3. Widen the child column to match
ChangeColumnSize('group_', 'chartmaster', 'VARCHAR(90)', ' NOT NULL ', '', '90');

# 4. Recreate the FK using the correct child column name
AddConstraint('chartmaster', 'chartmaster_ibfk_1', 'group_', 'accountgroups', 'groupname');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(
		basename(__FILE__, '.php'),
		__('Change Column Size for accountgroups.groupname and chartmaster.group_ to VARCHAR(90)')
	);
}
