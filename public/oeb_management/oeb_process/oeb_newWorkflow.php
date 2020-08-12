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
                            <a href="oeb_management/oeb_process/oeb_workflows.php">Workflows</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Create new workflow</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Benchmarking Workflows
                    <small>Create your new workflow</small>
                </h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->

                <div class="row">
			        <div class="col-md-12">
						<div class="mt-element-step">
							<div class="row step-line">
                                <div class="col-md-4 mt-step-col first active" id="firstActive">
                                    <div class="mt-step-number bg-white">1</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Validation</div>
                                </div>
                                <div class="col-md-4 mt-step-col second" id="secondActive">
                                    <div class="mt-step-number bg-white">2</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Metrics</div>
                                </div>
                                <div class="col-md-4 mt-step-col last" id="thirdActive">
                                    <div class="mt-step-number bg-white">3</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Consolidation</div>
                                </div>
							</div>
						</div>

					</div>
			    </div>
                <div class="portlet-body">
                    <div class="tabbable-custom nav-justified">
                        <ul class="nav nav-tabs nav-justified">
                            <li class="active uppercase">
                                <a href="#validation" id="first" data-toggle="tab" style="text-align:center"> Validation </a>
                            </li>
                            <li class="uppercase">
                                <a href="#metrics" id="second" data-toggle="tab" style="text-align:center"> Metrics </a>
                            </li>
                            <li class="uppercase">
                                <a href="#consolidation" id="third" data-toggle="tab" style="text-align:center"> Consolidation </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="validation">
                            </div>
                            <div class="tab-pane" id="metrics">
                            </div>
                            <div class="tab-pane" id="consolidation">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
					<div class="col-md-12 col-sm-12">
						<div class="portlet light bordered">
							<div class="portlet-title">
								<div class="caption">
									<i class="icon-share font-dark hide"></i>
									<span class="caption-subject font-dark bold uppercase">Necessary Information</span>
								</div>
							</div>

							<div class="portlet-body">
								<div class="" data-always-visible="1" data-rail-visible="0">
                                    <div class="form-group">
                                        <label for="nameWorkflow">Name</label>
                                        <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" 
                                        title="Set here the name of your new workflow."><i class="icon-question"></i></a>
                                        <input type="text" class="form-control" id="nameWorkflow">
                                        <br>
                                        <div id="divErrors" style="display:none;" class="alert alert-danger"></div>
                                        <div id="setWorkflow" class="portlet-body" >
                                            <button id="submit" class="btn btn-lg green"> <i class="fa fa-plus"></i>  SUBMIT</button>
                                        </div>
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
