function currentUser() {
	return $.ajax({
		type: 'POST',
		url: 'applib/oeb_publishAPI.php?action=getAllFiles'
	});
}

$(document).ready(function() {
    $.when(currentUser()).done(function(user){
    console.log(user)

    })
        //build table
        var table = $('#filesTable').DataTable( {
            "ajax": {
                url: 'applib/oeb_publishAPI.php?action=getAllFiles&type=participant',
                dataSrc: ''
            },
            "columns" : [
                {"data" : null}, //0
                { "data" : "_id" }, //1
                { "data" : "path" }, //2
                { "data" : "datatype_name" }, //3
                { "data" : "oeb_id" }, //4
                { "data" : "oeb_eudatDOI" } //5
            ],
            'columnDefs': [
                {
                'targets': 0,
                'searchable': false,
                'orderable': false,
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name = "check" value="'+ $('<div/>').text(data).html() + '">';
                    }
                },
                {
                    "targets": 4,
                    render: function ( data, type, row ) {
                        if (data != null){
                            return '<a href=\"https://openebench.bsc.es/scientific/OEBC001" target="_blank">'+data+'</a>'
                        } else return '<a href=\"https://openebench.bsc.es/scientific/OEBC001" target="_blank">OEBD0020000EC2</a>'
                        
                    }

                },
                {
                    "targets": 2,
                    render: function ( data, type, row ) {
                        return "<b>"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop();
                    }
                },
                {
                    "targets": 1,
                    className: "hide_column"

                },

                {
                    "targets": 5,
                    render: function ( data, type, row ) {
                        if (data != null){
                            //table.row( 0 ).data().prop("disabled", "disabled")
                            return "<a href=\"https://eudat-b2share-test.csc.fi/records/"+(data.split(".").splice(-1)).toString()+"\" target=\"_blank\">"+data+"</a>"
                        } else return "-"
                        
                    }
                }
            ],
             'order': [[1, 'asc']]

          });

          
          
       
          // Handle click on "Select all" control
          $('#example-select-all').on('click', function(){
             // Get all rows with search applied
             var rows = table.rows({ 'search': 'applied' }).nodes();
             // Check/uncheck checkboxes for all rows in the table
             $('input[type="checkbox"]', rows).prop('checked', this.checked);
          });

         
       
          // Handle click on checkbox to set state of "Select all" control
          $('#filesTable tbody').on('change', 'input[type="checkbox"]', function(){
             // If checkbox is not checked
             if(!this.checked){
                var el = $('#example-select-all').get(0);
                // If "Select all" control is checked and has 'indeterminate' property
                if(el && el.checked && ('indeterminate' in el)){
                   // Set visual state of "Select all" control
                   // as 'indeterminate'
                   el.indeterminate = true;
                }
             }
          });
          

        $('#btn-run-files').on('click', function(){
            var arrayOfFiles = [];
            $("#message").text("");
            $.each($('tbody tr '), function() {
                //check if inputcheckbox is checked
                if($('td:first-child input[type="checkbox"]', this).prop('checked')) {
                    //get id
                    arrayOfFiles.push(($('td:nth-child(2)', this)).prop('innerText'));
                }
                
            });
            if (arrayOfFiles.length === 0 || arrayOfFiles.length > 1 ) {
                $("#message").text("No file selected");
            } else location.href = 'oeb_publish/eudat/oeb_EUDATdataset.php?files=' + arrayOfFiles;

        }); 

});




