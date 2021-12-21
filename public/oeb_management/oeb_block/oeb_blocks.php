
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
                            <span>Blocks</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Benchmarking blocks
                    <small>Your available blocks</small>
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
                <div class="portlet-body">
                    <div class="tabbable-custom nav-justified">
                        <ul class="nav nav-tabs nav-justified">
                            <li class="active uppercase">
                                <a href="#validation" data-toggle="tab" style="text-align:center"> Validation <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right"
                                        data-original-title="The validation blocks you have available"></i></a> 
                            </li>
                            <li class="uppercase">
                                <a href="#metrics" data-toggle="tab" style="text-align:center"> Metrics <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right"
                                        data-original-title="The metrics blocks you have available"></i></a>
                            </li>
                            <li class="uppercase">
                                <a href="#consolidation" data-toggle="tab" style="text-align:center"> Consolidation <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right"
                                        data-original-title="The consolidation blocks you have available"></i></a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <div class="tab-pane active" id="validation">
                                <div id="blocks" class="portlet-body">
                                    <div class="btn-group" style="float:right;">
                                        <div class="actions">
                                            <a class="btn green" onclick="reload('validation')"> Reload Validation Blocks </a>
                                        </div>
                                    </div>
                                    <a class="btn btn-lg green newBlock" name="validation" style="margin-bottom:30px;"> <i class="fa fa-plus"></i> Create new</a>

                                    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />
                                    
                                    <table id="validationTable" class="table table-striped table-hover table-bordered"></table>
                                </div>
                            </div>
                            <div class="tab-pane" id="metrics">
                                <div id="metrics" class="portlet-body">
                                    <div class="btn-group" style="float:right;">
                                        <div class="actions">
                                            <a class="btn green" onclick="reload('metrics')"> Reload Metrics Blocks </a>
                                        </div>
                                    </div>
                                    <a class="btn btn-lg green newBlock" name="metrics" style="margin-bottom:30px;"> <i class="fa fa-plus"></i> Create new</a>

                                    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

                                    <table id="metricsTable" class="table table-striped table-hover table-bordered"></table>
                                </div>
                            </div>
                            <div class="tab-pane" id="consolidation">
                                <div id="consolidations" class="portlet-body">
                                    <div class="btn-group" style="float:right;">
                                        <div class="actions">
                                            <a class="btn green" onclick="reload('consolidation')"> Reload Consolidation Blocks </a>
                                        </div>
                                    </div>
                                    <a class="btn btn-lg green newBlock" name="consolidation" style="margin-bottom:30px;"> <i class="fa fa-plus"></i> Create new</a>

                                    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

                                    <table id="consolidationTable" class="table table-striped table-hover table-bordered"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style type="text/css">
                    #validationTable_filter, #metricsTable_filter, #consolidationTable_filter {
                        float: right;
                    }
                </style>
                <?php
                require "../../htmlib/footer.inc.php";
                ?>
