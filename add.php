<?php
  require_once('include/alive.php');
  $alive = new alive();

  if (isset($_POST['service_add'])) {
    echo $alive->service_add($_POST['service_name'], $_POST['service_port'], $_POST['service_desc']);
  } elseif (isset($_POST['company_add'])) {
    echo $alive->company_add($_POST['company_name'], $_POST['company_contact'], $_POST['company_email'], $_POST['company_desc']);
  } elseif (isset($_POST['server_add'])) {
    echo $alive->server_add($_POST['company_id'], $_POST['server_name'], $_POST['server_desc'], $_POST['server_ip']);
  }

?>
<form action="add.php" method="post">
ADD SERVICE<br />
service_name: <input type="text" name="service_name" /><br />
service_port: <input type="text" name="service_port" /><br />
service_desc: <input type="text" name="service_desc" /><br />
<input type="submit" name="service_add" />
</form>

<select size="10">
<?php
  foreach ($alive->service_list() as $SERVICE) {
    echo '<option value="service_id">('. $SERVICE['service_id'] .') '. $SERVICE['service_port'] .' ['. $SERVICE['service_name'] .'] - '. $SERVICE['service_desc'] .'</option>';
  }
?>
</select>

<hr />

<form action="add.php" method="post">
ADD COMPANY<br />
company_name: <input type="text" name="company_name" /><br />
company_contact: <input type="text" name="company_contact" /><br />
company_email: <input type="text" name="company_email" /><br />
company_desc: <input type="text" name="company_desc" /><br />
<input type="submit" name="company_add" />
</form>
<select size="10">
<?php
  foreach ($alive->company_list() as $COMPANY) {
    echo '<option value="'. $COMPANY['company_id'] .'">('. $COMPANY['company_id'] .') '. $COMPANY['company_name'] .'</option>';
  }
?>
</select>

<hr />

<form action="add.php" method="post">
ADD SERVER<br />
company: <select name="company_id">
<?php
  foreach ($alive->company_list() as $COMPANY) {
    echo '<option value="'. $COMPANY['company_id'] .'">'. $COMPANY['company_name'] .'</option>';
  }
?>
</select><br />
server_name: <input type="text" name="server_name" /><br />
server_desc: <input type="text" name="server_desc" /><br />
server_ip: <input type="text" name="server_ip" /><br />
<input type="submit" name="server_add" />
</form>
