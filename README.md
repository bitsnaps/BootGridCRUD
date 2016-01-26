# BootGridCRUD
PHP class for full AJAX CRUD operations (using phpActiveRecord ORM) based on jQuery-BootGrid (by rstaib) and Bootstrap3.

Simple usage:

Setup connection (connection.php)

    $db_host      = "127.0.0.1";
    $db_user      = "root";
    $db_pass      = "";
    $db_name    = "mysql";
    

Controller file (on top of index.php in this example):

    //create object model
    $model = new User();
    //set field search (mandatory)
    $model->setSearchField('user_name');
    //set primary key field, readOnly (optional, otherwise firstField will considered as PK by default)
    $model->setPK('id');
    //set fields options (labels, required): to make a field required in edit/create form, just add '*' at the end of the label
    $model->setLabels(array('id' => 'N°', 'user_name' => 'Login', 'password')); //<-- optionals
    
    //create object model
    $user = $model->insert($_POST); //you have to check $_POST first
    $user->created_at = date('Y-m-d H:i:s');
    $user->save();
    //...
    $model->update($_POST)->save();
    $model->remove($id);
    

Features:
- It uses PHP-ActiveRecord ORM for CRUD operations
- It parses $_POST and map values to corresponding fields
- It allows post-operations before save an update/insert operation
- It doesn't requires any php framework, you can however plug it in your controller.

Notes:
- It assumes you have a read-only PK in your table
- It requires to set a field to search (via setSearchField())
- Although it uses an ORM it doesn't provide any special protection against injections.

jQuery-BootGrid by Rafael Staib
https://github.com/rstaib/jquery-bootgrid

php-activerecord
www.phpactiverecord.org

This is basic example to provide full CRUD (scaffolding like) using ajax desingned for jquery-bootgrid.
