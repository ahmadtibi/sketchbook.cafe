<?php
// Classes
sbc_class('Form');

$Form['register'] = new Form(array(
    'name'          => 'registerForm',
    'action'        => 'http://www.sketchbook.cafe/action/',
    'method'        => 'POST',

));

require 'header.php';
?>
Registration!
<?php
require 'footer.php';
?>