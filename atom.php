<?php
echo 'this is a test for PHP atom';
while ($trow = mysqli_fetch_assoc($result))
{
    echo 'testmanhero';
}
mysqli_data_seek($result,0);
