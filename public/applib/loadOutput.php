<?php

require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST){
	$executions = explode(",",$_REQUEST['execution']);
	$wds = array();
	foreach($executions as $execution){
		$wd = $GLOBALS['dataDir']."/".$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."/outputs_".$execution;

		$indexFile = $wd.'/index';

		$results =Array();
		if(is_dir($wd)) {
			// check if content uncompressed
			if(file_exists($indexFile)) {
				$results = file($indexFile);
				//var_dump($results);
			}
		}else{
			// create $wd
			mkdir($wd);
			touch($indexFile);

		}

		// Get internal results
		//
		if(!count($results)) {
			$files = $GLOBALS['filesCol']->findOne(array('_id' => $execution), array('files' => 1, '_id' => 0));
			$has_statistics = false;
			foreach($files["files"] as $id) {
	
				$fMeta = iterator_to_array($GLOBALS['filesMetaCol']->find(array('_id' => $id,
							'data_type'  => "tool_statistics",
							'format'     =>'TAR',
							'compressed' =>"gzip")));
				if(count($fMeta) != 0) {
					$has_statistics = true;
				}
			}

			if(!$has_statistics) {
				$_SESSION['errorData']['Error'][]="Error creating custom results. Please make sure that the execution has successfully finished.";
				echo '0';
				die();
			}
			foreach($files["files"] as $id) {
				$fMeta = iterator_to_array($GLOBALS['filesMetaCol']->find(array('_id' => $id,
							'data_type'  => "tool_statistics",
							'format'     =>'TAR',
							'compressed' =>"gzip")));
				if(count($fMeta) ) {
					$path = $GLOBALS['dataDir']."/".getAttr_fromGSFileId($id,'path');
					exec("tar --touch -xzf \"$path\" -C \"$wd\" 2>&1", $err);

					if(!count($err)) {
						$fp = fopen($indexFile, 'a');
						fwrite($fp, $id.PHP_EOL);
						fclose($fp);

					} else { echo "0"; die(); }
				}
			}
			$results = file($indexFile);
		}
	}
	echo '1';
}else{
	redirect($GLOBALS['URL']);
}


