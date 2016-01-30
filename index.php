<?php

session_start();
require_once 'connection.php';

//some useful fonctions
function getPost(&$value, $name) {if (isSetPost($name)) {$value = trim($_POST[$name]);return TRUE;} else return FALSE;}
function isPost($name){return (isset($_POST[$name]));}
function isGet($name){return (isset($_GET[$name]));}
function get(&$value, $name){if (isGet($name)) {$value=$_GET[$name]; return TRUE;} else return FALSE;}//$value can be empty!
function getPostNull(&$value, $name) {if (isPost($name)) {$value = $_POST[$name];return TRUE;} else return FALSE;}
function isSetPost($name){return (isPost($name) && ( ($p=trim($_POST[$name])) && !empty($p) )) ;}
function endsWith($haystack, $needle) {return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);}

if (get($id, 'edit')){
    echo (User::find($id)->to_json());
    return;
}

//create object model
$model = new User();
//set field search (mandatory)
$model->setSearchField('Host');
//set primary key field, readOnly (optional, otherwise firstField will considered as PK by default)
$model->setPK('User');

//set fields options (labels, required): to make a field required in edit/create form, just add '*' at the end of the label

$model->setLabels(array('host', 'user', 'password')); //<-- minimal selection

// $model->setLabels(array('id' => 'N°' /* <- label */, 'user_name' => 'Login*' /* <- label + requierd */, 'password_hash' => '*' /* <- only required */, 'email' /* it will be shown 'Email' using prettyName */)); //<-- advanced options


//form submit
if (!empty($_POST) && isPost('hdn-inpt')){
        
    if (getPost($mode,'mode')){
        if ($mode == 'create') {
            //create object model
            $model->insert($_POST)->save();
            echo 'Object created successfully.';
            
        } else if ($mode =='update') {
            //update object model
            $model->update($_POST)->save();
            echo 'Object updated successfully.';
        }
    }
    return;
}

if (getPostNull($uid, 'uid')){
    echo $model->getJson($uid);
    return;
}

if (getPost($rid, 'delete')){
    $model->remove($rid);
    echo '0';
    return;
}

$this_file = basename(__FILE__);


?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Listing <?= $model->getName() ?></title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/modern-business.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

   <!-- Bootgrid css -->
    <link rel="stylesheet" href="css/jquery.bootgrid.min.css">
        
</head>

<body>

    <!-- Page Content -->
    <div class="container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">

            <div class="col-lg-12">
                <div class="row">
                    <h1>Listing <?= $model->getName() ?></h1>
                    <hr />
                </div>
                   
               <div class="row">
                <table id="grid" class="table table-condensed table-hover table-striped" 
                    data-selection="true" data-multi-select="false">
                    <thead>
                       <?php
                            foreach($model->getLabels(true) as $field => $label){
                        ?>
                            <th data-column-id="<?=$field ?>" data-order="asc" <?= $field==$model->getPK()?'data-identifier="true"':'' ?> ><?=$label ?></th>
                        <?php
                            }
                        ?>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false">Commandes</th>
                    </thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
                </div>
                             
            </div>
        </div><!-- /.row -->

        <div class="row">
        </div><!-- /.row -->
         

        <!-- modal-form -->
        <div class="modal fade" id="modal-form" role="dialog">
           <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= $model->getName() ?> Form</h4>
                    </div>
                    <form id="form-edit" role="form" class="form-vertical" method="post" action="<?= $this_file ?>" data-async>
                        <div class="modal-body">
                           <?php
                                foreach($model->getLabels() as $field => $label){
                            ?>
                            <div class="form-group">
                               <label for="<?= $field ?>" class="control-label"><?= $label ?>:</label>
                               <input <?= $model->getPK() == $field? 'readonly="readonly"':'' ?> type="text" class="form-control" id="<?= $field ?>" name="<?= $field ?>" <?= endsWith($label,'*')?'required="required"':'' ?> />
                            </div>
                            <?php
                                }
                            ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" id="btn-close">Close</button>
                        <button type="submit" class="btn btn-primary" id="btn-update" name="btn-update">Apply</button>
                        <button type="submit" class="btn btn-success" id="btn-create" name="btn-create" style="display: none">Create</button>
                        <input type="hidden" id="hdn-inpt" name="hdn-inpt" value="hdn_<?= rand() ?>" />
                    </div>
                    </form>
                </div><!--.modal-content-->
            </div><!--.modal-dialog-->
         </div><!--.modal-->
     
        <!-- Modal Delete -->
        <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="Delete <?= $model->getName() ?>">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Delete <?= $model->getName() ?></h4>
              </div>
              <div class="modal-body">
                  <p>Do you really want to delete <span id="row-id"></span>?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button id="btn-delete" type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>
              </div>
            </div>
          </div>
        </div>  
		
    </div><!-- /.container -->
    
    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- jQuery Bootgrid -->
    <script src="js/jquery.bootgrid.min.js"></script>    
    
    <script>
$(function(){
            
            //in case you want to disable globally jquery caching
//            $.ajaxSetup({ cache: false });
            
            var bootGrid = $("#grid");
            bootGrid.bootgrid({
                ajax: true,
                url: "<?= $this_file ?>",
                post: function(){
                    return {uid:0};
                },
                formatters: {
                  "commands": function(col, row)  
                    {
				        return "<button type=\"button\" onclick=\"editModel(this)\" class=\"btn btn-xs btn-default command-edit\" data-row-id=\"" + row.<?= $model->getPK() ?> + "\"><span class=\"glyphicon glyphicon-pencil\"></span></button> " + 
									"<button type=\"button\" onclick=\"deleteModel(this);\" class=\"btn btn-xs btn-default command-delete\" data-row-id=\"" + row.<?= $model->getPK() ?> + "\"><span class=\"glyphicon glyphicon-trash\"></span></button>";
							}
                }
				//load translation file (french example)
                // ,labels: loadJson("js/labelsFr.json")
            }); //bootgrid (init)
            
             $("#grid-header").find('.actions.btn-group').append('<button class="btn btn-primary" type="button" onclick="createModel(this)">New <?= $model->getName() ?> <span class="icon glyphicon glyphicon-pencil"></span></button>');
            
        $('#modal-form').on('show.bs.modal', function(event){
            $('#form-edit')[0].reset();
        }).on('hidden.bs.modal', function(){
            bootGrid.bootgrid('reload');
        });
        
        //on Submit form event handler
        $('form[data-async]').submit(function(event) {
//            console.log(event);
			var $form = $(this);
            var $target = $('#modal-form');
            var $action = $form.attr('action');
            //form mode
            var mode =$('#btn-create').is(':visible')?'create':'update';
			var update = $form.serialize() +'&mode='+mode+'&_='+new Date().getTime();
			$.post($action, update).done(function(data){
                console.log(data);
                if (data !== null){
                    $target.modal('hide');
                }
            });
            event.preventDefault();            
		});
		
		$('#btn-delete').on("click", function(){
		var row = $(this).attr('data-row-id'); 
		$.post("<?= $this_file ?>", {delete: row}).done(function(data){
		    if (data === "0"){
		        console.log("Object deleted successfully.");
		    } else {
		        console.log(data);                    
		    }
		    $("#grid").bootgrid('reload');
		});                
		});		
            
        }); //$()
        
        function editModel(sender){
            $('#btn-update').show();
            $('#btn-create').hide();
            var row = $(sender).attr('data-row-id');
            if ($('#modal-form').modal()){
                $.getJSON("<?= $this_file ?>", {
                    edit: row,
                    _t:new Date().getTime()
                    }, 
                    function(data){
                    $.each(data, function(k, v){
                        $('#'+k).val(v);
                    });
                });
            }
        } //editModel()
        
        function createModel(sender){
            //console.log(sender);
            $('#btn-update').hide();
            $('#btn-create').show();
            $('#modal-form').modal();
        } //createModel()
        
        function deleteModel(sender){
            var row = $(sender).attr('data-row-id');
            $('#btn-delete').attr('data-row-id', row);
            $('#row-id').text(row);        
            $('#modal-delete').modal();
        }
		
	/*/load json file
	function loadJson(jsonFile){
		var json = null;
		$.ajax({
			'async': false,
			'global': false,
			'url': jsonFile,
			'dataType': "json",
			'success': function (data) {
				json = data;
			}
		});
		return json;
	}*/
		
    </script>

                
</body>

</html>
