<?php

require __DIR__."/../../config/bootstrap.php";

//files
// check if execution is given

if(isset($_REQUEST['files'])){
    $files = explode(",",$_REQUEST['files']);
    

}else redirect($GLOBALS['BASEURL'].'workspace/');

require "../htmlib/header.inc.php";
require "../htmlib/js.inc.php"; 



?>


<link href="assets/layouts/layout/css/layout.min.css" rel="stylesheet" type="text/css" />
<link href="assets/layouts/layout/css/themes/darkblue.min.css" rel="stylesheet" type="text/css" id="style_color" />
<link href="assets/layouts/layout/css/custom.min.css?v=1323751865" rel="stylesheet" type="text/css" />


<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid page-sidebar-fixed">
    <div class="page-wrapper">

        <?php require "../htmlib/top.inc.php"; ?>
        <?php require "../htmlib/menu.inc.php"; ?>

        
        
        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <!-- BEGIN CONTENT BODY -->
            <div class="page-content">

                <!-- BEGIN PAGE HEADER-->

                <!-- BEGIN PAGE BAR -->
                <div class="page-bar">
                    <ul class="page-breadcrumb">
                        <li>
                            <span>Home</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <a href="oeb_management/oeb_block/oeb_blocks.php">Publish</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span id="spanCreate">Edit Metadata file</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title">Edit Metadata</h1>
                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->
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
                                    
                                <div id ="editor_holder" ></div>
                                <br>
                                <button id="submit" class="btn btn-primary">Submit</button>
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
                require "../htmlib/footer.inc.php";
                ?>

