<#1>
<?php
$table_name = "uichk_xcoursecover";

if(!$ilDB->tableExists($table_name)) {
	$fields = [
		'ref_id' => [
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		],
		'cover_logo_id' => [
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		],
		'cover_square_id' => [
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		],
		'cover_banner_id' => [
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		],
		'cover_banner2_id' => [
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		],
		'cover_banner3_id' => [
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		],
	];
	
	$ilDB->createTable($table_name, $fields);
	$ilDB->addPrimaryKey($table_name, ["obj_id"]);
}
?>