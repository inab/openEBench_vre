<?php
// set form default values

$defaults = array();
if (isset($_SESSION['formData'])){
	$defaults = $_SESSION['formData'];
         unset($_SESSION['formData']);
}elseif($_REQUEST['action'] == 'update'){
	$defaults['access_token'] = $_SESSION['User']['linked_accounts'][$_REQUEST['account']]['access_token']; 
   $defaults['eudat_email'] = $_SESSION['User']['linked_accounts'][$_REQUEST['account']]['eudat_email']; 
}


// print html form
?>

<div class="portlet box blue-oleo">
   <div class="portlet-title">
      <div class="caption">
         <div style="float:left;margin-right:20px;"> <i class="fa fa-link"></i> B2SHARE (EUDAT)</div>
      </div>
   </div>
   <div class="portlet-body form">
      <div class="form-body">
         <p>EUDAT provides an API Token for allowing external applications to <strong>authenticate on your behalf</strong> for a limited period of time. If your are sure you want to allow <?php echo $GLOBALS['NAME']?> VRE access, fill in the following form.</p>
         <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
               <div class="form-group">
                  <a target="_blank" href="https://eudat.eu/services/userdoc/b2share-http-rest-api#Creating_an_access_token">How to generate my EUDAT Token?</a><br/>
		  <a target="_blank" href="<?php echo $GLOBALS['b2share_host']; ?>">Go to EUDAT</a></br>
                  <a href="javascript:openTermsOfUse();"> <?php echo $GLOBALS['NAME'];?> VRE terms of use</a>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="form-group">
                  <label class="control-label">EUDAT Server</label>
                  <span  class="form-control" readonly><?php echo $GLOBALS['b2share_host']; ?></span>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="form-group">
                  <label class="control-label">EUDAT-B2SHARE registration Email</label>
                  <input type="email" name="eudat_email" id="eudat_email" class="form-control" value="<?php echo ($defaults['eudat_email']? $defaults['eudat_email'] : $_SESSION['User']['Email']);?>">
                  <label class="control-label">Access Token</label>
                  <input type="text" name="access_token" id="access_token" class="form-control" value="<?php echo $defaults['access_token'];?>">
               </div>
            </div>
         </div>
      </div>
   </div>
</div>


