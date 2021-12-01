<?php
//Allows to select which files to publish.

require __DIR__."/../../../config/bootstrap.php";
redirectOutside();

require "../../htmlib/header.inc.php";


//project list of the user
$projects = getProjects_byOwner();

?>
<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white 
    page-container-bg-solid page-sidebar-fixed">
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
                            <span>Publish</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>EUDAT</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE WARNING-->
                <!-- Show errors from frontend-->
                <div id="myError"style="display:none;"></div>
                
                <!-- END PAGE WARNING-->
                <!-- BEGIN PAGE TITLE-->
                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Publish your data
                    <i class="icon-question tooltips" data-container="body" 
                        data-html="true" data-placement="right" data-toggle="tooltip" 
                        data-trigger="click" data-original-title="<p align='left' 
                        style='margin:0'>Select files to publish on EUDAT-B2SHARE. 
                        <a target='_blank' href='<?php echo $GLOBALS['OEB_doc'];?>/how_to/participate/publish_oeb.html'> +Info</a>.</p>">
                    </i>
                </h1>

                <!-- END PAGE TITLE -->
                <!-- END PAGE TITLE-->
                <!-- END PAGE HEADER-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="mt-element-step">
                            <div class="row step-line">
                                <div class="col-md-4 mt-step-col first active">
                                    <div class="mt-step-number bg-white">1</div>
                                    <div class="mt-step-title uppercase 
                                        font-grey-cascade">Select datasets
                                    </div>
                                </div>
                                <div class="col-md-4 mt-step-col second">
                                    <div class="mt-step-number bg-white">2</div>
                                    <div class="mt-step-title uppercase 
                                        font-grey-cascade">Edit metadata's file
                                    </div>
                                </div>
                                <div class="col-md-4 mt-step-col last">
                                    <div class="mt-step-number bg-white">3</div>
                                    <div class="mt-step-title uppercase 
                                        font-grey-cascade">Summary
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
			    </div>
                <!-- Choose project from list of projects the user has in workspace -->
                <div class="input-group" style="float:right; width:200px; margin-right:10px;">
                        <span class="input-group-addon" style="background:#5e738b;"><i class="fa fa-sitemap font-white"></i></span>
                        <select class="form-control" id="select_project" 
                            onchange="loadProjectWS(this);">
                            <?php 
                            foreach ($projects as $p_id => $p) {
                                $selected = (($_SESSION['User']['dataDir'] == $p_id) ? "selected" : ""); 
                                echo "<option value=$p_id $selected>". $p['name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                <!-- BEGIN EXAMPLE TABLE PORTLET -->

                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="portlet light bordered">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="icon-share font-dark hide"></i>
                                    <span class="caption-subject font-dark bold 
                                        uppercase">Select File(s)
                                    </span>
                                    <small style="font-size:75%;">
                                        Please select the file or files you want to publish
                                    </small>
                                </div>
                                <div class="actions">
                                    <a href="<?php echo $GLOBALS['BASEURL']; ?>oeb_publish/eudat/index.php" 
                                        class="btn green"> Reload Workspace </a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div id ="selectType"></div>
                                <!--table with files-->
                                <table id="filesTable" class="table table-striped 
                                    table-hover table-bordered" width="100%"> 
                                    <thead>
                                        <tr> 
                                            <th></th>
                                            <th>Filename <i class="icon-question" 
                                                data-toggle="tooltip" data-placement="top" 
                                                title="Execution and file name"></i>
                                            </th>
                                            <th>Data type <i class="icon-question" 
                                                data-toggle="tooltip" data-placement="top" 
                                                title="Type of data file"></i>
                                            </th>
                                            <th>OEB ID <i class="icon-question" 
                                                data-toggle="tooltip" data-placement="top" 
                                                title="OpenEBench identifier"></i>
                                            </th>
                                            <th>Eudat DOI <i class="icon-question" 
                                                data-toggle="tooltip" data-placement="top" 
                                                title="Identifier of EUDAT/B2SHARE"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                                <button class=" btn green"  id="btn-run-files" 
                                    style="margin-top:20px;">Submit file(s)
                                </button> 
                                <p id="message" style="color:red;"></p>
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
            location.href = baseURL + 'applib/oeb_manageProjects.php?op=reload&pr_id=' + 
            id.value + '&redirect_url=' + redirect_url;
        };
        
              
</script>
