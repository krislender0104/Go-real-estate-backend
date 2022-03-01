
function DownloadFile(action, sendDataKey, sendDataValue, tagetName, tagetType) {
    $("#formDataList").html("");
    $("#downloadForm").attr("action", "");

    var temp = "";

    $("#downloadForm").attr("action", action);
    temp = "<input type='hidden' name='"+sendDataKey+"' value='"+sendDataValue+"'/>"
    temp += "<input type='hidden' name='"+tagetName+"' value='"+tagetType+"'/>"
    $("#formDataList").html(temp);

    $('#downloadForm').submit();
    
}
