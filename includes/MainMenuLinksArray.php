<?php

unset($_SESSION['ModuleLink']);
unset($_SESSION['ReportList']);
unset($_SESSION['ModuleList']);
unset($_SESSION['MenuItems']);

$ModuleLink = array();
$ReportList = array();
$ModuleList = array();
$MenuItems = array();

if (isset($_SESSION['AccessLevel'])) {
	$SQL = "SELECT `modulelink`,
					`reportlink` ,
					`modulename`
				FROM modules
				WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
				ORDER BY `sequence`";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		$ModuleLink[] = $MyRow['modulelink'];
		$ReportList[$MyRow['modulelink']] = $MyRow['reportlink'];
		$ModuleList[] = __($MyRow['modulename']);
	}

	if (!in_array('SARIS', $ModuleLink ?? array())) {
		$InsertAt = count($ModuleLink ?? array());
		$AssetManagerIndex = array_search('FA', $ModuleLink ?? array());
		if ($AssetManagerIndex !== false) {
			$InsertAt = $AssetManagerIndex + 1;
		}
		array_splice($ModuleLink, $InsertAt, 0, array('SARIS'));
		array_splice($ModuleList, $InsertAt, 0, array(__('SARIS Integration')));
		$ReportList['SARIS'] = 'saris';
	}

	$SQL = "SELECT `modulelink`,
					`menusection` ,
					`caption` ,
					`url`
				FROM menuitems
				WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
				ORDER BY `sequence`, `menusection`";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = __($MyRow['caption']);
		$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = $MyRow['url'];
	}

	if (!isset($MenuItems['SARIS'])) {
		$MenuItems['SARIS']['Transactions']['Caption'] = array(
			__('Settings'),
			__('Students'),
			__('Invoices'),
			__('Payments'),
			__('Sync History')
		);
		$MenuItems['SARIS']['Transactions']['URL'] = array(
			'/SARIS_Settings.php',
			'/SARIS_Students.php',
			'/SARIS_Invoices.php',
			'/SARIS_Payments.php',
			'/SARIS_SyncHistory.php'
		);
	}

	$SARISScriptSecurity = array(
		'SARIS_Settings.php' => 15,
		'SARIS_Students.php' => 15,
		'SARIS_Invoices.php' => 15,
		'SARIS_Payments.php' => 15,
		'SARIS_SyncHistory.php' => 15
	);
	foreach ($SARISScriptSecurity as $ScriptName => $SecurityToken) {
		if (!isset($_SESSION['PageSecurityArray'][$ScriptName])) {
			$_SESSION['PageSecurityArray'][$ScriptName] = $SecurityToken;
		}
	}
}
