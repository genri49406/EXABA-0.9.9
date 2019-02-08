<?php
$id = (int)$_GET['id'];
echo '<input type="button" value="x" onclick="this.parentNode.innerHTML =\'\'" style="float: right;"></button>';
for ($i = 1; $i <= 43; $i++) {
echo "\r\n".'<img src="img/smiles/'.$i.'.gif" alt="" onclick="BB_code(\'f'.$id.'\',\' [smile]'.$i.'[/smile] \',\'\');">'."\r\n";
}

?>