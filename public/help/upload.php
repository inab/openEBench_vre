<?php

require __DIR__ . "/../../config/bootstrap.php";
redirectOutside();

?>

<?php require "../htmlib/header.inc.php"; ?>

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid page-sidebar-fixed">
	<div class="page-wrapper">

		<?php require "../htmlib/top.inc.php"; ?>
		<?php require "../htmlib/menu.inc.php"; ?>

		<!-- BEGIN CONTENT -->
		<div class="page-content-wrapper">
			<!-- BEGIN CONTENT BODY -->
			<div class="page-content" id="body-help">
				<!-- BEGIN PAGE HEADER-->
				<!-- BEGIN PAGE BAR -->
				<div class="page-bar">
					<ul class="page-breadcrumb">
						<li>
							<a href="/home/">Home</a>
							<i class="fa fa-circle"></i>
						</li>
						<li>
							<span>Help</span>
							<i class="fa fa-circle"></i>
						</li>
						<li>
							<span>Get Data</span>
						</li>
					</ul>
				</div>
				<!-- END PAGE BAR -->
				<!-- BEGIN PAGE TITLE-->
				<h1 class="page-title"> Get Data
				</h1>
				<!-- END PAGE TITLE-->
				<!-- END PAGE HEADER-->

				<div id="html-content-help">

					<h3>
						<p>The VRE provides two ways for getting data:</p>
					</h3>
					<ul>
						<li><a href="help/upload.php#uploadfile">Upload Data</a></li>
						<ul>
							<li><a href="help/upload.php#firststep">First Step: Upload Data</a></li>
							<li><a href="help/upload.php#secondstep">Second Step: Edit File Metadata</a></li>
						</ul>
						<!-- <li><a href="help/upload.php#repository">From Repository</a></li> -->
						<li><a href="help/upload.php#sample">Import Sample Data</a></li>
					</ul>





					<p><span id="uploadfile">&nbsp;</span></p>



					<ul>
						<li>
							<h2>Upload Data</h2>
							<ul>
								<li>
									<h3 id="firststep"> <i>First Step: Upload data</i></h3>
									<p><span>&nbsp;</span></p>
									<h4> Upload files from your local computer </h4>
									<p><img src="assets/layouts/layout/img/help/upload01.png" style="width:800px;max-width:100%;" /></p>
									<p>To upload a file from the computer, users just have to drag and drop the files to the specified area or click on it.</p>
									<h4> Create new file</h4>
									<p><img src="assets/layouts/layout/img/help/upload02.png" style="width:800px;max-width:100%;" /></p>
									<p>To create a new file from text, users just have to insert the file name and the text data (i.g. a DNA sequence) and click the button <em>SEND DATA</em>.</p>
									<h4> Load file from an external URL </h4>
									<p><img src="assets/layouts/layout/img/help/upload03.png" style="width:800px;max-width:100%;" /></p>
									<p>To load a file from an external URL, users just have to insert the URL in the input file and click the button <em>SEND DATA</em></p>
									<p><span id="repository">&nbsp;</span></p>
								</li>
								<li>
									<h3 id="secondstep"> <i>Second Step: Edit File Metadata</i></h3>
									<p><span>&nbsp;</span></p>
									<ol>
										<li>
											<p>Choose the file format.</p>
										</li>
										<li>
											<p>Choose the Data type.</p>
										</li>
									</ol>
									<h4> Fill in the Metadata</h4>
									<p><img src="assets/layouts/layout/img/help/metadataform.png" style="width:800px;max-width:100%;" /></p>
								</li>
							</ul>
						</li>
						<li>
							<h3 id="sample"> <i> Import Sample Data</i></h3>
							<p><span>&nbsp;</span></p>
							<p> You can import sample datasets which are provided by the VRE as shown below</p>
							<h4> Import predefined dataset for the tool you want to run</h4>
							<p><img src="assets/layouts/layout/img/help/importExampleDataset1.png" style="width:800px;max-width:100%;" /></p>
							<p>Once the sample datasets have been imported you will be redirected to the workspace</p>
							<h4> Information about the imported dataset </h4>
							<p><img src="assets/layouts/layout/img/help/importExampleDataset2.png" style="width:800px;max-width:100%;" /></p>
							<p>You can then run a tool or visualizer directly from your workspace</p>
							<h4> Launch Tool/Visualizer from the workspace</h4>
							<p><img src="assets/layouts/layout/img/help/importExampleDataset3.png" style="width:800px;max-width:100%;" /></p>
							<p><span id="repository">&nbsp;</span></p>
						</li>
					</ul>



					<h3>
						<p><i>Edit File Metadata</i> </p>
					</h3>
					<ul>
						<ol>
							<li>
								<p>Select the project you want to work with (default: MyFirstProject)</p>
							</li>
							<li>
								<p>Set a name for your execution</p>
							</li>
							<li>
								<p>Add a description</p>
							</li>
							<li>
								<p>Select your input file</p>
							</li>
							<li>
								<p>Select one or more challenges to participate in</p>
							</li>
							<li>
								<p>Set a name for you method</p>
							</li>
						</ol>
						<p><img src="assets/layouts/layout/img/help/secondStep.png" style="width:800px;max-width:100%;" /></p>
					</ul>

					<!-- <h3>From OpenEBench</h3>

					<p><img src="assets/layouts/layout/img/help/upload04.png" style="width:800px;max-width:100%;" /></p>

					<p><strong>  VRE</strong> provides the users with a repository with thousands of experiments ready to load to the Workspace.</p>

					<p><img src="assets/layouts/layout/img/help/upload05.png" style="width:800px;max-width:100%;" /></p>

					<p>Clicking on any of the experiments of the list, users access the experiment detail. Here, just clicking the button
						<img src="assets/layouts/layout/img/help/upload06.png" /> the experiment is automatically loaded to the user's <em>Repository</em> folder of the Workspace.
						As the uploading process of this kind of files is asyncron because of the huge size of some of them, users are redirected directly to the Workspace
						instead of the second step of the process (File Metadata Edition)</p>


					<p><span id="sample">&nbsp;</span></p> -->


				</div>

			</div>
			<!-- END CONTENT BODY -->
		</div>
		<!-- END CONTENT -->

		<?php

		require "../htmlib/footer.inc.php";
		require "../htmlib/js.inc.php";

		?>