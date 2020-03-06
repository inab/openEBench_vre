<?php 
redirectOutside();

?> 

<table id="tblReportResultsDemographics" class="table table-striped table-hover table-bordered"></table>

<script type="text/javascript">
    $(document).ready(function() {
        $('#tblReportResultsDemographics').DataTable( {
            "ajax": {
                url: 'https://dev-openebench.bsc.es/vre/applib/processes.php?list',
                dataSrc: ''
            },
            autoWidth: false,
            "columns" : [
                { "data" : "_id" },
                { "data" : "status" },
                { "data" : "inputs_meta.public_ref_dir.data_type" },
                { "data" : "inputs_meta.public_ref_dir.file_type" },
                { "data" : "owner"}
            ],
            "columnDefs": [
                { "title": "_id", "targets": 0 },
                { "title": "status", "targets": 1 },
                { "data_type": "title", "targets": 2 },
                { "fyle_type": "type", "targets": 3 },
                { "title": "owner", "targets": 4 },
                { render: function (data, type, row) {
                    var processId = row._id;
                    //status = 0; private
                    //status = 1; public
                    //status = 2; coming soon
                    var menu = '<select onChange="obtainStatus(value, name)" name="'+processId+'"><option value="" disabled selected> status...</option><option>Private</option><option>Public</option><option>Coming soon</option></select>';
                    switch(data) {
                        case 0: 
                            return menu + " <span value='0' class='label label-danger'><b>Private</b></span>"; 
                            break;
                        case 1: 
                            return menu + " <span value='1' class='label label-primary'><b>Public</b></span>"; 
                            break;
                        case 2: 
                            return menu + " <span value='2' class='label label-warning'><b>Coming soon</b></span>";
                            break;
                        default: 
                            return menu;
                    }
                }, "targets": 1}
            ]
        });
    });
    
    function obtainStatus(statusValue, processId) {
        console.log(statusValue + " " + processId); //Hay que insertar esto
    }
    
</script>