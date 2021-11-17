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
     * @param OODBBean|null $bean
     * @return iBean|null
     */
    public static function fromBean(null|OODBBean $bean): null|static;

}