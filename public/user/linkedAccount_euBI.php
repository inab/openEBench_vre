<?php
// set form default values

$defaults = array();
if (isset($_SESSION['formData'])){
	$defaults = $_SESSION['formData'];
         unset($_SESSION['formData']);
}elseif($_REQUEST['action'] == 'update'){
	$defaults['alias_token'] = $_SESSION['User']['linked_accounts']['euBI']['alias']; 
	$defaults['secret']      = $_SESSION['User']['linked_accounts']['euBI']['secret']; 
}


// print html form
?>

<div class="portlet box blue-oleo">
   <div class="portlet-title">
      <div class="caption">
         <div style="float:left;margin-right:20px;"> <i class="fa fa-link"></i> euro-BioImaging</div>
      </div>
   </div>
   <div class="portlet-body form">
      <div class="form-body">
         <p>euro-BioImaging provides Alias Tokens for allowing external applications to <strong>authenticate a session on your behalf</strong> for a limited period of time. If your are sure you want to allow <?php echo $GLOBALS['NAME']?> VRE access, fill in the following form.</p>
         <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
               <div class="form-group">
                  <a target="_blank" href="https://wiki.xnat.org/documentation/how-to-use-xnat/generating-an-alias-token-for-scripted-authentication">How to generate an euro-BioImaging Alias Token?</a><br/>
                  <a target="_blank" href="https://xnat.bmia.nl/">Go to euro-BioImaging</a></br>
                  <a href="javascript:openTermsOfUse();"><?php echo $GLOBALS['NAME']?> VRE terms of use</a>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="form-group">
                  <label class="control-label">Alias Token</label>
                  <input type="text" name="alias_token" id="alias_token" class="form-control" value="<?php echo $defaults['alias_token'];?>">
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="form-group">
                  <label class="control-label">Secret</label>
                  <input type="text" name="secret" id="secret" class="form-control" value="<?php echo $defaults['secret'];?>">
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="form-group">
                  <label class="control-label">Time limit (hours)</label>
                  <span  class="form-control" readonly>48</span>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>


