<?php
require 'header.php';

echo $data['Form']->start();
echo $data['Form']->field['dothis'];
echo $data['Form']->field['submit'];
echo $data['Form']->end();
?>
Registration!
<?php
require 'footer.php';
?>