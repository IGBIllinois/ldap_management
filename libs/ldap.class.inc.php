<?php
class ldap {

	///////////////Private Variables//////////
	private $ldap_resource = false;
	private $ldap_host;
	private $ldap_base_dn;
	private $ldap_bind_user = false;
	private $ldap_bind_pass;
	private $ldap_ssl = false;
	private $ldap_tls = false;
	private $ldap_port = 389;
	private $ldap_protocol = 3;
	////////////////Public Functions///////////

	public function __construct($host, $ssl, $port, $base_dn, $tls=false) {
		$this->set_host($host);
		$this->set_ssl($ssl);
		$this->set_port($port);
		$this->set_base_dn($base_dn);
		$this->set_tls($tls);
		$this->connect();
        $this->set_protocol(3);
	}


	public function __destruct() {}


	//get ldap functions
	public function get_host() { return $this->ldap_host; }


	public function get_base_dn() { return $this->ldap_base_dn; }


	public function get_bind_user() { return $this->ldap_bind_user; }


	public function get_bind_pass() { return $this->ldap_bind_pass; }


	public function get_ssl() { return $this->ldap_ssl; }


	public function get_port() { return $this->ldap_port; }


	public function get_protocol() { return $this->ldap_protocol; }


	public function get_resource() { return $this->ldap_resource; }


	public function get_connection() {
		return is_resource($this->ldap_resource);
	}


	//set ldap functions
	public function set_protocol($ldap_protocol) {
		$this->ldap_protocol = $ldap_protocol;
		ldap_set_option($this->get_resource(), LDAP_OPT_PROTOCOL_VERSION, $ldap_protocol);
	}


	public function set_bind_user($bind_user) {$this->ldap_bind_user = $bind_user;}


	public function set_bind_pass($bind_pass) {$this->ldap_bind_pass = $bind_pass;}


	//bind()
	//binds to the ldap server as specified user.  If no username/password is provide, binds anonymously
	//$rdn - full rdn of user
	//$password - password
	//returns true if successful, false otherwise.
	public function bind($rdn = "", $password = "") {
		$result = false;
		if ($this->get_connection()) {
			if (($rdn != "") && ($password != "")) {
				$result = @ldap_bind($this->get_resource(), $rdn, $password);

			}
			elseif (($rdn == "") && ($password == "")) {
				$result = @ldap_bind($this->get_resource());
			}
		}
		return $result;

	}


	public function search($filter, $ou = "", $attributes = "") {
		$result = false;
		$ldap_result = $this->search_result($filter, $ou, $attributes);
		if($ldap_result){
			$result = ldap_get_entries($this->get_resource(), $ldap_result);
		}
		return $result;
	}
	
	public function search_result($filter, $ou="", $attributes = ""){
		$result = false;
		if ($ou == "") {
			$ou = $this->get_base_dn();
		}
		if (($this->get_connection()) && ($attributes != "")) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = ldap_search($this->get_resource(), $ou, $filter, $attributes);
			}
		} elseif (($this->get_connection()) && ($attributes == "")) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = ldap_search($this->get_resource(), $ou, $filter);
			}
		}
		return $result;
	}


	public function replace($rdn, $entries) {
		if (ldap_mod_replace($this->get_resource(), $rdn, $entries)) {
			return true;
		}
		else {
			return false;
		}
	}


	public function add($dn, $data) {
		if ($this->get_connection() && $this->get_bind_user()) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = ldap_add($this->get_resource(), $dn, $data);
				return $result;
			}
		}
		return false;
	}


	public function mod_add($dn, $data) {
		if ($this->get_connection() && $this->get_bind_user()) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = @ldap_mod_add($this->get_resource(), $dn, $data);
				return $result;
			}
		}
		return false;
	}


	public function mod_del($dn, $data) {
		if ($this->get_connection() && $this->get_bind_user()) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = ldap_mod_del($this->get_resource(), $dn, $data);
				return $result;
			}
		}
	}


	public function modify($dn, $data) {
		if ($this->get_connection() && $this->get_bind_user()) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = @ldap_modify($this->get_resource(), $dn, $data);
				return $result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function mod_rename($dn, $newrdn) {
		if ($this->get_connection() && $this->get_bind_user()) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = ldap_rename($this->get_resource(), $dn, $newrdn, NULL, true);
				return $result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function remove($dn) {
		if ($this->get_connection() && $this->get_bind_user()) {
			if ($this->bind($this->get_bind_user(), $this->get_bind_pass())) {
				$result = ldap_delete($this->get_resource(), $dn);
				return $result;
			}
		}
	}


	public function get_error() {
		if ($this->get_connection()) {
			return ldap_error($this->get_resource());
		} else {
			return false;
		}
	}


	//////////////////Private Functions/////////////////////

	private function set_host($ldap_host) { $this->ldap_host = $ldap_host; }


	private function set_base_dn($ldap_base_dn) { $this->ldap_base_dn = $ldap_base_dn; }


	private function set_ssl($ldap_ssl) { $this->ldap_ssl = $ldap_ssl; }


	private function set_port($ldap_port) { $this->ldap_port = $ldap_port; }

	private function set_tls($tls) { $this->ldap_tls = $tls; }


	private function connect() {

		$prefix = "";
		if ($this->get_ssl() == true) {
			$prefix = "ldaps://";
		}
		elseif ($this->get_ssl() == false) {
			$prefix = "ldap://";
		}

		$this->ldap_resource = ldap_connect($prefix . $this->get_host().":".$this->get_port());
		$result = false;
		if ($this->get_connection()) {
			$result = true;
		}
		if($this->ldap_tls){
			$result = ldap_start_tls($this->ldap_resource);
		}
		return $result;
	}


}


?>
