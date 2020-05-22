<!--<script src="https://cdn.jsdelivr.net/npm/vue"></script>-->

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
                            <span>Processes</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Benchmarking Workflows
                    <small>Your available workflows</small>
                    <div class="btn-group" style="float:right;">
                        <div class="actions">
                            <!-- <a onclick="getdata()" id="processA" class="btn green"> Reload Processes </a> -->
                            <a id="processReload" class="btn green"> Reload Workflows </a>
                        </div>
                    </div>
                </h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->

                <!-- BEGIN ERRORS DIV -->
                <div class="row">
                    <div class="col-md-12">

			<!-- Show errors from frontend-->
                	<div id="myError"style="display:none;"></div>

			<!-- Show errors from PHP backend-->
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
                       </div>
                    </div>
                <!-- END ERRORS DIV -->

                <!-- BEGIN EXAMPLE TABLE PORTLET -->
                    <div class="row">
                        <div class="col-md-12">
                            <div id="errorsTool" class="alert alert-danger" style="display:none;"></div>
                            <div class="portlet light portlet-fit bordered">
                                <div id="processes" class="portlet-body">
                                    <a href="oeb_management/oeb_process/oeb_newProcess.php" class="btn btn-lg green" style="margin-bottom:30px;"> <i class="fa fa-plus"></i> Create new</a>

                                    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

                                    <table id="tblReportResultsDemographics" class="table table-striped table-hover table-bordered"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                  <!-- END EXAMPLE TABLE PORTLET-->
                </div>
                <!-- END CONTENT BODY -->

                <style type="text/css">
                    #tblReportResultsDemographics_filter {
                        float: right;
                    }

                    .btn-block {
                        width: 48%;
                        font-size: 12px;
                        display: inline-block;
                        line-height: 1.5;
                    }
                </style>
                <?php
                require "../../htmlib/footer.inc.php";
                ?>
