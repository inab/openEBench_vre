<?php
//Allows to select which files to publish.

require __DIR__."/../../config/bootstrap.php";

//project list of the user
$projects = getProjects_byOwner();

//get files from the current active project
$allFiles = getFilesToDisplay(array('_id' => $_SESSION['User']['dataDir']), null);

require "../htmlib/header.inc.php";
//require "../htmlib/js.inc.php"; 

?>


<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid page-sidebar-fixed">
    <div class="page-wrapper">
        <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

        <?php
        require "../htmlib/top.inc.php"; 
        require "../htmlib/menu.inc.php";
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
                                    <span>Files</span>
                                </li>
                            </ul>
                        </div>
                        <!-- END PAGE BAR -->
                        <!-- BEGIN PAGE TITLE-->
                        <!-- BEGIN PAGE TITLE-->
                        <h1 class="page-title"> Publish
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
                                            <a href="<?php echo $GLOBALS['BASEURL']; ?>oeb_publish/index.php" class="btn green"> Reload Workspace </a>
                                        </div>
                                    </div>

                                    <div class="portlet-body">

                        
                                    <?php
                                    $proj_name_active   = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], "path"));
                                    
                                    $file_filter = array(
                                        "data_type" => "participant",
                                        "project"   => $proj_name_active
                                    );
                                    $filteredFiles = getGSFiles_filteredBy($file_filter);
                                    ?>

                                    <div>
                                        <div class="col-xs-12 selectorLists">
                                            <ul class=" list-group">
                                                <span id="listOfTools">
                                                <?php 
                                                foreach ($filteredFiles as $key => $value) {
                                                    echo '<li class="list-group-item runs"><input class="checkboxes" type="checkbox" value="' . $value['_id'] . '"name="sport">  ' . basename(getAttr_fromGSFileId($value['_id'], "path")) . '</li>';
                                                }
                                                ?>
                                                </span>
                                            </ul>
                                        </div>
                                            <p id="message"></p>
                                    </div>
                                    <button class=" btn green" onclick="getallselected()" id="btn-run-files" style="margin-top:20px;">Edit Selected Files</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- END EXAMPLE TABLE PORTLET-->

                <!-- Footer-->
                <?php require "../htmlib/footer.inc.php"; ?>

                <style>
                    .selected {
                        background-color: #eef1f5;
                    }

                    .selectorLists {
                        min-height: 20vh;
                        min-width: 22vh;

                    }
                </style>

                <script>
                    var redirect_url = "oeb_publish/";

                    function loadProjectWS(id) {
                        var baseURL = $('#base-url').val();
                        console.log(id);
                        location.href = baseURL + 'applib/oeb_manageProjects.php?op=reload&pr_id=' + id.value + '&redirect_url=' + redirect_url;
                    };


                    function getallselected() {
                        var baseURL = $('#base-url').val();
                        var arrayOfFiles = [];
                        $.each($("input[name='sport']:checked"), function() {
                            arrayOfFiles.push($(this).val());
                        });
                        
                        if (arrayOfFiles.length === 0) {
                            $("#message").text("No file selected");
                        }
                        else location.href = 'oeb_publish/oeb_EUDATdataset.php?files=' + arrayOfFiles;
                        //else viewResults(arrayOfFiles);
                    }
                    /** 
                    viewResults = function(files) {
                        
                        //
                        var baseURL = $('#base-url').val();
                        
                        $.ajax({
                            type: "POST",
                            url: baseURL + "/applib/publishFormAPI.php",
                            data: "files=" + files,
                            success: function(data) {
                                console.log(data);
                                if (data == '1') {
                                    setTimeout(function() {
                                        location.href = 'oeb_publish/oeb_EUDATdataset.php?files=' + files;
                                    }, 500);
                                } else if (data == '0') {
                                    setTimeout(function() {
                                        location.href = 'workspace/';
                                    }, 500);
                                }
                            }
                        });

                    };
                    */
                    
                </script>