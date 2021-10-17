<?php

declare(strict_types=1);

namespace FluencePrototype\Bean;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use RedBeanPHP\RedException\SQL;

/**
 * Trait Bean
 * @package FluencePrototype\Bean
 */
trait Bean
{

    private null|int $id = null;
    private DateTimeObject $created;
    private null|DateTimeObject $updated;
    private null|DateTimeObject $deleted;

    /**
     * @return int|null
     */
    public function getId(): null|int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(null|int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return DateTimeObject
     */
    public function getCreated(): DateTimeObject
    {
        return $this->created;
    }

    /**
     * @return DateTimeObject|null
     */
    public function getUpdated(): null|DateTimeObject
    {
        return $this->updated;
    }

    /**
     * @return DateTimeObject|null
     */
    public function getDeleted(): null|DateTimeObject
    {
        return $this->deleted;
    }

    /**
     *
     */
    public function setDeleted(): void
    {
        $this->deleted = DateTimeObject::createFromFormat('U', time());
    }

    /**
     * @param OODBBean $bean
     */
    public function setBeanDetails(OODBBean $bean): void
    {
        $this->id = (int)$bean->id;
        $this->created = DateTimeObject::createFromFormat('U', $bean->created);
        $this->updated = $bean->updated ? DateTimeObject::createFromFormat('U', $bean->updated) : null;
        $this->deleted = $bean->deleted ? DateTimeObject::createFromFormat('U', $bean->deleted) : null;
    }

    /**
     * @param string $className
     * @return OODBBean
     */
    protected function findOrDispense(string $className): OODBBean
    {
        if (!$this->id) {
            $bean = R::dispense(typeOrBeanArray: $className);
            $bean->created = time();
            $bean->updated = null;
            $bean->deleted = null;

            return $bean;
        }

        $bean = R::findOne(type: $className, sql: '`id` = ?', bindings: [$this->id]);
        $bean->updated = time();

        return $bean;
    }

    /**
     * @param iBean $bean
     */
    public static function store(iBean &$bean): void
    {
        try {
            if ($id = R::store(bean: $bean->toBean())) {
                if ($bean->getId() === null) {
                    $bean->setId((int)$id);
                }
            }
        } catch (SQL) {
        }
    }

    /**
     * @param iBean $bean
     */
    public static function trash(iBean &$bean): void
    {
        R::trash($bean->toBean());

        $bean = null;
    }

}