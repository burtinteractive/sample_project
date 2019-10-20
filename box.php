<?php
/**
 * Box Model
 *
 * @package portal
 * @subpackage portal.app.model
 * @copyright Copyright &copy; 2018, Oregon State University
 * @author Adam Burt <adam.burt@oregonstate.edu>
 *
 */
require_once(APP . 'vendors' . DS . 'Box' . DS . 'Box_API.class.php');
require_once(APP . 'vendors' . DS . 'firebase'. DS .'JWT.php') ;
require_once(APP . 'vendors' . DS . 'guzzlehttp'. DS .'Client.php') ;
class Box
{
    var $folder_id = "";
    var $path = "";
    function getAuthenticationURL(){
        return Configure::read('Box.authentication_url');
    }

    function getFolderId()
    {
        return $this->folder_id;
    }

    function setFolderId($fid){
        $this->folder_id = $fid;
    }

    function getConfigPath(){
        return APP . 'webroot' . DS .'svcs'.DS.$this->path;
    }

    function setConfigPath($path){
         $this->path = $path;
    }

    function connectToBoxJWT(){
        //the  path is  set on object initialization based on  project that needs it.
        $json = include($this->getConfigPath());
        $config = json_decode($json);
        $private_key = $config->boxAppSettings->appAuth->privateKey;
        $passphrase = $config->boxAppSettings->appAuth->passphrase;
        $key = openssl_pkey_get_private($private_key, $passphrase);
        $claims = [
            'iss' => $config->boxAppSettings->clientID,
            'sub' => $config->enterpriseID,
            'box_sub_type' => 'enterprise',
            'aud' => $this->getAuthenticationURL(),
            // This is an identifier that helps protect against
            // replay attacks
            //'jti' => base64_encode(random_bytes(64)),
            'jti' => base64_encode(openssl_random_pseudo_bytes(64)),
            // We give the assertion a lifetime of 45 seconds
            // before it expires
            'exp' => time() + 45,
            'kid' => $config->boxAppSettings->appAuth->publicKeyID
        ];

        $jwt = new \Firebase\JWT\JWT();
        $assertion = $jwt->encode($claims, $key, 'RS512');

        $params = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
            'client_id' => $config->boxAppSettings->clientID,
            'client_secret' => $config->boxAppSettings->clientSecret
        ];

        $this->box = new Box_API($config->boxAppSettings->clientID, $config->boxAppSettings->clientSecret, "");
        // Make the request
        $client = new GuzzleHttp\Client();

            $response = $client->request('POST', $this->getAuthenticationURL(), [
                'form_params' => $params
            ]);

            // Parse the JSON and extract the access token
            $data = $response->getBody()->getContents();
            $access_token = json_decode($data)->access_token;
            $this->jwt_token = $access_token;
            $this->client = $client;



    }

    /*Creates a Folder in the Box account.
    * Needs the new file name and the parent id.
    * Parent ID is set by calling program set in config file.
    */
    function createFolder($folder_name,$parent_id = 0){

        $this->connectToBoxJWT();

        $folder_id = $this->getFolderId();
        $url = 'https://api.box.com/2.0/folders';
        if($parent_id != 0) {
            $folder_params = "{\"name\": \"$folder_name\", \"parent\":{ \"id\": \"$parent_id\" }}";
        }else{
            $folder_params = "{\"name\": \"$folder_name\", \"parent\":{ \"id\": \"$folder_id\" }}";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $folder_params);


        $headers = array();
        $headers[] = "Authorization: Bearer ".$this->jwt_token;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $results = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        return json_decode($results,true);

    }

    /*Creates a collaboration in the Box account.
     * Takes in the folder id and email of collaborator.
     *
     */
    function createCollaboration($folder_id, $email,$role){

        $this->connectToBoxJWT();
        $url = "https://api.box.com/2.0/collaborations";

        $collab_data = "{\"item\": { \"id\": \"$folder_id\", \"type\": \"folder\"}, \"accessible_by\": { \"type\": \"user\", \"login\": \"$email\" }, \"role\": \"editor\"}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $collab_data);


        $headers = array();
        $headers[] = "Authorization: Bearer ".$this->jwt_token;
        $headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $results = curl_exec($ch);


        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        return json_decode($results);


    }

    /*Sends the file to Box.com
    * Needs the file name, the new name and the parent id of the folder
    * where it will be stored.
    */
    function uploadFileToBox($file_name, $name, $parent_id ){
        $this->connectToBoxJWT();
        $url = "https://upload.box.com/api/2.0/files/content";
        $file = new \CURLFile($file_name,'application/pdf',$name);
        $file_data = array('file' => $file, 'name' => $name."pdf" , 'parent_id' => $parent_id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file_data);


        $headers = array();
        $headers[] = "Authorization: Bearer ".$this->jwt_token;
        $headers[] = "Content-Type: multipart/form-data";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $results = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        return $results;

    }

    function deleteFolder($folder_id){

        $this->connectToBoxJWT();
        $params = [];
        $url = "https://api.box.com/2.0/folders/".$folder_id."?recursive=true";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $headers = array();
        $headers[] = "Authorization: Bearer ".$this->jwt_token;
        $headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $results = curl_exec($ch);


        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);

        return json_decode($results);
    }

    function createGroup(){
        $this->connectToBoxJWT();
        $url = "https://api.box.com/2.0/groups";

        $group_string = "{\"name\": \"Box Employees\", \"provenance\": \"Google\", \"external_sync_identifier\": \"Google-Box-Users\", \"description\": \"All box Users\", \"invitability_level\": \"admins_and_members\", \"member_viewability_level\": \"admins_only\"}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $group_string);


        $headers = array();
        $headers[] = "Authorization: Bearer ".$this->jwt_token;
        $headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);

    }
}