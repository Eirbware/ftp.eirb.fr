<?php

namespace FtpEirb\Models;

/**
 * @extends AbstractModel<User>
 */
class User extends AbstractModel
{
    protected static $tableName = "users";
    protected static $idFields = ["id"];
    protected static $boolFields = ["admin"];
    protected static $intFields = [];
    protected static $dateFields = [];

    /** @var string */
    public $id;
    /** @var string */
    public $first_name;
    /** @var string */
    public $last_name;
    /** @var bool */
    public $admin;
    /** @var string|null */
    public $added_by;
}
