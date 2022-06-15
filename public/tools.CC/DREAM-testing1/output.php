<?php


require __DIR__."/../../../config/bootstrap.php";

redirectOutside();


$toolid = "DREAM-testing1";


// check if execution is given

if(!isset($_REQUEST['execution']) && !isset($_REQUEST['OEBpetition'])){
	$_SESSION['errorData']['Error'][]="You should select a project to view results";
	redirect($GLOBALS['BASEURL'].'workspace/');
} else {
    if(isset($_REQUEST['execution'])){
        $executions= explode(",",$_REQUEST['execution']);
        // find unTARed data in tmp dir
        $data_wds      = array(); // list of unTARed temporal directories, one per execution
        $data_ids      = array(); // list of file_ids for each temporal directory
        $data_pathTemps= array(); // list of files to be passed to the OEB viewer
        foreach ($executions as $execution){
            // build temporal directories
            $wd  = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."/outputs_".$execution;
            if (!is_dir($wd)){
                $_SESSION['errorData']['Error'][]="Cannot visualize your results. They are not accessible anymore. Try logging again, please. ($wd)";
                redirect($GLOBALS['BASEURL'].'workspace/');
            }
            array_push($data_wds,$wd);

            // get file_ids from index 
            $indexFile = $wd.'/index';
            $results = file($indexFile);
            array_push($data_ids,$results);

            // prepare data for custom viewer
            $inner_data  = glob("$wd/*", GLOB_ONLYDIR);
            if (!isset($inner_data[0])){
                    $_SESSION['errorData']['Error'][]="Cannot display the run output. Received results do not contain the expected data or are empty.";
                    redirect($GLOBALS['BASEURL'].'workspace/');
            }	
            $viewerfolder = fromAbsPath_toPath($inner_data[0]);
            $pathTemp = 'workspace/workspace.php?op=openPlainFileFromPath&fnPath='.$viewerfolder;
            array_push($data_pathTemps,$pathTemp);
        }

        // build data-dir for custom viewer
        $data_dir = "[\"".implode("\", \"",$data_pathTemps)."\"]";

    }elseif(isset($_REQUEST['OEBpetition'])){
        $petition_id = $_REQUEST['OEBpetition'];
        //get visualitzation url
        $visualitzationURL = OEBDataPetition::selectAllOEBPetitions(array("_id" => $petition_id))[0]['visualitzation_url'];
        
        // build temporal directories
        $other_exec = "other_executions"."/".$petition_id;
        $wd  = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."/".$other_exec;
        if (!file_exists($wd)) {
            mkdir($wd, 0777, true);
        }
        //download visualitzation files
	    if(!file_put_contents( $wd."/execution.tar.gz",fopen($visualitzationURL, 'r'))) {
            $_SESSION['errorData']['Error'][]="Error downloading tar file";
	        redirect($GLOBALS['BASEURL'].'workspace/');
        }
        //get filename
        $headers = get_headers($visualitzationURL,true);
        $fileName = isset($headers['Content-Disposition']) ? strstr($headers['Content-Disposition'], "filename=") : null ;
        $fileName = trim($fileName,"filename=\"'");
        var_dump($fileName);
        $fileName = substr($fileName, 0, -7);
        $data_dir = "";

        /** Extract tar file */
        try {
            $phar = new PharData($wd."/execution.tar.gz");
            $phar->extractTo($wd, null, true);
            $data_dir = '["workspace/workspace.php?op=openPlainFileFromPath&fnPath='.$wd.'/'.$fileName.'"]';
        } catch (Exception $e) {
            $_SESSION['errorData']['Error'][]="Error extracting tar file";
            
        }
       
    }
}



// get tool metadata
$tool = getTool_fromId($toolid, 1);



//////////////////////////////////////////////////////////////////// print page

?>

<?php require "../../htmlib/header.inc.php"; ?>


<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid page-sidebar-fixed">



    <div class="page-wrapper">

        <?php require "../../htmlib/top.inc.php"; ?>
        <?php require "../../htmlib/menu.inc.php"; ?>

        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <!-- BEGIN CONTENT BODY -->
            <div class="page-content">
                <!-- BEGIN PAGE HEADER-->
                <!-- BEGIN PAGE BAR -->
                <div class="page-bar">
                    <ul class="page-breadcrumb">
                        <li>
                            <a href="home/">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <a href="workspace/">User Workspace</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Tools</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>

                            <span><?php echo $tool["name"]; ?></span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Results
                    <small><?php echo $tool["title"]; ?></small>
                </h1>
                <!-- END PAGE TITLE-->
                <!-- END PAGE HEADER-->
                <div class="row">
                    <div class="col-md-12">
                        <p style="margin-top:0;">General Statistics for <strong><?php echo basename($pathTAR); ?></strong> project.</p>

                    </div>
                    <div class="col-md-12">
                        <p>
                            In order to facilitate the interpretation of benchmarking results OpenEbench offers several ways to visualize metrics: <br>
                            In this 2D plot two metrics from challenge <?php echo $tool["title"]; ?> are represented in the X and Y axis, showing the results from the participants in this challenge.
                            The gray line represents the pareto frontier, which runs over the participants showing the best efficiency and the arrow in the plot represents the optimal corner.
                            <br>
                            The blue selection list can be used to switch between the different classification methods / visualization modes (square quartiles, diagonal quartiles and k-means clustering)
                            Along with the chart these results are also transformed to a table which separates the participants in different groups.

                        </p>
                        <div class="note note-info" style="padding-bottom:7px;">
                            <h4><a href="workspace/workspace.php?op=downloadFile&fn=<?php echo $results[0]; ?>" style="text-decoration:none;"><i class="fa fa-download"></i> Download all the raw data in a compressed tar.gz file </a></h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel-group accordion">
                            <div class="panel panel-default">

                            <div id="custom_body" data-dir='<?php echo("$data_dir") ?>' x-label="True Positive Rate - % driver genes correctly predicted" y-label="Precision - % True positives over total predicted"></div>
                                

                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- END CONTENT BODY -->
        </div>
        <!-- END CONTENT -->

        <?php

        require "../../htmlib/footer.inc.php";
        require "../../htmlib/js.inc.php";

        ?>



        <!-- <script src = "https://rawgit.com/javi-gv94/vre_build/master/build.js"></script> -->
        <script>
        </script>
