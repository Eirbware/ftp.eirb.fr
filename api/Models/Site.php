<?php

namespace FtpEirb\Models;

/**
 * @extends AbstractModel<Site>
 */
class Site extends AbstractModel
{
    protected static $tableName = "sites";
    protected static $idFields = ["id"];
    protected static $boolFields = [];
    protected static $intFields = [];
    protected static $dateFields = [];

    /** @var string */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $uid;
    /** @var string */
    public $dir;
}
