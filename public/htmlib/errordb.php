<?php

//require __DIR__."/../../config/bootstrap.php";
require "../../config/globals.inc.php";

?>

<?php require "../htmlib/header.inc.php"; ?>

<body class=" login">

        <!-- BEGIN LOGO -->
        <div class="logo">
            	<a href="<?php echo $GLOBALS['URL']; ?>">
		<img src="assets/layouts/layout/img/logoplusvre.png" alt="" /> </a>
		<!--<img src="assets/pages/img/logo-big.png" alt="" /> </a> --> <!-- MUG image still !! -->
        </div>
        <!-- END LOGO -->

        <!-- BEGIN LOGIN -->
        <div class="content">
            <!-- BEGIN LOGIN FORM -->
                <h3 class="form-title font-green">Oups...</h3>
                <p>Something went wrong... try to access the platform later</p>
		<p style="text-align:center;font-weight:bold;">
		<?php if (isset($_SESSION['errorData'])) {
			foreach ($_SESSION['errorData'] as $subTitle => $txts) {
				print "$subTitle : &nbsp;&nbsp;";
				foreach ($txts as $txt) {
                                       	print "$txt<br/>";
                        	}
			}
			unset($_SESSION['errorData']);
		} ?>

		</p>
                <!--<p><?//php echo $_REQUEST['msg'];  echo $_SESSION;?> </p> -->
	</div>

<?php 

require "../htmlib/footer-login.inc.php"; 
require "../htmlib/js.inc.php";

?>
