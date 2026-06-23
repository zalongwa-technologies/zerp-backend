<?php

NewScript('GLChangesInEquity.php', 8);
UpdateField('scripts', 'description', 'Shows the statement of changes in equity for the selected period', "script='GLChangesInEquity.php'");

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(
		basename(__FILE__, '.php'),
		__('Add Statement of Changes in Equity to scripts table')
	);
}
?>
