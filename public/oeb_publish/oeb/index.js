function currentUser() {
	return $.ajax({
		type: 'POST',
		url: 'applib/publishFormAPI.php?action=getAllFiles'
	});
}

$(document).ready(function() {
    $.when(currentUser()).done(function(user){
        console.log(user)
    })
    //create tabs
    //TODO: get number of communities

    var communities = new Array("Quest for Orthologs", "TCGA", "CIBERER"); 
    var files = new Array("")

    //create tabs
    for (let index = 0; index < communities.length; index++) {
        var tabTitles = $('<li class="active uppercase"><a href="#'+communities[index]+'" data-toggle="tab">'+communities[index]+' </a></li>');
        $("#tabs").append(tabTitles);
        
    }



    

})


/*
$(document).ready(function() {
    $.when(currentUser()).done(function(user){
    console.log(user)
    })

       //build table
       var table = $('#filesTable').DataTable( {
        "ajax": {
            url: 'applib/publishFormAPI.php?action=getAllFiles&type=participant',
            dataSrc: ''
        },
        "columns" : [
            {"data" : null}, //0
            { "data" : "_id" }, //1
            { "data" : "path" }, //2
            { "data" : "datatype_name" }, //3
            { "data" : "status" }, //4
            { "data" : "oeb_id" } //5
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
                    return 'Available to publish'
                    
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
                        return "OEBD0020000EC2"
                    
                }
            }
        ],
         'order': [[1, 'asc']]

      });
      //build table
      var table = $('#assessmentTable').DataTable( {
        "ajax": {
            url: 'applib/publishFormAPI.php?action=getAllFiles&type=assessment',
            dataSrc: ''
        },
        "columns" : [
            {"data" : null}, //0
            { "data" : "_id" }, //1
            { "data" : "path" }, //2
            { "data" : "datatype_name" }, //3
            { "data" : "status" }, //4
            { "data" : "oeb_id" } //5
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
                    return 'Available to publish'
                    
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
                        return "OEBD0020000EC2"
                    
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
})


*/
