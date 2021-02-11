<?php

require __DIR__ . "/../../config/bootstrap.php";

redirectOutside();

//Retrive communities and datasets
$communities = getCommunities();
$datasets = getDatasets();


// Print page
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
              <a href="home/">Home</a>
              <i class="fa fa-circle"></i>
            </li>
            <li>
              <span>Get Data</span>
              <i class="fa fa-circle"></i>
            </li>
            <li>
              <span>From Repository</span>
              <i class="fa fa-circle"></i>
            </li>
            <li>
              <span>Repository Name</span>
            </li>
          </ul>
        </div>
        <!-- END PAGE BAR -->
        <!-- BEGIN PAGE TITLE-->
        <h1 class="page-title">
          <a href="javascript:;" target="_blank"><img src="assets/layouts/layout/img/icon.png" width=100></a>
          Benchmarking Datasets
        </h1>
        <!-- END PAGE TITLE-->
        <!-- END PAGE HEADER-->
            <div class="row">
              <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light portlet-fit bordered">
                  <div class="portlet-title">
                    <div class="caption">
                      <i class="icon-share font-red-sunglo hide"></i>
                      <span class="caption-subject font-dark bold uppercase">Browse Datasets</span>
                    </div>
                  </div>
                  
                  <div class="portlet-body">
                  <div id="loading-datatable">
                      <div id="loading-spinner">LOADING</div>
                    </div>
                    

                    <table class="table table-striped table-hover table-bordered" id="table-repository">
                      <thead>
                        <tr>
                          <th> Id </th>
                          <th> Title </th>
                          <th> Description </th>
                          <th> Version </th>
                          <th> Type </th>
                          <th> Community </th>
                          <th> Visibility </th>
                          <th> Import </th>

                        </tr>
                      </thead>

                      <tbody>
                        <!-- process and display each result row -->

                        <?php 
                        
                        foreach ($datasets as $d) {
                          if ($d['type'] != "participant") {
                            
                        ?>
                            <tr>
                              <td> <?php echo $d['_id']; ?> </td>
                              <td> <?php echo $d['name']; ?> </td>
                              <td> <?php echo $d['description']; ?> </td>
                              <td> <?php echo $d['version']; ?> </td>
                              <td> <?php echo getDataTypeName($d['type']); ?> </td>
                              <td> <?php
                              
                                    foreach ($d['community_ids'] as $ids) {
                                      foreach ($communities as $c) {
                                        if($c['_id'] == $ids) echo $c['acronym']. " ";
                                      }
                                    }
                                    
                                    ?> </td>
                              <td> <?php echo $d['visibility']; ?> </td>
                              <td style="vertical-align:middle;">

                                <?php
                                $r = $d['datalink'];
                                if (array_key_exists('uri', $r)) $dataset_uri= $r['uri'];
                                else $dataset_uri = "";
                                ?>
                                <!-- Send via GET url and metadata to getData.php // CURRENTLY DONE VIA POST-->

                                <?php
                                /*
                              $GET_metadata = http_build_query(array(
                                "uploadType"       => "repository",
                                "url"              => $dataset_uri,
				"data_type"        => "metrics_reference",
				"description"      => $obj->description,
                                "oeb_dataset_id"   => $obj->_id,
                                "oeb_community_ids"=> (array) $obj->community_ids
                              ));
                              */
                                ?>
                                <!-- <a <?php //if(!$dataset_uri){echo 'class="disabled" style="opacity: 0.4;"';}
                                        ?> href="applib/getData.php?<?php //echo $GET_metadata; 
                                                                    ?>">
                              <i class="font-green fa fa-download tooltips disabled" aria-hidden="true" style="font-size:22px;" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Import dataset to workspace</p>"></i></a> -->

                                <!-- Send via POST url and metadata to getData.php -->
                                <form action="applib/getData.php" method="post">
                                  <input type="hidden" name="uploadType" value="repository" />
                                  <input type="hidden" name="url" value="<?php echo htmlspecialchars($dataset_uri); ?>" />
                                  <input type="hidden" name="data_type" value="<?php echo htmlspecialchars($d['type']); ?>" />
                                  <input type="hidden" name="description" value="<?php echo htmlspecialchars($d['description']); ?>" />
                                  <input type="hidden" name="oeb_dataset_id" value="<?php echo htmlspecialchars($d['_id']); ?>" />
                                  <?php foreach ((array) $d['community_ids'] as $c) { ?>
                                    <input type="hidden" name="oeb_community_ids[]" value="<?php echo htmlspecialchars($c); ?>" />
                                  <?php } ?>

                                  <button type="submit" <?php if ($dataset_uri == "") echo "disabled"; ?> class="btn green dropdown-toggle" value="submit">
                                    <i class="fa fa-download tooltips font-white" data-original-title="Import dataset to workspace"></i>
                                  </button>
                                </form>

                                

                              </td>

                            </tr>


                        <?php }
                        }  ?>

                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
              </div>
            </div>
            </div>
            <!-- END CONTENT BODY -->
      </div>
      <!-- END CONTENT -->

      <?php

      require "../htmlib/footer.inc.php";
      require "../htmlib/js.inc.php";




      ?>