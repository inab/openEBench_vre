<?php

require __DIR__ . "/../../../config/bootstrap.php";

?>
<script src="../dist/jsoneditor.js"></script>
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
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>New process</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Create new process</h1>
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

                                <div id="processes" class="portlet-body">

                                    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />
                                    
                                    <div id='editor_holder'>
                                    <script>
                                            // Initialize the editor with a JSON schema
                                            var editor = new JSONEditor($('#editor_holder')[0],{
                                                schema: {
                                                    type: "object",
                                                    title: "Car",
                                                    properties: {
                                                        make: {
                                                            type: "string",
                                                            enum: [
                                                                "Toyota",
                                                                "BMW",
                                                                "Honda",
                                                                "Ford",
                                                                "Chevy",
                                                                "VW"
                                                            ]
                                                        },
                                                        model: {
                                                            type: "string"
                                                        },
                                                        year: {
                                                            type: "integer",
                                                            enum: [
                                                                1995,1996,1997,1998,1999,
                                                                2000,2001,2002,2003,2004,
                                                                2005,2006,2007,2008,2009,
                                                                2010,2011,2012,2013,2014
                                                            ],
                                                            default: 2008
                                                        }
                                                    }
                                                }
                                            });
                                        </script>
                                    </div>
                                    <button id='submit'>Submit (console.log)</button>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                  <!-- END EXAMPLE TABLE PORTLET-->
                </div>
                <!-- END CONTENT BODY -->

                <?php
                require "../../htmlib/footer.inc.php";
                ?>

            <script>

                
                // Hook up the submit button to log to the console
                document.getElementById('submit').addEventListener('click',function() {
                    // Get the value from the editor
                    console.log(editor.getValue());
                });
                </script>
