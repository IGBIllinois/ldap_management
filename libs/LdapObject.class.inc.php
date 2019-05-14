<?php

abstract class LdapObject
{
    protected $id;
    protected static $idField = "uid";
    protected static $ou = "";
    protected static $fullAttributes = array('*', '+');
    protected $raw_data;
    protected static $lastSearch = array();

//    abstract public static function create($id); TODO create() functions in subclass need to be brought in line

    abstract public function remove();

    // TODO all() and search() should be abstract also, but they haven't been implemented in all subclasses yet

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    public function load_by_id($id) {
        $filter = static::idFilter($id);
        $result = Ldap::getInstance()->search($filter, static::$ou, static::$fullAttributes);
        if ( $result['count'] > 0 ) {
            $this->load_from_result($result[0]);
        }
    }

    protected function load_from_result($result) {
        $this->id = $result[static::$idField][0];
    }

    public static function exists($id) {
        $filter = static::idFilter($id);
        $attributes = array(static::$idField);
        $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
        if ( $result['count'] ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the LDAP filter necessary to get the LDAP object with the given id
     * @param string $id
     * @return string
     */
    protected static function idFilter($id) {
        return sprintf("(%s=%s)", static::$idField, $id);
    }

    public static function lastSearchCount() {
        return count(static::$lastSearch);
    }

    public function getRDN() {
        $filter = static::idFilter($this->id);
        $attributes = array('dn');
        $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
        if ( isset($result[0]['dn']) ) {
            return $result[0]['dn'];
        } else {
            return false;
        }
    }

    public function getLdapAttributes() {
        $this->loadLdapResult();
        if ( $this->raw_data ) {
            return ldap_get_attributes(Ldap::getInstance()->get_resource(), $this->raw_data);
        }
        return false;
    }

    protected function loadLdapResult() { // TODO we should be using this method to load the object itself
        if ( $this->raw_data == null ) {
            $filter = static::idFilter($this->id);
            $result = Ldap::getInstance()->search_result($filter, static::$ou, static::$fullAttributes);
            if ( $result != false ) {
                $this->raw_data = ldap_first_entry(Ldap::getInstance()->get_resource(), $result);
            }
        }
    }

    protected static function sorter($key = null, $asc = true) {
        if ( $key === null ) {
            $key = static::$idField;
        }
        $key = "get" . ucfirst($key);
        if ( $asc ) {
            return function ($a, $b) use ($key) {
                return LdapObject::username_cmp($a->$key(), $b->$key());
            };
        } else {
            return function ($a, $b) use ($key) {
                return LdapObject::username_cmp($b->$key(), $a->$key());
            };
        }
    }

    public static function username_cmp($a, $b) {
        if ( $a == $b ) {
            return 0;
        }
        // Empty strings should show up at the end of the list
        if ( $a == '' ) {
            return 1;
        }
        if ( $b == '' ) {
            return -1;
        }
        $aalpha = strcspn($a, '0123456789');
        $balpha = strcspn($b, '0123456789');
        if ( $aalpha == $balpha && substr($a, 0, $aalpha) == substr($b, 0, $balpha) ) {
            $anum = substr($a, $aalpha);
            $bnum = substr($b, $balpha);
            if ( is_numeric($anum) && is_numeric($bnum) ) {
                return intval($anum) < intval($bnum) ? -1 : (intval($anum) == intval($bnum) ? 0 : 1);
            } else {
                return strcasecmp($a, $b);
            }
        } else {
            return strcasecmp($a, $b);
        }
    }
}