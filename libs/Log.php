<?php

// Log functions
class Log
{
    const DOMAIN_ADD = 1;
    const DOMAIN_REMOVE = 2;
    const GROUP_ADD = 3;
    const GROUP_ADD_USER = 4;
    const GROUP_ADD_DIR = 5;
    const GROUP_REMOVE_USER = 6;
    const GROUP_REMOVE_DIR = 7;
    const GROUP_SET_DESC = 8;
    const GROUP_SET_NAME = 9;
    const GROUP_SET_OWNER = 10;
    const GROUP_REMOVE = 11;
    const HOST_ADD = 12;
    const HOST_REMOVE = 13;
    const HOST_SET_IP = 14;
    const HOST_SET_NAME = 15;
    const USER_ADD = 16;
    const USER_REMOVE = 17;
    const USER_ADD_HOST = 18;
    const USER_REMOVE_HOST = 19;
    const USER_LOCK = 20;
    const USER_UNLOCK = 21;
    const USER_SET_EXP = 22;
    const USER_REMOVE_EXP = 23;
    const USER_SET_PASS_EXP = 24;
    const USER_REMOVE_PASS_EXP = 25;
    const USER_SET_CLASSROOM = 26;
    const USER_SET_CRASHPLAN = 27;
    const USER_SET_DESC = 28;
    const USER_REMOVE_DESC = 29;
    const USER_SET_EXP_REASON = 30;
    const USER_REMOVE_EXP_REASON = 31;
    const USER_SET_FORWARD = 32;
    const USER_SET_SUBFOLDER = 33;
    const USER_SET_LEFT = 34;
    const USER_SET_LOGIN = 35;
    const USER_SET_NAME = 36;
    const USER_SET_NONCAMPUS = 37;
    const USER_SET_PASS = 38;
    const USER_SET_USERNAME = 39;
    const EXP_EMAIL_SENT = 40;
    const USER_SET_BIOCLUSTER = 41;
    const USER_REMOVE_CRASHPLAN = 42;

    const TYPE_USER = 'user';
    const TYPE_GROUP = 'group';
    const TYPE_HOST = 'host';
    const TYPE_DOMAIN = 'domain';

    // TODO in php7 this can be a const
    static $EVENT_OBJECT_TYPE = [
        self::DOMAIN_ADD => self::TYPE_DOMAIN,
        self::DOMAIN_REMOVE => self::TYPE_DOMAIN,  // DOMAIN_REMOVE
        self::GROUP_ADD => self::TYPE_GROUP,  // GROUP_ADD
        self::GROUP_ADD_USER => self::TYPE_GROUP,  // GROUP_ADD_USER
        self::GROUP_ADD_DIR => self::TYPE_GROUP,  // GROUP_ADD_DIR
        self::GROUP_REMOVE_USER => self::TYPE_GROUP,  // GROUP_REMOVE_USER
        self::GROUP_REMOVE_DIR => self::TYPE_GROUP,  // GROUP_REMOVE_DIR
        self::GROUP_SET_DESC => self::TYPE_GROUP,  // GROUP_SET_DESC
        self::GROUP_SET_NAME => self::TYPE_GROUP,  // GROUP_SET_NAME
        self::GROUP_SET_OWNER => self::TYPE_GROUP,  // GROUP_SET_OWNER
        self::GROUP_REMOVE => self::TYPE_GROUP,  // GROUP_REMOVE
        self::HOST_ADD => self::TYPE_HOST,  // HOST_ADD
        self::HOST_REMOVE => self::TYPE_HOST,  // HOST_REMOVE
        self::HOST_SET_IP => self::TYPE_HOST,  // HOST_SET_IP
        self::HOST_SET_NAME => self::TYPE_HOST,  // HOST_SET_NAME
        self::USER_ADD => self::TYPE_USER,  // USER_ADD
        self::USER_REMOVE => self::TYPE_USER,  // USER_REMOVE
        self::USER_ADD_HOST => self::TYPE_USER,  // USER_ADD_HOST
        self::USER_REMOVE_HOST => self::TYPE_USER,  // USER_REMOVE_HOST
        self::USER_LOCK => self::TYPE_USER,  // USER_LOCK
        self::USER_UNLOCK => self::TYPE_USER,  // USER_UNLOCK
        self::USER_SET_EXP => self::TYPE_USER,  // USER_SET_EXP
        self::USER_REMOVE_EXP => self::TYPE_USER,  // USER_REMOVE_EXP
        self::USER_SET_PASS_EXP => self::TYPE_USER,  // USER_SET_PASS_EXP
        self::USER_REMOVE_PASS_EXP => self::TYPE_USER,  // USER_REMOVE_PASS_EXP
        self::USER_SET_CLASSROOM => self::TYPE_USER,  // USER_SET_CLASSROOM
        self::USER_SET_CRASHPLAN => self::TYPE_USER,  // USER_SET_CRASHPLAN
        self::USER_SET_DESC => self::TYPE_USER,  // USER_SET_DESC
        self::USER_REMOVE_DESC => self::TYPE_USER,  // USER_REMOVE_DESC
        self::USER_SET_EXP_REASON => self::TYPE_USER,  // USER_SET_EXP_REASON
        self::USER_REMOVE_EXP_REASON => self::TYPE_USER,  // USER_REMOVE_EXP_REASON
        self::USER_SET_FORWARD => self::TYPE_USER,  // USER_SET_FORWARD
        self::USER_SET_SUBFOLDER => self::TYPE_USER,  // USER_SET_SUBFOLDER
        self::USER_SET_LEFT => self::TYPE_USER,  // USER_SET_LEFT
        self::USER_SET_LOGIN => self::TYPE_USER,  // USER_SET_LOGIN
        self::USER_SET_NAME => self::TYPE_USER,  // USER_SET_NAME
        self::USER_SET_NONCAMPUS => self::TYPE_USER,  // USER_SET_NONCAMPUS
        self::USER_SET_PASS => self::TYPE_USER,  // USER_SET_PASS
        self::USER_SET_USERNAME => self::TYPE_USER,  // USER_SET_USERNAME
        self::EXP_EMAIL_SENT => self::TYPE_USER,  // EXP_EMAIL_SENT
        self::USER_SET_BIOCLUSTER => self::TYPE_USER,  // USER_SET_BIOCLUSTER
        self::USER_REMOVE_CRASHPLAN => self::TYPE_USER,  // USER_REMOVE_CRASHPLAN
    ];
    static $EVENT_RELATED_TYPE = [
        self::GROUP_ADD_USER => self::TYPE_USER,
        self::GROUP_REMOVE_USER => self::TYPE_USER,
        self::GROUP_SET_OWNER => self::TYPE_USER,
        self::USER_ADD_HOST => self::TYPE_HOST,
        self::USER_REMOVE_HOST => self::TYPE_HOST,
    ];
    static $EVENT_UPDATE_NAME = [
        self::GROUP_SET_NAME => true,
        self::HOST_SET_NAME => true,
        self::USER_SET_USERNAME => true,
    ];

    // Records the given message in the log file. $quiet logs in log file but not to screen
    public static function info(
        $message,
        $eventId = null,
        $object = null,
        $related = null,
        $value = null,
        $time = null,
        $oldId = null
    ) {
        global $login_user;
        if (isset($login_user)) {
            $user_str = $login_user->getUsername();
        } else {
            $user_str = "guest";
        }
        $current_time = date('Y-m-d H:i:s');
        $full_msg = $current_time . " $user_str: " . $message . "\n";
        if (__ENABLE_LOG__) {
            $fh = fopen(self::logFile(), 'a');
            fwrite($fh, $full_msg);
            fclose($fh);
            self::saveToDatabase(
                $login_user,
                $current_time,
                $message,
                $eventId,
                $object,
                $related,
                $value,
                $time,
                $oldId
            );
        }
    }

    /**
     * @param User            $user
     * @param                 $logtime
     * @param string          $message
     * @param int|null        $eventId
     * @param LdapObject|null $object
     * @param LdapObject|null $related
     * @param string|null     $value
     * @param int|null        $time
     * @param string|null     $oldId
     * @return bool|string
     */
    public static function saveToDatabase(
        $user,
        $logtime,
        $message,
        $eventId = null,
        $object = null,
        $related = null,
        $value = null,
        $time = null,
        $oldId = null
    ) {
        if (MySQL::init(__LOG_DB_HOST__, __LOG_DB_NAME__, __LOG_DB_USER__, __LOG_DB_PASS__)) {
            // First get user
            if ($user !== null) {
                $userObj = MySQL::getInstance()->select(
                    "select * from objects where name=:name and type=:type",
                    [':name' => $user->getId(), ':type' => self::TYPE_USER]
                )
                ;
                if (!$userObj) {
                    // Doesn't exist, insert
                    $userId = MySQL::getInstance()->insert(
                        "insert into objects (name, type) values (:name, :type)",
                        [":name" => $user->getId(), ':type' => "user"]
                    )
                    ;
                } else {
                    $userId = $userObj[0]['id'];
                }
            } else {
                $userId = null;
            }

            // Next, get object/related ids
            $objectId = null;
            if ($eventId !== null && isset(self::$EVENT_OBJECT_TYPE[$eventId])) {
                // Check if this is a name update
                if (isset(self::$EVENT_UPDATE_NAME[$eventId])) {
                    // TODO there's a potential issue if you delete an object, then rename another object to its name
                    MySQL::getInstance()->query(
                        "update objects set name=:newname where name=:oldname and type=:type limit 1",
                        [
                            ':newname' => $object->getId(),
                            ':oldname' => $oldId,
                            ':type' => self::$EVENT_OBJECT_TYPE[$eventId],
                        ]
                    )
                    ;
                }

                $objectObj = MySQL::getInstance()->select(
                    "select * from objects where name=:name and type=:type",
                    [
                        ':name' => $object->getId(),
                        ':type' => self::$EVENT_OBJECT_TYPE[$eventId],
                    ]
                )
                ;
                if ($objectObj) {
                    $objectId = $objectObj[0]['id'];
                } else {
                    // Doesn't exist, insert
                    $objectId = MySQL::getInstance()->insert(
                        "insert into objects (name, type) values (:name, :type)",
                        [":name" => $object->getId(), ':type' => self::$EVENT_OBJECT_TYPE[$eventId]]
                    )
                    ;
                }
            }
            $relatedId = null;
            if ($eventId !== null && isset(self::$EVENT_RELATED_TYPE[$eventId])) {
                $relatedObj = MySQL::getInstance()->select(
                    "select * from objects where name=:name and type=:type",
                    [
                        ':name' => $related->getId(),
                        ':type' => self::$EVENT_RELATED_TYPE[$eventId],
                    ]
                )
                ;
                if ($relatedObj) {
                    $relatedId = $relatedObj[0]['id'];
                } else {
                    // Doesn't exist, insert
                    $relatedId = MySQL::getInstance()->insert(
                        "insert into objects (name, type) values (:name, :type)",
                        [":name" => $related->getId(), ':type' => self::$EVENT_RELATED_TYPE[$eventId]]
                    )
                    ;
                }
            }

            return MySQL::getInstance()->insert(
                'insert into logs (logtime,user,object_id,message,related_id,event_id,value,time) values (:logtime,:user,:object_id,:message,:related_id,:event_id,:value,:time)',
                [
                    ':logtime' => $logtime,
                    ':user' => $userId,
                    ':object_id' => $objectId,
                    ':message' => $message,
                    ':related_id' => $relatedId,
                    ':event_id' => $eventId,
                    ':value' => $value,
                    ':time' => $time,
                ]
            )
                ;
        }
        return false;
    }

    // Makes sure the log file exists and return its location
    public static function logFile()
    {
        if (!file_exists(__LOG_FILE__)) {
            touch(__LOG_FILE__);
        }
        return __LOG_FILE__;
    }

    public static function getLogs($id, $type)
    {
        if (MySQL::init(__LOG_DB_HOST__, __LOG_DB_NAME__, __LOG_DB_USER__, __LOG_DB_PASS__)) {
            return MySQL::getInstance()->select(
                "select l.*, u.name as creator from logs l join objects o on o.id=l.object_id left join objects r on r.id=l.related_id left join objects u on u.id=l.user where ((o.name=:name and o.type=:type) or (r.name=:name and r.type=:type)) order by l.logtime",
                [':name' => $id, ':type' => $type]
            )
                ;
        }
        return [];
    }
}


