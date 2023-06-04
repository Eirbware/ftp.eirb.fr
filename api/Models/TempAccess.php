<?php

namespace FtpEirb\Models;

/**
 * @extends AbstractModel<TempAccess>
 */
class TempAccess extends AbstractModel
{
    protected static $tableName = "temp_access";
    protected static $idFields = ["access_id"];
    protected static $boolFields = [];
    protected static $intFields = [];
    protected static $dateFields = ["created_at", "expires_at"];

    /** @var int */
    public $access_id;
    /** @var string */
    public $username;
    /** @var string */
    public $password;
    /** @var string */
    public $created_at;
    /** @var string */
    public $expires_at;
}
