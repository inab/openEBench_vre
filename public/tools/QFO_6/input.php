<?php

require __DIR__ . "/../../../config/bootstrap.php";
redirectOutside();

InputTool_checkRequest($_REQUEST);

$from = InputTool_getOrigin($_REQUEST);

list($rerunParams, $inPaths) = InputTool_getPathsAndRerun($_REQUEST);


$dirName = InputTool_getDefExName();

// get tool details
$toolId = "QFO_6";
$tool   = getTool_fromId($toolId, 1);

// if inputs not specified, make sure user has the appropiate data in the workspace
$has_data = 1;
if (!$inPaths[0]['path']) {
	$has_data = isUserDataMatchingToolRequirements($tool['input_files']);
}



?>

<?php require "../../htmlib/header.inc.php"; ?>

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
							<a href="workspace/">User Workspace</a>
							<i class="fa fa-circle"></i>
						</li>
						<li>
							<span>Tools</span>
							<i class="fa fa-circle"></i>
						</li>
						<li>
							<span><?php echo $tool['name']; ?></span>
						</li>
					</ul>
				</div>
				<!-- END PAGE BAR -->

				<!-- BEGIN PAGE TITLE-->
				<h1 class="page-title"> <?php echo $tool['title']; ?> </h1>
				<!-- END PAGE TITLE-->

				<!-- END PAGE HEADER-->
				<div class="row">
					<!-- SHOW ERRORS -->
					<div class="col-md-12">
						<?php if (isset($_SESSION['errorData'])) { ?>
							<div class="alert alert-warning">
								<?php foreach ($_SESSION['errorData'] as $subTitle => $txts) {
									print "$subTitle<br/>";
									foreach ($txts as $txt) {
										print "<div style=\"margin-left:20px;\">$txt</div>";
									}
								}
								unset($_SESSION['errorData']);
								?>
							</div>
						<?php } ?>


						<form action="#" class="horizontal-form" id="tool-input-form">
							<input type="hidden" name="tool" value="<?php echo $toolId; ?>" />
							<input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />


							<div class="portlet box yellow" id="no-data">
								<div class="portlet-title">
									<div class="caption">
										<div style="float:left;margin-right:20px;"> <i class="fa fa-exclamation-triangle"></i> No data available</div>
									</div>
								</div>
								<div class="portlet-body form">
									<div class="form-body">
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">


													<p>No appropriate data found to run this Tool/Visualizer. You can <a class="btn green" href="./getdata/uploadForm.php"><i class="fa fa-upload"></i> Upload</a> your own data or use
														the <a class="btn green" href="./getdata/sampleDataList.php" id="btn-sample"><i class="fa fa-database"></i> Example Datasets</a> provided by the VRE:</p>
													<p>For more information please visit the <a class="btn green" href="./help/tools.php"><i class="fa fa-info-circle"></i> Help</a> section.</p>

												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
							// }
							?>

							<!-- BEGIN PORTLET 1: PROJECT -->
							<div class="blockToolForm">
								<div class="portlet box blue-oleo">
									<div class="portlet-title">
										<div class="caption">
											<div style="float:left;margin-right:20px;"> <i class="fa fa-check-square-o"></i> Project</div>
										</div>
									</div>
									<div class="portlet-body form">
										<div class="form-body">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="control-label">Select Project</label>
														<?php InputTool_getSelectProjects(); ?>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="control-label">Execution Name</label>
														<input type="text" name="execution" id="dirName" class="form-control" value="<?php echo $dirName; ?>">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label class="control-label">Description</label>
														<textarea <?php echo print($r); ?> id="description" name="description" class="form-control" style="height:120px;" placeholder="Write a short description here..."></textarea>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- END PORTLET 1: PROJECT -->



								<!-- BEGIN PORTLET 2: SECTION 1 -->
								<div class="portlet box blue-oleo form-block-header" id="form-block-header1">
									<div class="portlet-title">
										<div class="caption">
											<i class="fa fa-cogs"></i> Tool settings
										</div>
									</div>
									<div class="portlet-body form form-block" id="form-block1">
										<div class="form-body">

											<!-- PRINT TOOL INPUT FILES -->
											<h4 class="form-section">File inputs</h4>

											<div class="row">
												<div class="col-md-12">
													<?php $ff = matchFormat_File($tool['input_files']['input']['file_type'], $inPaths); ?>
													<?php InputTool_printSelectFile($tool['input_files']['input'], $rerunParams['fasta1'], $ff[0], false, true); ?>
												</div>
											</div>
											<input type="hidden" name="input_files_public_dir[goldstandard_dir]" value="<?php echo $tool['input_files_public_dir']['goldstandard_dir']['value']; ?>" />
											<input type="hidden" name="input_files_public_dir[assess_dir]" value="<?php echo $tool['input_files_public_dir']['assess_dir']['value']; ?>" />
											<input type="hidden" name="input_files_public_dir[public_ref_dir]" value="<?php echo $tool['input_files_public_dir']['public_ref_dir']['value']; ?>" />

											<!-- PRINT TOOL ARGUMENTS -->
											<h4 class="form-section">Settings</h4>

											<?php InputTool_printSettings($tool['arguments'], $rerunParams); ?>
										</div>
									</div>
								</div>
							</div>
					</div>
					<!-- END PORTLET 2: SECTION 1 -->

					<div class="alert alert-danger err-nd display-hide">
						<strong>Error!</strong> You forgot to fill out some mandatory fields, please check them before submit the form.
					</div>

					<div class="alert alert-warning warn-nd display-hide">
						<strong>Warning!</strong> At least one analysis should be selected.
					</div>

					<div class="form-actions blockToolForm">
						<button type="submit" class="btn green" style="float:right;">
							<i class="fa fa-check"></i> Compute</button>
					</div>
					</form>
				</div>
			</div>
		</div>
		<!-- END CONTENT BODY -->
	</div>
	<!-- END CONTENT -->
	<div class="modal fade bs-modal-lg" id="modalDTStep2" tabindex="-1" role="basic" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title">Select file(s)</h4>
				</div>
				<div class="modal-body">
					<div id="loading-datatable">
						<div id="loading-spinner">LOADING</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
					<button type="button" class="btn green btn-modal-dts2-ok" disabled>Accept</button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>

		<!-- /.modal-dialog -->
	</div>


	<?php

	require "../../htmlib/footer.inc.php";
	require "../../htmlib/js.inc.php";

	?>



	<script>
		formToolBlocker(<?php echo $has_data; ?>)
	</script>