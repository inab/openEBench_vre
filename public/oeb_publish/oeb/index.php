<?php
//Allows to select which files to publish.

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
                        <p>You don't have the properly permisions to request to publish datafiles. Only owners, managers and challanege contributors are allowed.</p>
                        
                        <p class="mb-0">You can request that permision sending a ticket to helpdesk: <a href="/vre/helpdesk/?sel=roleUpgrade">click here!</a></p>
                    </div>
                </div>
                <!-- END PAGE WARNING-->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Publish data</h1>

                <!-- END PAGE TITLE -->
                <!-- END PAGE TITLE-->
                <!-- END PAGE HEADER-->

                <!-- BEGIN SELECT AND TABLE PORTLET -->
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="portlet light bordered">
                        <?php var_dump($_SESSION['User']['TokenInfo']['oeb:roles']); ?>
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="icon-share font-dark hide"></i>
                                    <span class="caption-subject font-dark bold uppercase">Select File(s)</span> <small style="font-size:75%;">Please select the file or files you want to request to include into the challenge:</small>
                                </div>
                            </div>
                            <!--only communities you are allowed to submit will be apperar-->
                            <div class="portlet-body">
                            <!--<button type="submit"><a href="javascript:submit2();"> Submit selected files </a></button>-->
                                <div class="input-group" style="margin-bottom:20px;">
									<span class="input-group-addon" style="background:#5e738b;"><i class="fa fa-users font-white"></i></span>
									<select id="communitySelector" class="form-control" style="width:100%;" onchange="loadCommunity(this)">
										<option value="">Filter files by community</option>
										<?php foreach ($communityList as $cl) { ?>
										    <option value="<?php echo $cl ?>" <?php if ($_REQUEST["community"] == $cl) echo 'selected'; ?>><?php echo getCommunities($cl, "name"); ?></option>
										<?php } ?>
									</select>
								</div>
                                
                                <div id ="tableMyFiles" style="display:<?php if (!isset($_REQUEST["community"]) || empty($_REQUEST["community"])) echo 'none'; ?>;">
                                    <br>
                                    <br>
                                    <table id="communityTable" class="table table-striped table-hover table-bordered" width="100%"></table>
                                </div>
                                    
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END SELECT AND TABLE  PORTLET -->
                <!-- BEGIN LIST TO MANAGE FILES -->
                <div class="row">
					<div class="col-md-12 col-sm-12">
						<div class="portlet light bordered">
							<div class="portlet-title">
								<div class="caption">
									<i class="icon-share font-dark hide"></i>
									<span class="caption-subject font-dark bold uppercase">Publishable Files</span>
								</div>

								<div class="actions" style="display:none!important;" id="actions-files">
									<div class="btn-group">
										<a class="btn btn-sm blue-madison" href="javascript:;" data-toggle="dropdown">
											<i class="fa fa-cogs"></i> Actions
											<i class="fa fa-angle-down"></i>
										</a>
										<ul class="dropdown-menu pull-right" role="menu">
											<li><a href="javascript:submit();"> Submit selected files </a></li>
										</ul>
									</div>
									<div class="btn-group">
                                        <a class="btn btn-sm red pull-right" id="btn-rmv-all" href="javascript: removeFromList('all');">
                                            <i class="fa fa-times-circle"></i> Clear all files from list
                                        </a>

									</div>
								</div>
							</div>

							<div class="portlet-body">
								<div class="" data-always-visible="1" data-rail-visible="0">
									<ul class="feeds" id="list-files-submit"></ul>
									<div id="desc-files-submit">In order to select the file to submit, please select them clicking on the checkboxes from the table above.</div>
								</div>
							</div>
						</div>
					</div>
				</div>
                <!-- END LIST TO MANAGE FILES -->

                <!-- BEGIN LIST OF ALL FILES -->

                <div class="row">
					<div class="col-md-12 col-sm-12">
						<div class="portlet light bordered">
							<div class="portlet-title">
								<div class="caption">
									<span class="caption-subject font-dark bold uppercase">List of all status files</span>
								</div>
                                <span style="float:right;"><button id ="show-all-files" type="button" class="btn" data-toggle="collapse" data-target="#files"><i class="fa fa-angle-down"></i></button></span>
                            </div>
                            <div class="portlet-body">
                                <br>
                                <div id="files" class="collapse">
                                    <table id="tableAllFiles" class="table table-striped table-hover table-bordered" width="100%"></table>
                                </div>
                            </div>   

                            

                        </div>
                    </div>
                </div>
            </div>
            <!-- END LIST OF ALL FILES -->

            <!-- Footer-->
            <?php 
            require "../../htmlib/footer.inc.php"; 
            require "../../htmlib/js.inc.php";
            ?>                                    
            <style>
                .hide_column {
                    display : none;
                }
            </style>

            <script>
                var redirect_url = "oeb_publish/oeb/";

               
                function loadCommunity(op) {
                    console.log(op.value)
                    location.href = baseURL + redirect_url + "?community=" + op.value;

                }
                    
            </script>
