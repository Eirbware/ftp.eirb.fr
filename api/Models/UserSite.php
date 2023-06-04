<?php

namespace FtpEirb\Models;

/**
 * @extends AbstractModel<UserSite>
 */
class UserSite extends AbstractModel
{
    protected static $tableName = "users_sites";
    protected static $idFields = ["id"];
    protected static $boolFields = [];
    protected static $intFields = [];
    protected static $dateFields = ["authorized_at"];

    /** @var int */
    public $id;
    /** @var string */
    public $user_id;
    /** @var string */
    public $site_id;
    /** @var string */
    public $authorized_by;
    /** @var string */
    public $authorized_at;
}
