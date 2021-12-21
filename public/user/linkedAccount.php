<?php

require __DIR__ . "/../../config/bootstrap.php";

redirectOutside();


require "../htmlib/header.inc.php";?>

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
                            <span>User</span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
			<span><a href="<?php echo $GLOBALS['URL'];?>user/usrProfile.php#tab_1_4">My Keys</a></span>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Linked Accounts</span>
                        </li>
                    </ul>
                </div>
                <!-- END PAGE BAR -->
                <!-- BEGIN PAGE TITLE-->
                <h1 class="page-title"> Linked Accounts </h1>
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

                    <form name="linkedAccount" id="linkedAccount" action="applib/linkedAccount.php" method="post">
                  
			<input type="hidden" name="account" id="account" value="<?php echo $_REQUEST['account'];?>">
			<input type="hidden" name="action"  id="action"  value="<?php echo $_REQUEST['action'];?>">


			<?php 

		    	## EUROBIOIMAGING FORM
	    
		 	if ($_REQUEST['account'] == "euBI"){
				require("linkedAccount_euBI.php");
			}

			## B2SHARE FORM
	    
			if ($_REQUEST['account'] == "b2share"){
				require("linkedAccount_b2share.php");
			}?>

                        <div class="portlet-body form">
		                 <div class="form-actions">
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit</button>
                                    <button type="reset" class="btn default">Reset</button>
                                    <button onclick="window.history.go(-1); return false;" class="btn default">Cancel</button>
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
