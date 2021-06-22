<?php

require __DIR__ . "/../../config/bootstrap.php";

redirectOutside();

$tools = getTools_List();
$commmunities = getCommunities();


?>

<?php require "../htmlib/header.inc.php"; ?>

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
                            <span>Helpdesk</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Contact form</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Helpdesk contact form </h1>
                <!-- END PAGE TITLE-->
                <!-- END PAGE HEADER-->
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        $error_data = false;
                        if ($_SESSION['errorData']) {
                            $error_data = true;
                            ?>
                            <?php if ($_SESSION['errorData']['Info']) { ?>
                                <div class="alert alert-info">
                                <?php } else { ?>
                                    <div class="alert alert-danger">
                                    <?php } ?>

                                    <?php
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
                    <form name="helpdesk" id="helpdesk" action="applib/openTicket.php" method="post" class="needs-validation" novalidate>

                        <div class="portlet box blue-oleo">
                            <div class="portlet-title">
                                <div class="caption">
                                    <div style="float:left;margin-right:20px;"> <i class="fa fa-ticket"></i> Ticket content</div>
                                </div>
                            </div>
                            <div class="portlet-body form">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label"><b>Your name</b></label>
                                                <input type="text" name="Name" id="Name" value="<?php echo $_SESSION["User"]["Name"] . " " . $_SESSION["User"]["Surname"]; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label"><b>Your email</b></label>
                                                <input type="text" name="Email" id="Email" value="<?php echo $_SESSION["User"]["Email"]; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label"><b>Type of request</b></label>
                                                <select name="Request" id="Request" class="form-control">
                                                    <option value=""><b>Select a request</b></option>
                                                    <option value="general" <?php if ($_REQUEST["sel"] == "general") { ?>selected<?php } ?>>I have a technical question</option>
                                                    <option value="tools" <?php if ($_REQUEST["sel"] == "tools") { ?>selected<?php } ?>>I have an issue related with some tool</option>
                                                    <option value="space" <?php if ($_REQUEST["sel"] == "space") { ?>selected<?php } ?>>I need more disk space</option>
                                                    <option value="community" <?php if ($_REQUEST["sel"] == "community") { ?>selected<?php } ?>>Register a new community</option>
                                                    <option value="roleUpgrade" <?php if ($_REQUEST["sel"] == "roleUpgrade") { ?>selected<?php } ?>>Request to become contributor</option>
                                                    <!-- <option value="tooldev" <?php 
                                                                                    ?>selected<?php 
                                                                                                                                        ?>>I want to become a tool developer</option> -->
                                                </select>
                                               
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row display-hide" id="row-tools">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label"><b>Tools List</b></label>
                                                <select name="Tool" id="Tool" class="form-control" disabled>
                                                    <option value="">Select a Tool </option>
                                                    <?php foreach ($tools as $t) { ?>
                                                        <option value="<?php echo $t["_id"]; ?>"><?php echo $t["name"]; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row display-hide" id="row-communities">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label"><b>Community List </b><span style="color:red;">*</span></label>
                                                <select name="commmunityList" id="commmunityList" class="form-control" >
                                                    <option value="">Select a community </option>
                                                    <?php if (isset($_REQUEST["BE"])){
                                                        $com_id = getBenchmarkingEvents($_REQUEST["BE"], "community_id");
                                                        $comm_name = getCommunities($com_id, "name");
                                                        echo "<option value='$com_id' selected>$comm_name</option>";
                                                    }?>
                                                    <?php foreach ($commmunities as $c) { ?>
                                                        <option value="<?php echo $c["_id"]; ?>"><?php echo $c["name"]; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row display-hide" id="row-benchEvent">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label"><b>Benchmarking event List </b><span style="color:red;">*</span></label>
                                                <select name="BEList" id="BEList" class="form-control" >
                                                    <option value="">Select a benchmarking event </option>
                                                    <?php if (isset($_REQUEST["BE"])){
                                                        $BE_id = $_REQUEST["BE"];
                                                        $BE_name = getBenchmarkingEvents($BE_id, "name");
                                                        echo "<option value='$BE_id' selected>$BE_name</option>";
                                                    }?>
                                                     
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row display-hide" id="row-challenges">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label"><b>Challenge List </b><span style="color:red;">*</span></label>
                                                <select name="challengeList" id="challengeList" class="form-control" multiple>
                                                    <option value="">Select a challenge </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label"><b>Subject </b><span style="color:red;">*</span></label>
                                                <input type="text" name="Subject" id="Subject" class="form-control" placeholder="" required>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <?php if ($_REQUEST["sel"] != "tooldev") { ?>
                                                    <label class="control-label" id="label-msg"><b>Message details </b><span style="color:red;">*</span></label>
                                                <?php } else { ?>
                                                    <label class="control-label" id="label-msg">Please tell us which kind of tool(s) you want to integrate in the VRE</label>
                                                <?php } ?>
                                                <textarea class="form-control" name="Message" id="Message" rows="6"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit</button>
                                    <button type="reset" class="btn default">Reset</button>
                                </div>
                            </div>
                        </div>

                    </form>


                </div>
                <!-- END CONTENT BODY -->
            </div>
            <!-- END CONTENT -->

            <div class="modal fade bs-modal-sm" id="myModal1" tabindex="-1" role="basic" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <?php
                        if (isset($_SESSION['errorData'])) {
                            ?>
                            <div class="alert alert-warning">
                                <?php foreach ($_SESSION['errorData'] as $subTitle => $txts) {
                                    ?>
                                    <h4 class="modal-title"><?php echo $subTitle; ?></h4>
                                </div>
                                <div class="modal-body">
                                    <?php foreach ($txts as $txt) {
                                        print $txt . "</br>";
                                    } ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn dark btn-outline" data-dismiss="modal">Accept</button>
                                </div>
                            <?php
                        }
                        unset($_SESSION['errorData']);
                        ?>

                        <?php } ?>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>

            <div class="modal fade bs-modal-sm" id="myModal5" tabindex="-1" role="basic" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>

            <?php

            require "../htmlib/footer.inc.php";
            require "../htmlib/js.inc.php";

            ?>

