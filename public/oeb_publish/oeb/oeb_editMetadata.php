<?php
//Allows to edit metadata file before submit

require __DIR__."/../../../config/bootstrap.php";
redirectOutside();

require "../../htmlib/header.inc.php";


//project list of the user
$projects = getProjects_byOwner();

//$communities = getCommunities("OEBC004", "name");
//var_dump($communities);
//var_dump($_SESSION['errorData']['Warning']);


if (!is_null ($_SESSION['User']['TokenInfo']['oeb:roles'])) {
    $communityList = getCommunitiesFromRoles($_SESSION['User']['TokenInfo']['oeb:roles']);
} else {
    $communityList = array("Filter files by community");
}


?>
<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid page-sidebar-fixed">
    <div class="page-wrapper">
        <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

        <?php
        require "../../htmlib/top.inc.php"; 
        require "../../htmlib/menu.inc.php";
        ?>


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
                            <span>OEB</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Edit metadata</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Edit metadata's file</h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE TITLE-->
                <!-- END PAGE HEADER-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="mt-element-step">
                            <div class="row step-line">
                                <div class="col-md-4 mt-step-col first active">
                                    <div class="mt-step-number bg-white">1</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Select files</div>
                                </div>
                                <div class="col-md-4 mt-step-col second active">
                                    <div class="mt-step-number bg-white">2</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Edit metadata's file</div>
                                </div>
                                <div class="col-md-4 mt-step-col last">
                                    <div class="mt-step-number bg-white">3</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Summary</div>
                                </div>
                            </div>
                        </div>
					</div>
			    </div>

                <!-- BEGIN LIST OF ALL FILES -->

                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet light portlet-fit bordered">
                            <div class="portlet-body">
                         
				                <h3 style="font-weight: bold; color: #666;">List of files</h3>

                                <div data-always-visible="1" data-rail-visible="0">
                                    <div>List of files to request to publish.</div><br/>
                                    <ul class="feeds" id="list-files-run-tools">
                                        <?php 
                                        if (isset($_REQUEST['files']) ){
                                            $fns = json_decode($_REQUEST['files']);
                                            foreach($fns as $file){
                                                $fnPath    = getAttr_fromGSFileId($file->id,'path');
                                                $filename  = $file->filename;
                                                $filedir   = basename(dirname($fnPath));

                                        ?>	
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">
                                                            <i class="fa fa-file"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            <span class="text-info"><b>
                                                            <?php echo $filedir; ?>  /</b></span><a href="javascript:openForm('<?php echo $file->id?>','<?php echo $filename?>'); "> <?php echo $filename; ?></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2" >
                                                <i id ="file_correct" style= "color: Green; display: none" class="fa fa-check-circle fa-lg"></i>
                                                <span style= "color: Tomato;">Pending..</span>
                                            </div>

                                        <?php }}?>

                                        </li>
                                    </ul>
                                    <div class="scroller-footer">
                                        <a class="btn btn-sm green pull-right" id="btn-submit-all" href="javascript:submitForms(); return false;">Submit</a>
                                    </div>
                                </div>
			                </div>
                        </div>
                    </div>
                </div>
                <!-- END LIST OF ALL FILES -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet light portlet-fit bordered">
                            <div id="blocks" class="portlet-body">
                                <!-- LOADING SPINNER
                                <div id="loading-datatable" class="loadingForm">
                                    <div id="loading-spinner">LOADING</div>
                                    <div id="loading-text">It could take a few minutes</div>
                                </div>
                                 -->
                                <div id ="formMetadata">
                                    <div id ="editor_holder" style="display: none;"></div>
                                    <br>
                                    <button id="saveForm" class="btn btn-primary" disabled>Save</button><span id='valid_indicator'></span>
                                    <br>
                                    <p class="errorClass" id="idP" style="display:none;"></p>
                                    
                                </div>
 				                <div id="result" style="display:none; margin-top:20px;" class="alert alert-info"></div>
                            </div>
                        </div>  
                    </div>

                </div>

            </div>
        </div> 
    </div>
            

<!-- Footer-->
<?php 
require "../../htmlib/footer.inc.php"; 
require "../../htmlib/js.inc.php";
?>                                    
<style type="text/css">
    label {
        font-weight: bold;
    }
    .form-group .required {
        font-size: 14px;
        color: #333;
    }
    .invalid-feedback {
        color: red;
    }
</style>