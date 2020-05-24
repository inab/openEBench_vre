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
                            <a href="home/">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Management</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Workflows</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Benchmarking Workflows
                    <small>Your available workflows</small>
                </h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->

                <!-- BEGIN ERRORS DIV -->
                <div class="row">
			        <div class="col-md-12">
                        <div id="myError"style="display:none;"></div>
                        <?php
                        $error_data = false;
                        if ($_SESSION['errorData']) {
                            $error_data = true;
                      
                     	    if ($_SESSION['errorData']['Info']) { ?>
                                <div class="alert alert-info">
                             <?php } else { ?>
                                <div class="alert alert-danger">
                             <?php }
                             foreach ($_SESSION['errorData'] as $subTitle => $txts) {
                                        print "<strong>$subTitle</strong><br/>";
                                        foreach ($txts as $txt) {
                                            print "<div>$txt</div>";
                                        }
                             }
                             unset($_SESSION['errorData']);
                             ?>
                                </div>
                          <?php } ?>
                            <div class="portlet light portlet-fit bordered">
                                <div id="workflows" class="portlet-body">
                                    <div class="btn-group" style="float:right;">
                                        <div class="actions">
                                            <a id="workflowsReload" class="btn green"> Reload Workflows </a>
                                        </div>
                                    </div>
                                    <a href="oeb_management/oeb_process/oeb_newWorkflow.php" class="btn btn-lg green" style="margin-bottom:30px;"> <i class="fa fa-plus"></i> Create new</a>

                                    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

                                    <table id="workflowsTable" class="table table-striped table-hover table-bordered"></table>
                                </div>
                            </div>

					    </div>
                    </div>
			    </div>
                
                <!-- END ERRORS DIV -->

                <!-- END EXAMPLE TABLE PORTLET-->
                </div>
                <!-- END CONTENT BODY -->

                <style type="text/css">
                    #workflowsTable_filter {
                        float: right;
                    }

                    .btn-block {
                        width: 100%;
                        font-size: 12px;
                        display: block;
                        line-height: 1.5;
                    }
                </style>
                <?php
                require "../../htmlib/footer.inc.php";
                ?>
