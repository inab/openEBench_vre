changeToolStatus = function(tool, op) {
	
	location.href= baseURL + "applib/changeToolStatusAdmin.php?tool=" + tool + "&status=" + op.value;

}