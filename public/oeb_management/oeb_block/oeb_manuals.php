<?php

require __DIR__ . "/../../../config/bootstrap.php";

?>

<?php
require "../../htmlib/header.inc.php";
require "../../htmlib/js.inc.php"; ?>

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
                            <span>Management</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Manuals</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>See the manuals</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Manuals
                    <small>What to do in management tag</small>
                </h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->

                <div class="row">
                    <div class="portlet-body">
                        <div class="tabbable-custom nav-justified">
                            <ul class="nav nav-tabs nav-justified">
                                <li class="active uppercase">
                                    <a href="#admin" data-toggle="tab" style="text-align:center"> Administrator Manual </a>
                                </li>
                                <li class="uppercase">
                                    <a href="#community" data-toggle="tab" style="text-align:center"> Community Manager Manual </a>
                                </li>
                                <li class="uppercase">
                                    <a href="#global" data-toggle="tab" style="text-align:center"> Global Manual </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <div class="tab-pane active" id="admin">
                                    <div id="admins" class="portlet-body">
                                    <h3 for="processesTag"><b>Workflows Tag</b></h3>
                                        <br>
                                        <div style="margin-left: 20px;">
                                            <h4 for="listProcess"><b>List Workflows</b></h4>
                                            <div>
                                                Is the interface where all the workflows are listed. Furthermore, there is the Action column. 
                                            </div>
                                            <img src="oeb_management/oeb_block/photos_manual/workflow/list_workflows_admin.png">
                                            <br><br><br>

                                            <h4 for="listProcess"><b>1 - Action column</b></h4>
                                            <div>
                                                Actions:
                                                <ul>
                                                    <li><b>Create VRE tool: </b>Internally the tool is created and validated.</li>
                                                    <li><b>Reject workflow: </b>The workflow is rejected.</li>
                                                </ul>
                                            </div>
                                            <img src="oeb_management/oeb_block/photos_manual/workflow/actions_workflow_admin.png">
                                            <br><br><br>

                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="community">
                                    <div id="communities" class="portlet-body">
                                    <h3 for="processesTag"><b>Workflows Tag</b></h3>
                                        <br>
                                        <div style="margin-left: 20px;">
                                        <h4 for="listProcess"><b>List Workflows</b></h4>
                                            <div>
                                                Is the interface where their own workflows are listed. The action column is not in the community manager interface. 
                                            </div>
                                            <img src="oeb_management/oeb_block/photos_manual/workflow/list_workflows_community.png">
                                            <br><br><br>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="global">
                                    <div id="globals" class="portlet-body">
                                        <h3 for="processesTag"><b>Processes Tag</b></h3>
                                        <br>
                                        <div style="margin-left: 20px;">
                                            <h4 for="listProcess"><b>List and New Processes</b></h4>
                                            <div>
                                                Is the interface where all the available processes are listing.
                                                <br>
                                                <ul>
                                                    <li><b>Administrators: </b>Can see all processes</li>
                                                    <li><b>Community Managers: </b>Can see publics, community avaialables (if they are of the same community) and their own processes</li>
                                                </ul>
                                            </div>
                                            <img src="oeb_management/oeb_block/photos_manual/process/list_process.png">
                                            <br><br><br>
                                            
                                            <h4 for="newBlock"><b>1 - Create new Process</b></h4>
                                            <div>Is the same for all users</div>
                                            <img src="oeb_management/oeb_block/photos_manual/process/new_process.png">
                                            <br><br><br>

                                            <h4 for="newBlock"><b>2 - Reload button</b></h4>
                                            <div>You can reload ONLY the table clicking this button</div>
                                            <br><br><br>
                                            
                                            <h4 for="newBlock"><b>3 - Change the status </b></h4>
                                            <div>
                                                You can change the status of the process.
                                                <br>
                                                Users:
                                                <ul>
                                                    <li><b>Administrators: </b>Can change all processes</li>
                                                    <li><b>Community Managers: </b>Can change their own processes</li>
                                                </ul>
                                                Status:
                                                <ul>
                                                    <li><b>Private: </b>The process is private (only for you and administrators).</li>
                                                    <li><b>Public: </b>The process is available for everyone.</li>
                                                    <li><b>Coming soon: </b>The process is private and you are telling that it will come soon to the administrator.</li>
                                                    <li><b>Testing: </b>You are testing the process.</li>
                                                    <li><b>Community available: </b>The process is available for the same community as you (and for all the administrators).</li>
                                                </ul>
                                            </div>
                                            <img src="oeb_management/oeb_block/photos_manual/process/change_status_process.jpg">
                                            <br><br><br>

                                            <h4 for="newBlock"><b>4 - Actions</b></h4>
                                            <div>
                                                Action column:
                                                <br>
                                                <ul>
                                                    <li><b>Delete: </b>You can remove your process if is not being used in any workflow</li>
                                                </ul>
                                            </div>
                                            <img src="oeb_management/oeb_block/photos_manual/process/delete_process.png">
                                            <br><br><br>
                                        </div>
                                        <h3 for="processesTag"><b>Workflows Tag</b></h3>
                                        <br>
                                        <div style="margin-left: 20px;">
                                            <h4 for="listProcess"><b>List and New Workflows</b></h4>
                                            <br>
                                            <h4 for="listProcess"><b>1 - Create new  Workflow</b></h4>
                                            <div>Is the same for all users. The processes availables are the own, the public and the community available processes.
                                                When the workflow is created the responsable administrators and the user received an email.
                                            </div>
                                            <img src="oeb_management/oeb_block/photos_manual/workflow/new_workflow.png">
                                            <br><br><br>

                                            <h4 for="listProcess"><b>2 - View JSON</b></h4>
                                            <div>Users can see their workflow in JSON Format.</div>
                                            <img src="oeb_management/oeb_block/photos_manual/workflow/view_json_workflow.png">
                                            <br><br><br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
			    </div>
                <!-- END CONTENT BODY -->
                <?php
                require "../../htmlib/footer.inc.php";
                ?>
