
var $box_file_array = [];

$(document).on('click','.set_check',function(){
    var $original_box_array_size = $box_file_array.length;
    $(this).closest("tr").toggleClass("table_row_background");
    if($(this).val()=="1"){
        $(this).val("0");
        reduceBoxArray($(this).attr("id"));
    }else{
        $(this).val("1");
        increaseBoxArray($(this).attr("id"));
        if($box_file_array.length ==0 || $original_box_array_size == $box_file_array.length ){
            alert("This set does not contain any downloadable files.");
        }
    }

    setBoxButton();

});

function setBoxButton(){
    if($box_file_array.length > 0){
        $("#box_button").prop('disabled' ,false);
    }else{
        $("#box_button").prop('disabled' ,true);
    }
}

function reduceBoxArray($code){
    for(var i = 0; i < $box_file_array.length; i++){
        if($box_file_array[i].includes($code)) {
            $box_file_array.splice(i, 1);
            i--;
        }
    }
}

function increaseBoxArray($code){

    $temp_array = dropfile_array.slice();
    var temp_box_file_array =  $.grep($temp_array,function(n){
        return n.includes($code);
    });
    $box_file_array = $box_file_array.concat(temp_box_file_array);
}

var $user_count = 1;
var $user_array = [];
var $latest_email_field ="user_email1";
var $total_number_of_files = 0;
var $item = null;
$("#add_user_button").on('click',function()
{
    addUser();

});


$(document).on("load","#"+$latest_email_field, function(){
    $item = $(this);

});
function addUser(){
    $user_count++;

    if($(".emails_new").length > 0){
        $(".emails_new").append("<label for=\"user_email" + $user_count + "\" style=\"margin:20px 0 0 0;\">User Email:</label>\n" +
            "<input type=\"text\" id=\"user_email" + $user_count + "\" name=\"user_email" + $user_count + "\" value=\"\" class=\"form-control editable clickSelect\">\n");
        $latest_email_field = "user_email" + $user_count;

    }else {
        $("#" +$latest_email_field).after("<label for=\"user_email" + $user_count + "\" style=\"margin:20px 0 0 0;\">User Email:</label>\n" +
            "<input type=\"text\" id=\"user_email" + $user_count + "\" name=\"user_email" + $user_count + "\" value=\"\" class=\"form-control editable clickSelect\">\n");
        $latest_email_field = "user_email" + $user_count;
    }
}


function createUserArray($start){
    while($user_count > $start){
        if($("#user_email"+$user_count).val()!= "") {
            $user_array[$count] = $("#user_email" + $user_count).val();
            $count++;
        }
        $user_count--;

    }
    $user_count = 1;
}

function removeShare(callback){

    $.post($base_url+'/diva/deleteFolder',{'folder_id':$("#folder_id").val()},function(data){
        callback(data);
        //return data;
    });
}

function addCollaborator( callback){
    createUserArray(1);
    $.post($base_url+'/diva/addCollaborator',{'emails':$user_array,'folder_id':$("#folder_id").val()},function(data){
        $user_count = 1;
        $user_array = [];
        callback(data);
       // return data;
    });
}

function createBoxFolder(callback){

    $building_name = $(".panel-title:first").text();
    $building_name = $building_name.replace(" ","_");
    $total_number_of_files = $box_file_array.length;
    $.post($base_url+'/divaAdmin/createBoxFolder',{'emails':$user_array,'building_name':$building_name,"expires":$("#expires").val(),"building_code":$("#building_code").val(),total_items:$total_number_of_files,building_id:$("#building_id").val()},function(data){
        
            callback(data);
    });
}

$("#box_button").on('click', function(){
    $check = true;
    if(dropfile_array.length == 1){
        $box_file_array = dropfile_array.slice();
    }
    $total_number_of_files = $box_file_array.length;
    //check if connected to box
    //send users first and create folder
    $count = 0;



    createUserArray(0);
    if ($check) {
        $("html,body").scrollTop(0);
        $(".modal-backdrop").addClass("in");
        $(".modal-backdrop").css("display", "block");
        $(".modal-backdrop").css("opacity", "0.5");
        $(".modal-backdrop").css("z-index", "100000");
        $(".loader").css("display", "block");
        $(".loader").css("z-index", "100001");
        $("#loader-animation").css("display", "block");
        $(".loader-animation").css("z-index", "100001");
        $("#box-loader-text").text("Creating Folder.");
        createBoxFolder(startUpload);
    }


});

function startUpload(){
            uploadFilesArchiveRec(1);
}

function  uploadFilesArchiveRec($file_num) {
    // terminate if array exhausted
    if ($box_file_array.length === 0){
        $(".modal-backdrop").removeClass("in");
        $(".modal-backdrop").css("display","none");
        $(".loader").css("display","none");
        if(dropfile_array.length == 1){
            $("#box_alert").text("File has been shared successfully");
        }else {
            $("#box_alert").text("All files have been shared successfully");
        }

        return;

    }

    // pop top value
    var $file_name = $box_file_array[0];
    $("#box-loader-text").empty();

    file_count = $file_num+"";
    if( file_count.length  == 1){
        file_count = "00"+ file_count;
    }else if( file_count.length == 2){
        file_count = "0"+ file_count;
    }

    $building_parts = $file_name.split("-");
    $("#box-loader-text").append("Uploading a total of "+$total_number_of_files+" file(s) to Box <br/>" +"Sending "+$file_num+"/"+$total_number_of_files+" to Box "+$file_name+".pdf ");


    $box_file_array.shift();
    //need to check if connected to Box on the first attempt
    //if order has been changed will need to loop through order and send the array of new file order.
    //this will grab old files out of the DB and add to box. Get stuff out of DB

    $.post($base_url+'/diva/uploadToBox',{file_name:$file_name},function(data){
        // call completed - so start next request
        setTimeout(function() {
        $file_num = $file_num + 1;
        uploadFilesArchiveRec($file_num);
        },3000);
    });

}

$(document).on('click','#box-tab',function(){
    if($("#icon").hasClass('glyphicon-plus')) {
        $("#icon").removeClass('glyphicon-plus');
        $("#icon").addClass('glyphicon-minus');
    }else{
        $("#icon").removeClass('glyphicon-minus');
        $("#icon").addClass('glyphicon-plus');
    }
});

$("#all_folders,#all_folders2").on('click',function(){
    $element = $(this);
    if($($element).val() == "1"){
        //$($element).val("0");
        $("#all_folders").val("0");
        $("#all_folders2").val("0");
        $("#all_folders").prop("checked",false);
        $("#all_folders2").prop("checked",false);
    }else{
        //$($element).val("1");
        $("#all_folders").val("1");
        $("#all_folders2").val("1");
        $("#all_folders").prop("checked",true);
        $("#all_folders2").prop("checked",true);
    }

    $('.set_check').each(function (index, obj) {

        if (this.checked === true ) {

            if($($element).val()== "1"){
            }else {

                this.checked = false;
                $box_file_array = [];
                $(obj).val("0");
                $(obj).closest("tr").toggleClass("table_row_background");
            }

        }else{
            this.checked = true;
            $box_file_array = dropfile_array.slice();
            $(obj).val("1");
            $(obj).closest("tr").toggleClass("table_row_background");

        }
    });

    setBoxButton();

});