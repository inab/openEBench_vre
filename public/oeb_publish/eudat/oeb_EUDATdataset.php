<?php

require __DIR__."/../../../config/bootstrap.php";
redirectOutside();

//files
// check if execution is given

if(isset($_REQUEST['files'])){
    $files = explode(",",$_REQUEST['files']);
    

}else redirect($GLOBALS['BASEURL'].'workspace/');

require "../../htmlib/header.inc.php";
require "../../htmlib/js.inc.php"; 

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
                            <a href="oeb_publish/eudat/">Publish</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span></span>
                            <a href="oeb_publish/eudat/">EUDAT</a>
                            
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title">EUDAT Publication</h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->

                <!-- BEGIN LIST FILES  PORTLET -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet light portlet-fit bordered">
                            <div class="portlet-body">
				                <h3 style="font-weight: bold; color: #666;">List of datasets</h3>

                                <div data-always-visible="1" data-rail-visible="0">
                                    <div>List of datasets to be published.</div><br/>
                                    <ul class="feeds" id="list-files-run-tools">
                                        <?php 
                                        if (isset($_REQUEST['files']) ){
                                            $fns =(is_array($_REQUEST['files'])? $_REQUEST['files'] : array($_REQUEST['files']));

                                            foreach($fns as $fn){
                                                $fnPath    = getAttr_fromGSFileId($fn,'path');
                                                $filename  = basename($fnPath);
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
                                                            <span class="text-info" style="font-weight:bold;">
                                                            <?php echo $filedir; ?>  /</span> <?php echo $filename; }}?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="scroller-footer">
                                        <a class="btn btn-sm green pull-right" id="btn-rmv-all" href="javascript:windows.history.back(); return false;"><i class="fa fa-times-circle"></i> Edit list</a>
                                    </div>
                                </div>
			                </div>
                        </div>
                    </div>
                </div>
                <!-- END LIST FILES PORTLET -->

                <!-- BEGIN EXAMPLE TABLE PORTLET -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet light portlet-fit bordered">
                            <div id="blocks" class="portlet-body">
                                <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />
                                <!-- LOADING SPINNER -->
                                <div id="loading-datatable" class="loadingForm">
                                    <div id="loading-spinner">LOADING</div>
                                    <div id="loading-text">It could take a few minutes</div>
                                </div>
                                    
                                <input type="hidden" id="files" name="custId" value= "<?php echo $_REQUEST['files'] ?>">
                                <div id ="formMetadata">
                                    
                                    <div id ="editor_holder" ></div>
                                    <br>
                                    <button id="submit" class="btn btn-primary">Submit</button><span id='valid_indicator'></span>
                                    <br>
                                    <p class="errorClass" id="idP" style="display:none;"></p>
                                    
                                </div>
 				<div id="result" style="display:none; margin-top:20px;" class="alert alert-info"></div>
				<!-- </div> -->
                                <!-- Modal -->
                                <div class="modal fade" id="myModal" role="dialog">
                                    <div class="modal-dialog modal-lg">"
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <button type="button" onclick="closeModal()" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                                <h3 class="modal-title">
                                                    <span >
                                                        <i class="fa fa-list"></i>
                                                    </span>
                                                    <b>Summary </b>
                                                </h3>
                                            </div>
                                            <div style="margin: 15px ;"><h4>Are you sure you want to submit the following data to EUDAT?</h4></div>
                                            <div class="modal-body table-responsive">
                                                <h4 class="text-info" style="font-weight:bold;" >Datasets: </h4>


						   <div class="portlet-body">
                		                      <div class="" data-always-visible="1" data-rail-visible="0">
                                		        <ul class="feeds" id="list-files-run-tools">
					 	  <?php 
						   if (isset($_REQUEST['files']) ){
							$fns =(is_array($_REQUEST['files'])? $_REQUEST['files'] : array($_REQUEST['files']));

							foreach($fns as $fn){
							   $fnPath    = getAttr_fromGSFileId($fn,'path');
							   $filename  = basename($fnPath);
						 	  $filedir   = basename(dirname($fnPath));

					   	  	  ?>	
					   	  	  <li>
						     	 <div class="col1"><div class="cont">
						        	 <div class="cont-col1"><div class="label label-sm label-info"><i class="fa fa-file"></i></div></div>
						        	 <div class="cont-col2"><div class="desc"><span class="text-info" style="font-weight:bold;"><?php echo $filedir; ?>  /</span> <?php echo $filename; ?></div></div>
						      	</div></div>
					   	  	 </li>
					   		<?php
							}
					   	}
					   	?> 
						    </ul>
                                     		 </div>
                                  	 	</div>
				  	 	<br/>
						
                                                <h4 class="text-info" style="font-weight:bold;" >Metadata: </h4>
                                                <div style="max-height:400px;" id ="summaryContent"></div>
					    </div>
                                            <div class="modal-body table-responsive">
                                                <h4 class="text-info" style="font-weight:bold;" >EUDAT Access: </h4>
				   		<div class="portlet-body">
						    <p>
							Service: B2SHARE</br>
							Server: <a href="https://eudat-b2share-test.csc.fi/">https://eudat-b2share-test.csc.fi</a></br>
							Username: openEBench-generic 
						    </p>
						</div>
                                            
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" id="submitModal" class="btn btn-primary">Submit</button>
                                                <button type="button" id="closeModal" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    
                                    </div>
                                </div>
                                
                                <br>
                                <button id="back" class="btn btn-default" style="display:none;">Back</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
            <!-- END CONTENT BODY -->
            
                <style type="text/css">
                    
                    
                    .invalid-feedback {
                        color: red;
                    }

                    button {
                        margin: 3px;
                    }

                    label {
                        font-weight: bold;
                    }

                    #idP {
                        margin-top: 20px;
                    }
                    
                    /* Encapsulate some fields */
                    .form-group, .btn-group,
                    div[data-schemapath="root.nextflow_files.files"] {
                        margin-left: 20px;
                    }

                    .form-group .required {
                        font-size: 14px;
                        color: #333;
                    }

                </style>
                <!-- Footer-->
                <?php
                require "../../htmlib/footer.inc.php";
                ?>

