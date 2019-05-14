<?php


class LdapStatus
{
    /** @var boolean status */
    public $status;
    /** @var string message */
    public $message;
    /** @var LdapObject|null object */
    public $object;

    /**
     * LdapStatus constructor.
     * @param boolean         $status
     * @param string          $message
     * @param LdapObject|null $object
     */
    public function __construct($status, $message, $object = null) {
        $this->status = $status;
        $this->message = $message;
        $this->object = $object;
    }


    /**
     * @return boolean
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @return LdapObject|null
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * @param LdapObject|null $object
     */
    public function setObject($object) {
        $this->object = $object;
    }


}