<?php
require(dirname(__FILE__).'/config.php');

if ( ! function_exists('glob_recursive'))
 {
     // Does not support flag GLOB_BRACE
     
     function glob_recursive($pattern, $flags = 0)
     {
         $files = glob($pattern, $flags);
         
         foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
         {
             $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
         }
         
         return $files;
     }
 }


// get languages
if (!$result = $mysqli->query('SELECT * FROM language WHERE active = 1 ORDER BY default_language DESC')) throw new Exception('An error occured while trying to get languages.');

$languages = array();
while ($row = $result->fetch_assoc()) $languages[] = array('code' => $row['code'], 'name' => $row['name']);

$files = glob_recursive(realpath(dirname(__FILE__).'/../_language/fr').'/*.php');

$updated_files = array();

foreach ($languages as $row) {
	foreach ($files as $key => $file) { 
		if (!strstr($file,'tag') && !strstr($file,'emails') && !strstr($file,'beanstream')) {		
			$tmpArray = require($file);
			
			$updated_files[$key]['name'] = $file;
			
			foreach ($tmpArray as $key_index => $value) {
				if (!stristr($key_index,'TEXT_EMAIL_PLAIN') && !stristr($key_index,'TEXT_EMAIL_HTML') && !stristr($key_index,'EMAIL_ORDER_NOTIFICATION_CONTENT')) $updated_files[$key]['values'][$key_index][$row['code']] = nl2br($value);
			}
		}
	}			
}

//echo '<pre>'.print_r($updated_files,1).'</pre>';
//exit;

// create csv
if (is_array($files) && sizeof($files) && $handle = fopen(dirname(__FILE__).'/language-file-'.time().'.csv','w')) {
	$line = array(
		'FILENAME (DO NOT CHANGE)',
		'INDEX (DO NOT CHANGE)',
	);
	
	foreach ($languages as $row) $line[] = mb_strtoupper($row['name'],'utf-8');
		
	fputcsv($handle,$line);	
	
	foreach ($updated_files as $file) {		
		$i=0;
		foreach ($file['values'] as $key => $rows) {
			$line = array();
			$line[] = $file['name'];
			$line[] = $key;
			
			foreach ($rows as $row) $line[] = $row;
			
			fputcsv($handle,$line);	
			
			++$i;
		}				
	}

	fclose($handle);
}
?>