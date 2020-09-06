<?php
echo <<<_END
Welcome to Decryptoid!
<br><br>
<form action="authenticate.php" method="post" enctype='multipart/form-data'>
<input type="submit" value="Log In">
</form>
<form action="createUser.php" method="post" enctype='multipart/form-data'>
<input type="submit" value="Create User">
</form>
_END;
