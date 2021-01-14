<?php
//Allows to select which files to publish.

require __DIR__."/../../../config/bootstrap.php";
redirectOutside();

require "../../htmlib/header.inc.php";


//project list of the user
$projects = getProjects_byOwner();


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
                            <span>Publish participant data</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE TITLE-->
                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Publish participant data
                    <!-- Choose project from list of projects the user has in his workspace -->
                    <div class="input-group" style="float:right; width:200px; margin-right:10px;">
                        <span class="input-group-addon" style="background:#5e738b;"><i class="fa fa-sitemap font-white"></i></span>
                        <select class="form-control" id="select_project" onchange="loadProjectWS(this);">
                            <?php 
                            foreach ($projects as $p_id => $p) {
                                $selected = (($_SESSION['User']['dataDir'] == $p_id) ? "selected" : ""); 
                                echo "<option value=$p_id $selected>". $p['name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </h1>

                <!-- END PAGE TITLE -->
                <!-- END PAGE TITLE-->
                <!-- END PAGE HEADER-->
                <!-- BEGIN EXAMPLE TABLE PORTLET -->

                <div class="row">
                    <div class="col-md-12 col-sm-12">

                        <div class="portlet light bordered">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="icon-share font-dark hide"></i>
                                    <span class="caption-subject font-dark bold uppercase">Select File(s)</span> <small style="font-size:75%;">Please select the file or files you want to use</small>
                                </div>
                                <div class="actions">
                                    <a href="<?php echo $GLOBALS['BASEURL']; ?>oeb_publish/eudat/index.php" class="btn green"> Reload Workspace </a>
                                </div>
                            </div>

                            <div class="portlet-body">
                                <!--table with files-->
                                <table id="filesTable" class="table table-striped table-hover table-bordered" width="100%">
                                
                                    <thead>
                                        <tr> 
                                            <th><input name="select_all" value="1" id="example-select-all" type="checkbox" /></th>
                                            <th>id</th>
                                            <th>Filename</th>
                                            <th>Data type</th>
                                            <th>OEB ID</th>
                                            <th>Eudat DOI</th>
                                        </tr>
                                    </thead>
                                </table>
                                
                                <button class=" btn green"  id="btn-run-files" style="margin-top:20px;">Submit file(s)</button> <p id="message"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        
            <!-- END EXAMPLE TABLE PORTLET-->

            <!-- Footer-->
            <?php require "../../htmlib/footer.inc.php"; 
            require "../../htmlib/js.inc.php";
            ?>                                    
            <style>
                .hide_column {
                    display : none;
                }
            </style>

            <script>
                var redirect_url = "oeb_publish/eudat/";

                function loadProjectWS(id) {
                    var baseURL = $('#base-url').val();
                    console.log(id);
                    location.href = baseURL + 'applib/oeb_manageProjects.php?op=reload&pr_id=' + id.value + '&redirect_url=' + redirect_url;
                };  
                    
            </script>
