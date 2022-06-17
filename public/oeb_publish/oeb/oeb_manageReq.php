<?php
//Allows to select which files to publish.

require __DIR__."/../../../config/bootstrap.php";

#redirectOutside();
redirectLogin();
require "../../htmlib/header.inc.php";



//project list of the user
$projects = getProjects_byOwner();

if (!is_null ($_SESSION['User']['TokenInfo']['oeb:roles'])) {
    $communityList = getCommunitiesFromRoles($_SESSION['User']['TokenInfo']['oeb:roles']);
} else {
    $communityList = array("Filter files by community");
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
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
                            <span>Publish data</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE WARNING-->
                <div id= "warning-notAllowed" style="display:none;">
                    <br>
                    <div class="alert alert-warning expand" role="alert">
                        <h4 class="alert-heading bold">You are not allowed
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </h4>
                        <p>You don't have the properly permisions to request to 
                            publish datafiles. Only owners, managers and benchmarking 
                            contributors are allowed.
                        </p>
                        <p class="mb-0">You can request that permision sending a 
                            ticket to helpdesk: <a href="/vre/helpdesk/?sel=roleUpgrade">click here!</a>
                        </p>
                    </div>
                </div>
                <!-- END PAGE WARNING-->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Publish your datasets
		            <i class="icon-question tooltips" data-container="body" data-html="true" 
                        data-placement="right" data-toggle="tooltip" data-trigger="click" 
                        data-original-title="<p align='left' style='margin:0'>
                            See your publication requests <a target='_blank' 
                            href='<?php echo $GLOBALS['OEB_doc'];?>/how_to/participate/publish_oeb.html#publish-your-data-to-openebench'> +Info</a>.</p>">
                    </i>
		        </h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE TITLE-->
                <!-- END PAGE HEADER-->

                <!-- BEGIN LIST OF ALL FILES -->
                <div class="row">
					<div class="col-md-12 col-sm-12">
						<div class="portlet light bordered">
							<div class="portlet-title">
								<div class="caption">
									<span class="caption-subject font-dark bold 
                                        uppercase">My Publication requests
                                    </span>
								</div>
                            </div>
                            <div class="portlet-body">
                                <!-- Show errors from frontend-->
                	            <div id="myError" class ="alert-dismissible" style="display:none;">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div id="files">
                                    <table id="tableAllFiles" class="table table-striped 
                                        table-hover table-bordered" width="100%">
                                    </table>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary float-right" 
                                        onclick="location.href='oeb_publish/oeb/oeb_newReq.php'">New request
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php 
                        $matches  = preg_grep ('/owner|manager/', $_SESSION['User']['TokenInfo']['oeb:roles']);
                        ?>
                        <div id ="approvalSection" style="display:<?php if(!empty($matches)) 
                            echo "block"; else echo "none"?>;" >
                            <h1 class="page-title"> Administration Panel
		                        <i class="icon-question tooltips" data-container="body" 
                                    data-html="true" data-placement="right" 
                                    data-toggle="tooltip" data-trigger="click" 
                                    data-original-title="Table to manage users publication requests. 
                                    <a target='_blank' href='<?php echo $GLOBALS['OEB_doc'];?>/how_to/participate/publish_oeb.html'> +Info</a>.">
                                </i>
		                    </h1>
                        <div class="portlet light bordered">
                            <div  class="portlet-title">
                                <div class="caption">
                                    <span class="caption-subject font-dark bold uppercase">My requests to evaluate</span>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <!-- LOADING SPINNER-->
                                <div id="loading-datatable" class="loadingForm">
                                    <div id="loading-spinner">LOADING</div>
                                    <div id="loading-text">It could take a few minutes</div>
                                </div>
                                <div id="pendingReq">
                                    <div id ="BESelect"></div>
                                </div>
                                    <table id="tableApprovals" 
                                        class="table table-striped table-hover 
                                        table-bordered" width="100%">
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                               
                </div>
            </div>
        </div>
    </div>
    <!-- END LIST OF ALL FILES -->
    <!-- Modal Action Confirmation -->
    <div class="modal fade" id="actionDialog" tabindex="-1" role="dialog" 
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="feeds" id="file-action">
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
                                            <span id="file" class="text-info" 
                                                style="font-weight:bold;">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div>
                        <form id="confirm-form">
                            <div id="inputDenyReason" style="display:none">
                                <label for="reason">Enter a rason to deny: 
                                    <span style="color:red;">*</span>
                                </label>
                                <textarea name="reason" rows="5" cols="60" 
                                    placeholder="Reason to deny">
                                </textarea>
                            </div>
                        
                            <input name="actionReq" id="actionReq" hidden="hidden" value=""/>
                            <input name="reqId" id="reqId" hidden="hidden" value=""/>
                            </div>
                            <br>
                            </div>
                            <div class="modal-footer">
                                <button id = "" type="submit"  name="submit" 
                                    class="btn btn-primary">
                                    Accept
                                </button>
                                <button type="button" class="btn btn-secondary" 
                                    data-dismiss="modal">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Modal Log -->
            <div class="modal fade" id="modalLog" role="dialog">
                <div class="modal-dialog modal-lg">"
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" onclick="closeModal()" 
                                class="close" data-dismiss="modal" aria-hidden="true">
                            </button>
                            <h3 class="modal-title">
                                <span>
                                    <i class="fa fa-list"></i>
                                </span>
                                <b>Log error </b>
                            </h3>
                        </div>
                        <div class="modal-body table-responsive">
                            <h4 class="text-info" style="font-weight:bold;" >Log: </h4>
                            <div style="max-height:400px;" id ="modalContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="closeModal" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer-->
            <?php 
            require "../../htmlib/footer.inc.php"; 
            require "../../htmlib/js.inc.php";
            ?>                                    
            <style>
                .hide_column {
                    display : none;
                }

                div.ellipsis {
                white-space: nowrap; 
                width: 150px; 
                overflow: hidden;
                text-overflow: ellipsis;
                direction: rtl;
              }
              div.ellipsis:hover {
                overflow: visible;
              }
            </style>
            

           
