<?php

require "../../phplib/genlibraries.php";
redirectOutside();



$toolid = "TCGA_CD";


// check inputs (fn)
if (!isset($_REQUEST['fn']) ){
	$_SESSION['errorData']['Error'][]="Please, before executing a workflow, select the data to be evaluated.";
	redirect('workspace/');
}
if(count($_REQUEST['fn']) < 1 ){
	$_SESSION['errorData']['Error'][] = "Please, select at least one input file to feed into the workflow";
	redirect('workspace/');
}


// get tool metadata
$tool = getTool_fromId($toolid, 1);


// get file metadata from fn
$inPaths   = Array();
if (!is_array($_REQUEST['fn']))
	$_REQUEST['fn'][]=$_REQUEST['fn'];

foreach($_REQUEST['fn'] as $fn){
		$file['path'] = getAttr_fromGSFileId($fn,'path');
		$file['fn'] = $fn;
		$file['format'] = getAttr_fromGSFileId($fn,'format');
		array_push($inPaths,$file);
}

// set default project directory name
$dirNum="000";
$reObj = new MongoRegex("/^".$_SESSION['User']['id']."\\/run\d\d\d$/i");
$prevs  = $GLOBALS['filesCol']->find(array('path' => $reObj, 'owner' => $_SESSION['User']['id']));
if ($prevs->count() > 0){
        $prevs->sort(array('_id' => -1));
        $prevs->next();
        $previous = $prevs->current();
        if (preg_match('/(\d+)$/',$previous["path"],$m) ){
            $dirNum= sprintf("%03d",$m[1]+1);
        }
}
$dirName="run".$dirNum;
$prevs  = $GLOBALS['filesCol']->find(array('path' => $GLOBALS['dataDir']."/".$_SESSION['User']['dataDir']."/$dirName", 'owner' => $_SESSION['User']['id']));
if ($prevs->count() > 0){
    $dirName="run".rand(100, 999);
}


// print page header

require "../../htmlib/header.inc.php";


////////////////////////////////////////////////////////// print  from
?>
 
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
			<h1 class="page-title"> <?php echo $tool["title"]; ?></h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->

                        <div class="row">
				<div class="col-md-12">
				<?php if(isset($_SESSION['errorData'])) { ?>
					<div class="alert alert-warning">
					<?php foreach($_SESSION['errorData'] as $subTitle=>$txts){
						print "$subTitle<br/>";
						foreach($txts as $txt){
							print "<div style=\"margin-left:20px;\">$txt</div>";
						}
					}
					unset($_SESSION['errorData']);
					?>
					</div>
				<?php } ?>

                              <!-- BEGIN PORTLET 0: INPUTS -->
                              <div class="portlet box blue-oleo">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <div style="float:left;margin-right:20px;"> <i class="fa fa-sign-in" ></i> Inputs</div>
                                      </div>
                                  </div>
                                  <div class="portlet-body">
                                <ul class="feeds" id="list-files-run-tools">
                                <?php foreach ($inPaths as $file) {
                                        $path= $file['path'];
                                        $p = explode("/", $path); 
                                        ?>
                                        <li class="tool-122 tool-list-item">
                                        <div class="col1">
                                            <div class="cont">
                                                <div class="cont-col1">
                                                    <div class="label label-sm label-info">
                                                            <i class="fa fa-file"></i>
                                                    </div>
                                                </div>
                                                <div class="cont-col2">
                                                    <div class="desc">
                                                        <span class="text-info" style="font-weight:bold;"><?php echo $p[1]; ?>  /</span> <?php echo $p[2]; ?> 
                                                    </div>
                                                </div>
                                            </div>
                                         </div>
                                        </li>
                                <?php } ?>
                                </ul>
                                  </div>
                              </div>
                              <!-- END PORTLET 0: INPUTS -->


                         <form action="#" class="horizontal-form" id="tool-form">

                                <input type="hidden" name="tool"    value="<?php echo $tool["_id"];?>"/>
				<input type="hidden" id="base-url"  value="<?php echo $GLOBALS['BASEURL']; ?>"/>
                                <input  type="hidden" name="input_files_public_dir[<?php echo $tool["input_files_public_dir"]["metrics_ref_datasets"]["name"]; ?>]" value="<?php echo $tool["input_files_public_dir"]["metrics_ref_datasets"]["value"]; ;?>"  >
                                <input  type="hidden" name="input_files_public_dir[<?php echo $tool["input_files_public_dir"]["assessment_datasets"]["name"]; ?>]" value="<?php echo $tool["input_files_public_dir"]["assessment_datasets"]["value"]; ;?>"  >
                                <input  type="hidden" name="input_files_public_dir[<?php echo $tool["input_files_public_dir"]["public_ref"]["name"]; ?>]" value="<?php echo $tool["input_files_public_dir"]["public_ref"]["value"]; ;?>"  >
                                 
                              <!-- BEGIN PORTLET 1: PROJECT AND INPUT FILE -->
                              <div class="portlet box blue-oleo">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <div style="float:left;margin-right:20px;"> <i class="fa fa-cogs" ></i> Workflow settings</div>
                                      </div>
                                  </div>
                                  <div class="portlet-body form">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Execution name</label>
                                                    <input type="text" name="project" id="dirName" class="form-control" value="<?php echo $dirName;?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Execution description</label>
                                                    <textarea id="description" name="description" class="form-control" style="height:120px;" placeholder="Write a short description here..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label"><?php echo $tool["input_files"]["genes"]["description"]; ?> <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'><?php echo $tool["input_files"]["genes"]["help"]; ?></p>"></i></label>
                                                      <select  name="input_files[<?php echo $tool["input_files"]["genes"]["name"]; ?>]" class="form-control form-field-enabled field_required">
							<?php
							if(count($_REQUEST['fn']) != 1) {
								?><option selected value> -- select a file -- </option> <?php
							}
							foreach ($inPaths as $file) {
								$p = explode("/", $file['path']); ?>
                                                                <option value="<?php echo $file['fn']; ?>" <?php if(count($_REQUEST['fn']) == 1) echo 'selected' ?>><?php echo $p[1]; ?> / <?php echo $p[2]; ?></option>
							<?php } ?>
                                                       </select>
                                                  </div>






                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 1 -->
                            <!-- BEGIN PORTLET 2: OPTIONS -->
                                
                          
                                <div class="portlet-body form form-block" id="form-block1">
                                      <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group operations_select">
                                                    <label class="control-label"><?php echo $tool["arguments"]["cancer_type"]["description"]; ?> <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'><?php echo $tool["arguments"]["cancer_type"]["help"]; ?></p>"></i></label>
                                                    <select class="form-control form-field-enabled valid select2naf field_required" name="arguments[cancer_type][]" id="operations" aria-invalid="false" multiple="multiple">
                                                    <?php
                                                    for ($i=0;$i<count($tool['arguments']['cancer_type']['enum_items']['name']);$i++) {
							$i_name = $tool['arguments']['cancer_type']['enum_items']['name'][$i];
							$i_desc = $tool['arguments']['cancer_type']['enum_items']['description'][$i];
                                                    //foreach ($tool['arguments']['cancer_type']['enum_items']['name'] as $i_name) {
                                                        if ($tool['arguments']['cancer_type']['default'] == $i_name){ ?>
                                                        	<option value="<?php echo $i_name; ?>" selected ><?php echo $i_desc; ?></option>
                                                        <?php }else{ ?>
                                                        	<option value="<?php echo $i_name; ?>" ><?php echo $i_desc; ?></option>
                                                        <?php }
                                                        } ?>
                                                    </select>    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label"><?php echo $tool["arguments"]["participant_id"]["description"]; ?> <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'><?php echo $tool["arguments"]["participant_id"]["help"]; ?></p>"></i></label>


                                                      <input type="text"  name="arguments[participant_id]" id="participant_id" class="form-control form-field-enabled field_required" value="">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                        
                              <!-- END PORTLET 2: OPTIONS -->

                              <div class="alert alert-danger err-tool display-hide">
                                  <strong>Error!</strong> You forgot to fill in some mandatory fields, please check them before submit the form.
                              </div>

                              <div class="alert alert-warning warn-tool display-hide">
                                  <strong>Warning!</strong> At least one analysis should be selected.
                              </div>

                              <div class="form-actions">
                                  <button type="submit" class="btn blue" style="float:right;">
                                    <i class="fa fa-check"></i> Compute</button>
                              </div>
                              </form>
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
