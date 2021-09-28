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

    private ?int $id = null;
    private DateTimeObject $created;
    private ?DateTimeObject $updated;
    private ?DateTimeObject $deleted;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
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
    public function getUpdated(): ?DateTimeObject
    {
        return $this->updated;
    }

    /**
     * @return DateTimeObject|null
     */
    public function getDeleted(): ?DateTimeObject
    {
        return $this->deleted;
    }

    /**
     *
     */
    public function setDeleted(): void
    {
        $this->deleted = time();
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
        /** @var OODBBean $bean */
        $bean;

        if (!$this->id) {
            $bean = R::dispense(typeOrBeanArray: $className);
            $bean->created = time();
            $bean->updated = null;
            $bean->deleted = null;
        } else {
            $bean = R::findOne(type: $className, sql: '`id` = ?', bindings: [$this->id]);
            $bean->updated = time();
        }

        return $bean;
    }

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

}