<?php
/**
 * DivaFile
 *
 * @package portal
 * @subpackage portal.app.model
 * @copyright Copyright &copy; 2018, Oregon State University
 * @author Adam Burt <adam.burt@oregonstate.edu>
 *
 */

class DivaFile
{
    var $filename = "";
    var $temp_name = "";
    var $file_number = 0;
    var $type = "";
    var $mime_type = "";
    var $temp_path = "";
    var $pic_path = "";
    var $directory_path = "";
    var $size = "";
    var $set_id = "";
    var $building_id = "";
    var $year = "";
    var $firm_id = "";
    var $number = "";
    var $box_id = "";
    var $old_set = false;
    var $description = "";
    var $building_name = "";
    var $drawing_id = "";
    var $original_name = "";
    var $current_user = "";
    var $current_box_folder_id = "";
    var $only_set = false;
    function __construct($fname) {

        $this->filename = $fname;
    }
    function getFilename(){

        return $this->filename;
    }
    function setFilename($fname){

        $this->filename = $fname;
    }
    function setOriginalName($on){

        $this->original_name = $on;
    }
    function getOriginalName(){

        return $this->original_name;
    }

    function setDirectoryPath($path){

        $this->directory_path = $path;
    }
    function getDirectoryPath(){

        return $this->directory_path;
    }
    function getFileSize(){

        return $this->size;
    }
    function setFileSize($size){

        $this->size = $size;
    }

    function getTempName(){

        return $this->temp_name;
    }
    function setTempName($temp){

        $this->temp_name = $temp;
    }

    function getTempPath(){

        return $this->temp_path;
    }
    function setTempPath($temp){

        $this->temp_path = $temp;
    }

    function getFileNumber(){

        return $this->file_number;
    }
    function setFileNumber($num){

        $this->file_number = $num;
    }
    function getType(){

        return $this->type;
    }
    function setType($type){

        $this->type = $type;
    }
    function getMimeType(){

        return $this->mime_type;
    }
    function setMimeType($mime){

        $this->mime_type = $mime;
    }
    function getBoxId(){
        return $this->box_id;
    }
    function setBoxId($box_id){

        $this->box_id = $box_id;
    }
    function getPic(){

        return $this->pic_path;
    }
    function setPic($pic){

        $this->pic_path = $pic;
    }
    function setCurrentUser($user){
        $this->current_user = $user;
    }
    function getCurrentUser(){
        return $this->current_user;
    }

    function getCurrentBoxFolderId()
    {

        return $this->current_box_folder_id;
    }
    function setCurrentBoxFolderId($bfid){

        $this->current_box_folder_id = $bfid;
    }

    /****************data functions*****************************/

    function setYear($year){

        $this->year = $this->checkString(4,$year);
    }
    function getYear(){

        return $this->year;
    }
    function setDescription($description){

        $this->description = $description;
    }
    function getDescription(){

        return $this->description;
    }

    function setBuildingId($bid){

        $this->building_id = $this->checkString(4,$bid);
    }
    function getBuildingId(){

        return $this->building_id;
    }
    function setBuildingName($bn){

        $this->building_name = trim($bn);
    }
    function getBuildingName(){

        return $this->building_name;
    }
    function setSetId($sid){

        $this->set_id = $this->checkString(4,$sid);
    }
    function getSetId(){

        return $this->set_id;
    }
    function setIsSet($is_set){

        $this->only_set = $is_set;
    }
    function getIsSet(){
        return $this->only_set;
    }
    function setFirmId($fid){

        $this->firm_id = $this->checkString(4,$fid);
    }
    function getFirmId(){

        return $this->firm_id;
    }

    function checkString($length, $string){
        $string = trim($string);

        return  addslashes(substr($string,0, $length));
    }

    function setNumber($num){
        while(strlen($num)< 3){

            $num = "0".$num;
        }
        $this->number = $num;

    }
    function getNumber(){

        return $this->number;
    }
    function setDrawingId($did){

        $this->drawing_id = $did;
    }
    function getDrawingId(){

        return $this->drawing_id;
    }


}