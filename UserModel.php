<?php

namespace stdin\core;

use stdin\core\db\DbModel;

abstract class UserModel extends DbModel
{
    abstract public function getDisplayName():string;


}