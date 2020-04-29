<!--<script src="https://cdn.jsdelivr.net/npm/vue"></script>-->

<?php

require __DIR__ . "/../../../config/bootstrap.php";

require('oeb_view_functions.php');
// project list
$projects = getProjects_byOwner();
$toolsList = getTools_List();

sort($toolsList);

//get all files for user
$allFiles = getFilesToDisplay(array('_id' => $_SESSION['User']['dataDir']), null);

?>

<?php
require "../../htmlib/header.inc.php";
require "../../htmlib/js.inc.php";
?>

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid page-sidebar-fixed">
    <div class="page-wrapper">
        <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

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
                            <span>Results</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Views</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>General</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->

                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Results

                    <!-- Choose project from list of projects the user has in his workspace -->
                    <div class="input-group" style="float:right; width:200px; margin-right:10px;">
                        <span class="input-group-addon" style="background:#5e738b;"><i class="fa fa-sitemap font-white"></i></span>
                        <select class="form-control" id="select_project" onchange="loadProjectWS(this);">
                            <?php foreach ($projects as $p_id => $p) {
                                $selected = (($_SESSION['User']['dataDir'] == $p_id) ? "selected" : ""); ?>
                                <option value="<?php echo $p_id; ?>" <?php echo $selected; ?>><?php echo $p['name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </h1>

                <!-- END PAGE TITLE -->
                <!-- END PAGE HEADER -->


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
                                    <a href="<?php echo $GLOBALS['BASEURL']; ?>oeb_results/oeb_views/oeb_generalView.php" class="btn green"> Reload Workspace </a>
                                </div>
                            </div>

                            <div class="portlet-body">

                                <div class="input-group" style="margin-bottom:20px;">
                                    <span class="input-group-addon" style="background:#5e738b;"><i class="fa fa-wrench font-white"></i></span>
                                    <select id="toolSelector" class="form-control" style="width:100%;" onchange="loadWSTool(this)">
                                        <!-- <option value="">Filter files by tool</option> -->
                                        <?php foreach ($toolsList as $tl) { ?>
                                            <option value="<?php echo $tl["_id"]; ?>" <?php if ($_REQUEST["tool"] == $tl["_id"]) echo 'selected'; ?>><?php echo $tl["name"]; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>



                                </form>
                                <?php
                                $proj_name_active   = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], "path"));
                                $file_filter = array(
                                    "tool"       => getSelectedTool($toolsList, $_REQUEST["tool"]),
                                    "data_type" => "tool_statistics",
                                    "project"   => $proj_name_active
                                );
                                $filteredFiles = getGSFiles_filteredBy($file_filter);

                                //var_dump($filteredFiles);

                                ?>
                                <div class="row">
                                    <div class="col-xs-6 selectorLists">
                                        <ul class=" list-group">
                                            <span id="listOfTools">


                                                <?php foreach ($filteredFiles as $key => $value) {
                                                    echo '<li data="' . $value['parentDir'] . '" class="list-group-item runs">' . basename(getAttr_fromGSFileId($value['parentDir'], "path")) . '</li>';
                                                }
                                                ?>

                                            </span>
                                        </ul>
                                    </div>

                                    <div class="col-xs-6 selectorLists">
                                        <ul id="listOfToolsSelected" class="list-group">

                                        </ul>

                                    </div>

                                </div>
                                <button class=" btn green" onclick="myFunction()" id="btn-run-files" style="margin-top:20px;">Run Selected Files</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->









                <?php
                require "../../htmlib/footer.inc.php";

                ?>
                <style>
                    .selected {
                        background-color: #eef1f5;
                    }

                    .selectorLists {
                        min-height: 20vh;
                        min-width: 22vh;
                        border: 1px solid #5e738b
                    }
                </style>
                <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
                <script>
                    var redirect_url = "oeb_results/oeb_views/oeb_generalView.php"

                    function loadProjectWS(id) {
                        location.href = baseURL + 'applib/oeb_manageProjects.php?op=reload&pr_id=' + id.value + '&redirect_url=' + redirect_url;
                    };

                    function loadWSTool(op) {
                        console.log(op.value)
                        location.href = baseURL + redirect_url + "?tool=" + op.value;

                    }

                    new Sortable(listOfTools, {
                        group: 'runs', // set both lists to same group
                        multiDrag: true, // Enable multi-drag
                        selectedClass: 'selected', // The class applied to the selected items
                        fallbackTolerance: 3, // So that we can select items on mobile
                        animation: 50
                    });

                    new Sortable(listOfToolsSelected, {
                        group: 'runs',
                        multiDrag: true, // Enable multi-drag
                        selectedClass: 'selected', // The class applied to the selected items
                        fallbackTolerance: 3, // So that we can select items on mobile
                        filter: '.head',
                        //animation: 50
                    });

                    function myFunction() {
                        var tool = $("#toolSelector option:selected").val();


                        var arrayofexecutions = [];
                        $('ul#listOfToolsSelected li').each(function(i) {
                            arrayofexecutions.push($(this).attr('data')); // This is your rel value)
                        });
                        console.log(arrayofexecutions, tool);
                        viewResults(arrayofexecutions, tool);
                    }
                    viewResults = function(execution, tool) {
                        App.blockUI({
                            boxed: true,
                            message: 'Creating tool output, this operation may take a while, please don\'t close the tab...'
                        });
                        console.log("execution=" + execution + "&tool=" + tool);
                        $.ajax({
                            type: "POST",
                            url: baseURL + "/applib/loadOutput.php",
                            data: "execution=" + execution + "&tool=" + tool,
                            success: function(data) {

                                if (data == '1') {
                                    setTimeout(function() {
                                        location.href = 'tools/' + tool + '/output.php?execution=' + execution;
                                    }, 500);
                                } else if (data == '0') {
                                    setTimeout(function() {
                                        location.href = 'workspace/';
                                    }, 500);
                                }
                            }
                        });

                    };
                </script>