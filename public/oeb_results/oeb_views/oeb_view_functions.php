<?php


function getSelectedTool($toolsList, $requestParamTool)
{
    // if tool is selected get tool from url param or else set 1st tool in tool list
    $toolSelected = "";
    if (isset($requestParamTool) && $requestParamTool != "") {
        $toolSelected = $requestParamTool;
    } else {
        $toolSelected = $toolsList[0]["_id"];
    }
    return $toolSelected;
};

function getAllFilesForSelectedTool($allFiles, $toolsList, $requestParamTool)
{
    //get selected tool
    $toolSelected = getSelectedTool($toolsList, $requestParamTool);

    //filter all files by selectedtool
    //array of filtered tools
    $filesForSelectedTool = array();
    if ($allFiles && !empty($allFiles)) {
        foreach ($allFiles as $key => $value) {
            if ($value['tool'] == $toolSelected) {
                array_push($filesForSelectedTool, $value);
            }
        };
    }
    return $filesForSelectedTool;
}
