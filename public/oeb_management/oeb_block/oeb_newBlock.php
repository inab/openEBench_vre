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
                            <a href="oeb_management/oeb_block/oeb_blocks.php">Blocks</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span id="spanCreate">Create new block</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Create new block</h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->

                <!-- BEGIN ERRORS DIV -->
                <div class="row">
                    <div class="col-md-12">

			<!-- Show errors from frontend-->
                	<div class="alert alert-danger" id="myError"style="display:none;"></div>

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
                            <div class="portlet light portlet-fit bordered">

                                <div id="blocks" class="portlet-body">
                                    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />
                                    <!-- LOADING SPINNER -->
                                    <div id="loading-datatable" class="loadingForm">
                                        <div id="loading-spinner">LOADING</div>
                                        <div id="loading-text">It could take a few minutes</div>
                                    </div>
                                    
                                    <div id='editor_holder'></div>
                                    <br>
                                    <button id="submit" style="display:none;" class="btn btn-primary">Submit</button>
                                    <button id="edit" style="display:none;" class="btn btn-primary">Edit</button>
                                    <br>
                                    <p class="errorClass" id="idP" style="display:none;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                  <!-- END EXAMPLE TABLE PORTLET-->
                </div>
                <!-- END CONTENT BODY -->
                <style type="text/css">
                    li a[href="#Infrastructure-details"], li a[href="#Owner"] {
                        display: none;
                    }
                    
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
                <?php
                require "../../htmlib/footer.inc.php";
                ?>

