<?php
  /* alive.php - 07/01/2015
   * Just a simple system to check the status of online resources (servers, services)


   * TO DO:
   * - make the system check every server every five minutes
   * - make the system email me if there's an outage that lasts two false
   * - make the system so you can manage it properly!
   * - make the system display outages properly
   */
  
// pull in the global variables
require('config.php');

class alive {
  /* functions;
   * company_get(); get information about a company
   * company_set(); set information about a company
   * company_add(); add a company
   * company_del(); delete a company
   *
   * server_get(); get information about a server
   * server_set(); set information about a server
   * server_add(); add a server
   * server_del(); delete a server
   *
   * service_get(); get service information
   * service_set(); set service information
   * service_add(); add a service
   * service_del(); delete a service
   *
   * check_get(); get checker information
   * check_set(); set checker information
   * check_add(); add checker
   * check_del(); delete checker
   */
  
  // vars
  var $LINK;
  var $SOCKERRNO;
  var $SOCKERRSTR;
  
  function alive() {
    global $_SET;
    // when the class is created
    $this->LINK = mysqli_connect($_SET['db_hostname'], $_SET['db_username'], $_SET['db_password'], $_SET['db_database']);
  }
  
  function company_list() {
    $QUERY = $this->LINK->query("SELECT company_id, company_name, company_contact, company_email, company_desc FROM alive_company ORDER BY company_name");
    
    while ($DATA = $QUERY->fetch_array()) {
      $OUT[$DATA['company_id']]['company_id'] = $DATA['company_id'];
      $OUT[$DATA['company_id']]['company_name'] = $DATA['company_name'];
      $OUT[$DATA['company_id']]['company_contact'] = $DATA['company_contact'];
      $OUT[$DATA['company_id']]['company_email'] = $DATA['company_email'];
      $OUT[$DATA['company_id']]['company_desc'] = $DATA['company_desc'];
    }
    
    return $OUT;
  }

  function company_get($COMPANYID) {
    $QUERY = $this->LINK->query("SELECT company_id, company_name, company_contact, company_email, company_desc FROM alive_company WHERE (company_id = '$COMPANYID')");
    
    return $QUERY->fetch_array();
  }
  
  function company_set($COMPANYID, $FIELD, $VALUE) {
    $this->LINK->query("UPDATE alive_company SET $FIELD = '$VALUE' WHERE (company_id = '$COMPANYID')");
    
    return $this->LINK->affected_rows();
  }
  
  function company_add($NAME, $CONTACT, $EMAIL, $DESC) {
    $this->LINK->query("INSERT INTO alive_company (company_name, company_contact, company_email, company_desc) VALUES('$NAME', '$CONTACT', '$EMAIL', '$DESC')");
    
    return $this->LINK->insert_id;
  }
  
  function company_del($COMPANYID) {
    $this->LINK->query("DELETE FROM alive_company WHERE (company_id = '$COMPANYID')");

    return $this->LINK->affected_rows();
  }
  
  
  function server_get($SERVERID) {
    $QUERY = $this->LINK->query("SELECT server_id, company_id, server_name, server_desc, server_ip FROM alive_server WHERE (server_id = '$SERVERID')");
    
    return $QUERY->fetch_array();
  }
  
  function server_set($SERVERID, $FIELD, $VALUE) {
    $this->LINK->query("UPDATE alive_server SET $FIELD = '$VALUE' WHERE (server_id = '$SERVERID')");
    
    return $this->LINK->affected_rows;
  }
  
  function server_add($COMPANYID, $SERVERNAME, $SERVERDESC, $SERVERIP) {
    $this->LINK->query("INSERT INTO alive_server (company_id, server_name, server_desc, server_ip) VALUES('$COMPANYID', '$SERVERNAME', '$SERVERDESC', '$SERVERIP')");
    
    return $this->LINK->insert_id;
  }
  
  function server_del($COMPANYID) {
    $this->LINK->query("DELETE FROM alive_server WHERE (server_id = '$SERVERID')");
    
    return $this->LINK->affected_rows();
  }
  
  
  function service_list() {
    $QUERY = $this->LINK->query("SELECT service_id, service_name, service_port, service_desc FROM alive_service ORDER BY service_port");
    
    while ($DATA = $QUERY->fetch_array()) {
      $OUT[$DATA['service_id']]['service_id'] = $DATA['service_id'];
      $OUT[$DATA['service_id']]['service_name'] = $DATA['service_name'];
      $OUT[$DATA['service_id']]['service_port'] = $DATA['service_port'];
      $OUT[$DATA['service_id']]['service_desc'] = $DATA['service_desc'];
    }
    
    return $OUT;
  }
  
  function service_get($SERVICEID) {
    $QUERY = $this->LINK->query("SELECT service_id, service_name, service_port, service_desc FROM alive_service WHERE (service_id = '$SERVICEID')");
    
    return $QUERY->fetch_array();
  }
  
  function service_set($SERVICEID, $FIELD, $VALUE) {
    $this->LINK->query("UPDATE alive_service SET $FIELD = '$VALUE' WHERE (service_id = '$SERVICEID')");
    
    return $this->LINK->affected_rows;
  }
  
  function service_add($SERVICENAME, $SERVICEPORT, $SERVICEDESC) {
    $this->LINK->query("INSERT INTO alive_server (service_name, service_port, service_desc) VALUES('$SERVICENAME', '$SERVICEPORT', '$SERVICEDESC')");
    
    return $this->LINK->insert_id;
  }
  
  function service_del($SERVICEID) {
    $this->LINK->query("DELETE FROM alive_service WHERE (service_id = '$SERVICEID')");
    
    return $this->LINK->affected_rows();
  }
  
  function check_company($COMPANYID) {
    $QUERY = $this->LINK->query("SELECT server_id, server_name FROM alive_server WHERE (company_id = '$COMPANYID')");
    
    while ($SERVER = $QUERY->fetch_array()) {
      $OUT[$SERVER['server_id']] = $this->check_server($SERVER['server_id']);
    }
    
    return $OUT;
  }
  
  function check_server($SERVERID) {
    $QUERY = $this->LINK->query("SELECT server_name, server_ip, service_port FROM alive_services, alive_service, alive_server WHERE (alive_server.server_id = '$SERVERID') AND (alive_server.server_id = alive_services.server_id) AND (alive_service.service_id = alive_services.service_id)");
    
    foreach ($QUERY as $SERVICE) {
      $OUT[$SERVICE['service_port']]['server_name'] = $SERVICE['server_name'];
      $OUT[$SERVICE['service_port']]['server_ip'] = $SERVICE['server_ip'];
      $OUT[$SERVICE['service_port']]['service_port'] = $SERVICE['service_port'];
      $OUT[$SERVICE['service_port']]['status'] = $this->check_exec($SERVICE['server_ip'], $SERVICE['service_port']);
    }
    
    return $OUT;
  }
  
  function check_service($SERVERID, $SERVICEID) {
    
  }
  
  function check_exec($SERVER, $PORT) {
    $SOCK = @fsockopen($SERVER, $PORT, $ERRNO, $ERRSTR, 10);
    
    if (!$SOCK) {
      $this->SOCKERRNO = $ERRNO;
      $this->SOCKERRSTR = $ERRSTR;
      return 0;
    }
    
    return 1;
  }
}
?>
