<?php

declare(strict_types=1);

namespace FluencePrototype\Bean;

use RedBeanPHP\OODBBean;

interface iBean
{

    /**
     * @return OODBBean
     */
    public function toBean(): OODBBean;

    /**
     * @param OODBBean $bean
     * @return iBean
     */
    public static function fromBean(OODBBean $bean): iBean;

}