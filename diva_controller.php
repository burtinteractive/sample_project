<?php
/**
 * Diva Controller

 * @package portal
 * @subpackage portal.app.model
 * @copyright Copyright &copy; 2012, INTO-OSU
 * @author Adam Burt <adam.burt@oregonstate.edu>
 *
 */
class DivaController extends AppController {

    var $uses = array(
        'Diva',
        'Box',
        'DivaFile',
        'DivaSet',
        'DivaItem',
        'DivaType',
        'DivaStatus',
        'DivaBuilding',
        'DivaModification',
        'PortalLog',
        'AimProperty',
        'DivaBoxSharedFolder',
        'DivaBoxCollaborator',
        'UserPreference',
        'Site'
    );

    var $helpers = array('Form', 'Time', 'Access');
    // var $components = array('DivaHelper');
    var $layout = 'limitless.1.4';

    var $ids =[];
    var $pics = [];
    var $fileArray  = [];
    var $forceDomain = 'diva.facilities.oregonstate.edu';
    /**
     * Index / Dashboard
     */
    function index() {

    }


    function viewBuilding($id, $type = 1){
        $building = $this->DivaBuilding->find('first', array('conditions'=>array('BuildingId'=>$id), 'fields'=>array('Description','BuildingCode','BuildingId')));
        if (empty($building)) {
            $this->error404();
        }
        //debug($building);
        $fields['setId'] = $id;
        $sets = $this->DivaSet->getAll(array(
            'buildingCode'=>$building['DivaBuilding']['BuildingCode'],
        ));
        $this->set('sets', $sets);
        $this->set('setCount', count($sets));

        // Gets all Items
        $items = $this->DivaItem->getAll(
            array(
                'buildingCode'=>$building['DivaBuilding']['BuildingCode']
            )
        );
        $this->set('items',$items);

        $this->set("itemCount", count($items));

        $this->set('id', $id);
        $this->set('building_name', $building['DivaBuilding']['Description']);
        $this->set('building_code', $building['DivaBuilding']['BuildingCode']);

        $this->PortalLog = new PortalLog();
        $this->PortalLog->hit($this->current_user['User']['username'], 'facilities::building',$building['DivaBuilding']['BuildingCode']);

        $results = $this->DivaSet->getAll(array('buildingCode'=>$building['DivaBuilding']['BuildingCode'], 'isActive'=>true));

        $types = $this->DivaType->find('all', array('conditions'=>array('Enabled'=>1)));
        $this->set('types', $types);

        $this->set('building_name', $results[0]['BuildingName']);
        $this->set('building_code', $results[0]['BuildingCode']);
        $this->set('results', $results);


        $this->addBreadcrumb('Buildings', '/diva/buildings');
        $this->addBreadcrumb($building['DivaBuilding']['Description'], '/diva/viewBuilding/' . $id . "/0");
    }



    /**
     * Shows all the sets for the specified building
     *
     * @param $id
     * @param int $type
     *
     */




    function getDivaBoxObject(){
        if($this->Session->check('divaBox')){
            return $this->Session->read("divaBox");
        }else{
            $divaBox = new Box();
            $divaBox->setConfigPath('diva_box.php');
            $divaBox->setFolderId( Configure::read('Box.Diva.folder_id'));
            $this->Session->write("divaBox",$divaBox);
            return $this->Session->read("divaBox");
        }

    }

  /*Creates a new box Folder
  *Database is updated and a Diva Object is created
  * We set most of the Diva Object attributes here
   * The collaborators are also set
  */
    function createBoxFolder(){
        $this->layout = null;
        $this->autoRender = false;
        $divaFile = new DivaFile("");
        $divaBox = $this->getDivaBoxObject();

        $user = $this->current_user['User']['username'];
        $emails = $_POST['emails'];
        $building_name = $_POST['building_name'];
        $emails[] = $this->current_user['User']['osuprimarymail'];

        //grab next ID from diva_Box_Shared_Folders
        $id = $this->DivaBoxSharedFolder->find('first', array('order' => array('id'=>'DESC')));
        $id = $id['DivaBoxSharedFolder']['id'] + 1;

        $folder_name = "OSU_".$building_name."_".$id;
        $results = $divaBox->createFolder($folder_name);
        $folder_id = $results['id'];
        $divaFile->setCurrentBoxFolderId($folder_id);

        $this->data['created_by'] = $user;
        $today =  date('Y-m-d');
        $new_date = date('Y-m-d', strtotime($today .' + '. $_POST['expires']." days"));
        $this->data['date_expires'] = $new_date." ".date('H:i:s');
        $this->data['folder_id'] = $folder_id;
        $this->DivaBoxSharedFolder->save($this->data);
        $this->data['building_code'] = $_POST['building_code'];
        $this->data['items_shared'] = $_POST['total_items'];
        $this->data['building_id'] = $_POST['building_id'];
        $this->Diva->updateBoxSharedFolder($this->data);


        foreach ($emails as $email){
            $this->data = [];
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if($email == $this->current_user['User']['email_address'] ){
                    $this->DivaBoxCollaborator->create();

                    $divaBox->createCollaboration($folder_id, $email, 'editor');
                    $this->data['box_shared_folder_id'] = $id;
                    $this->data['email'] = $email;
                    $this->DivaBoxCollaborator->save($this->data);
                }else {
                    $this->DivaBoxCollaborator->create();

                    $divaBox->createCollaboration($folder_id, $email, 'viewer');
                    $this->data['box_shared_folder_id'] = $id;
                    $this->data['email'] = $email;
                    $this->DivaBoxCollaborator->save($this->data);
                }
            }
        }

        $this->Session->write("diva",$divaFile);

        return $folder_id;
    }


    function addCollaborator(){


        $divaBox = $this->getDivaBoxObject();
        $this->layout = null;
        $this->autoRender = false;
        $folder_id = $_POST['folder_id'];
        $emails = $_POST['emails'];
        $id = $this->DivaBoxSharedFolder->find('all', array('conditions'=>array('folder_id'=>$folder_id),'fields'=>array('id')));
        $messages = [];
        $id = $id[0]['DivaBoxSharedFolder']['id'];
        $emails = array_filter($emails);
        foreach ($emails as $email){
            $this->data = [];
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->DivaBoxCollaborator->create();

                $results = $divaBox->createCollaboration($folder_id, $email, 'viewer');
                //var_dump($results);

                if(strpos($results->status,'40') !== false){
                    $messages[] = "error";
                    $messages[] = $results->message;
                }else{
                    $this->data['box_shared_folder_id'] = $id;
                    $this->data['email'] = $email;
                    $this->DivaBoxCollaborator->save($this->data);
                    $messages[] = "User ".$email." added successfully";
                }


            }else{
                $messages[] = "error";
                $messages[] = "User email ".$email." was not in the proper format.";

            }
        }

        return json_encode($messages);
    }

    function uploadToBox(){
        $this->layout = null;
        $this->autoRender = false;
        $divaFile = $this->Session->read('diva');
        $file = $_POST['file_name'];
        $divaFile->setFilename($file);
        $building_pieces = explode("-",$file);
        $item_no = explode(".",$building_pieces[2]);
        $res = $this->Diva->grabArchiveData($building_pieces[0],$building_pieces[1],$item_no[0]);

        $divaFile->setYear($res[0]['Year']);
        $divaFile->setBuildingId($res[0]['bid']);
        $divaFile->setBuildingName($res[0]['building_name']);
        $divaFile->setFirmId($res[0]['fid']);
        $divaFile->setSetId($building_pieces[1]);
        $divaFile->setNumber($building_pieces[2]);
        $divaFile->setDrawingId($res[0]['drawing_id']);
        $divaFile->setDescription($res[0]['item_description']);
        $divaFile->setDirectoryPath( $this->Diva->getFileSystemPath().DS.$building_pieces[0].DS."PDF".DS);

        $divaFile->setType("application/pdf");
        $divaFile->setMimeType("application/pdf");

        $this->Diva->sendToBox($divaFile,$this->getDivaBoxObject());
    }

    /*Deletes a folder in Box
    * It also updates the Database to reflect the deletion by
    * marking folder inactive in DB
    */
    function deleteFolder(){
        $this->layout = null;
        $this->autoRender = false;
        $divaBox = $this->getDivaBoxObject();
        $results = [];
        $messages = [];
        if(is_numeric($_POST['folder_id'])) {

            $results = $divaBox->deleteFolder($_POST['folder_id']);
            if($results == "" || empty($results)) {
                $this->DivaBoxSharedFolder->deleteFolder($_POST['folder_id']);

            }

        }
        if($results != "" || !(empty($results))){
            if(strpos($results->status,'40') !== false){
                $messages[] = "error";
                $messages[] = $results->message;
            }

        }else{
            $messages[] ="Folder deleted successfully.";
        }
        return json_encode($messages);
    }

    /* Grab all the collaborators from database
   *
   *
   */
    function jsonGetCollaborators($id){
        $this->layout = null;
        $this->autoRender = false;
        $results = $this->Diva->getCollaboratorsDB($id);
        echo json_encode($results);


    }





}