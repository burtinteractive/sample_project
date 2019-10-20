<script type="text/javascript">
    var dropfile_array = new Array();
    var dropfile_count = 0;
</script>

<h1 class="loader" >
    <div id="box-loader-text">Sending to Box.</div><br/>
    <span class="glyphicon glyphicon-refresh spin" style="left:50%;"></span>
</h1>
<div class="panel panel-flat">

    <div class="panel-body" >
        <div class="row">
            <div class="col-sm-2">
                <div class="thumbnail">
                    <div class="thumb">
                        <img src="https://apps.campusops.oregonstate.edu/facilities/images/buildings/<?php echo sanitize($building_code); ?>.jpg" class="" alt="<?php echo sanitize($building_name); ?>" >
                    </div>
                </div>
            </div>
            <div class="col-sm-5">
                <h3 class="panel-title"><?php echo sanitize($building_name); ?></h3>
                <?php if ($access->checkUrl('/divaAdmin/updateBuilding/', $current_user)) { ?>
                <div>
                    <a href="<?php echo $baseUrl; ?>/divaAdmin/updateBuilding/<?php echo $id; ?>" class="btn btn-default"><i class="fa fa-pencil"></i>  Edit Building</a>
                </div>
                <?php } ?>
                <h5>Viewing <?php echo $setCount; ?> Sets</h5>
                <div>
                    <?php echo sanitize($building_name); ?> contains <a href="<?php echo $baseUrl; ?>/diva/buildingItemsAll/<?php echo $id; ?>"><?php echo $itemCount; ?> items</a> from <a href="<?php echo $baseUrl; ?>/diva/viewBuilding/<?php echo $id; ?>"><?php echo $setCount; ?> sets</a>
                </div>
                <div class="tabbable" style="margin:20px 0 0 0;">

                        <ul class="nav nav-tabs nav-tabs-component">
                            <li class="active" id="box-tab"><a data-toggle="collapse" href="#box-collapse" role="button" aria-expanded="false" aria-controls="box-collapse" class="tab" >
                                    <img src="<?php echo $baseUrl; ?>/img/box-logo.png" style="margin:0 5px 0 0;">Box <i id="icon" class="glyphicon glyphicon-plus"></i></i></a>

                            </li>

                        </ul>
                </div>

                <div class="box-content collapse multi-collapse" id="box-collapse" >
                    <div class="row">
                        <div class="col-sm-12">
                            <div id="box_alert" class="alert alert-primary" role="alert" style="margin-bottom:0;">
                                Share these sets with other users via Box.
                            </div>
                            <input type="hidden" id="building_code" name="building_code" value="<?php echo sanitize($building_code); ?>" >
                            <input type="hidden" id="building_id" name="building_id" value="<?php echo sanitize($id); ?>" >
                            <label for="user_email1" style="margin:20px 0 0 0;">Duration of Share:</label>

                            <select class="form-control" id="expires">
                                <option value="90">3 months</option>
                                <option value="180">6 months</option>
                                <option value="270">9 months</option>
                                <option valeu="365">12 months</option>

                            </select>
                            <label for="user_email1" style="margin:20px 0 0 0;">User Email:</label>
                            <input type="text" id="user_email1" name="user_email" value="" class="form-control editable clickSelect" >
                            <label for="all_folders" style="margin:20px 0 0 0;">Select All:</label>
                            <input type="checkbox" id="all_folders" value="0"><br/><br/>
                            <button type="button" id="add_user_button" class="btn btn-primary">Add Another User</button>
                            <button type="button" id="box_button" class="btn btn-primary" disabled>Send Selected Files to Box</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-5">
<!--
                <h4>Set Navigator</h4>
                <ul class="pagination">
                    <?php
                    $adjIndex = 0;
                    $maxIndex =count($sets) - 1;
                    for ($i=$adjIndex; $i <= $maxIndex; $i++) {
                        $class='';
                        $label = $sets[$i]['SetCode'];
                        ?>
                        <li class="<?php echo $class; ?>">
                            <a href="<?php echo $baseUrl; ?>/diva/viewSet/<?php echo $sets[$i]['SetId']; ?>"
                               class="<?php echo $class; ?>"
                               title="Set <?php echo sanitize($sets[$i]['SetCode']  . "\n" . $sets[$i]['Year'] . "\n" . $sets[$i]['FirmName'] . "\n" . $sets[$i]['Types']); ?>"><?php echo sanitize($label); ?></a>
                        </li>

                        <?php
                    } ?>
                </ul>
    -->
            </div>
        </div>
        <div class="search_sets">

            <table class="table table-responsive" id="set-table">
                <thead>
                <tr>

                    <th style="width:115px">Check <input type="checkbox" id="all_folders2" vlaue="1"></th>
                    <th style="width:130px">Set Code</th>
                    <th style="width:100px">Year</th>
                    <th style="width:250px">Building Name</th>

                    <th style="width:250px">Firm / Contractor</th>
                    <th style="width:120px">Document Count</th>
                    <th></th>

                </tr>

                </thead>

                <tbody>
                <?php foreach($results as $index=>$res) { ?>
                    <tr>

                        <td><input type="checkbox" class="set_check" id="<?php echo sanitize($res['BuildingCode'] . '-' . $res['SetCode']); ?>" value="0"></td>
                        <td><a href="<?php echo $baseUrl; ?>/diva/viewSet/<?php echo $res['SetId']; ?>/<?php if (isset($type_id)) echo $type_id; ?>" class="items" name="<?php echo $res['SetCode']; ?>" id="<?php echo $res['SetId']; ?>"><?php echo sanitize($res['BuildingCode'] . '-' . $res['SetCode']); ?></a></td>
                        <td><?php echo sanitize($res['Year']); ?></td>
                        <td><?php echo sanitize($res['BuildingName']); ?></td>
                        <td><?php echo sanitize($res['FirmName']); ?></td>
                        <td><?php echo sanitize($res['ItemCount']); ?></td>
                        <td><?php
                            $setDocumentTypes = explode(',', $res['Types']);
                            $delimiter = '';
                            foreach ($setDocumentTypes as $sdt) {
                                $found = false;
                                $dt = trim($sdt);
                                foreach ($documentTypes as $dt) {
                                    if (strtolower($dt['Description']) == strtolower($sdt)) {
                                        echo $delimiter . '<a href="' . $baseUrl . '/diva/' . $res['BuildingId'] . '/' . $dt['TypeId']. '">' . sanitize($sdt) . '</a>';
                                        $found = true;
                                        break;
                                    }
                                }
                                if (empty($found)) echo $delimiter . sanitize($sdt);

                                $delimiter = ', ';

                            }

                            //echo sanitize($res['Types']);

                            ?></td>

                    </tr>
                <?php } ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
    <div class="modal-backdrop fade" style="display:none;" data-dismiss="modal" ></div>

<script>
var $base_url = "<?php echo $baseUrl; ?>";
$( document ).ready(function() {
    $(document).tooltip();
  <?php
        foreach($items as $i){
            if ($i['HasURL']) {
                echo "dropfile_array[dropfile_count] = '" . sanitize($i['BuildingCode'] . '-' . $i['SetCode'] . '-' . $i['ItemCode']) . "';";
                echo "dropfile_count++;";
            }
        }

   ?>

    var table = $('#set-table').DataTable({
//  data: dataSet,
        "lengthMenu": [[100, 200, 300, 400, 500, -1], [100, 200, 300, 400, 500, "All"]],
        "oLanguage": {
            "sSearch": "Filter:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
        }
    });


});
</script>
<script src="<?php echo $baseUrl; ?>/js/diva.js"></script>