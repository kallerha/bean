<?php

declare(strict_types=1);

namespace FluencePrototype\Bean;

use DateTime;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

/**
 * Trait Bean
 * @package FluencePrototype\Bean
 */
trait Bean
{

    private ?int $id = null;
    private DateTime $created;
    private ?DateTime $updated;
    private ?DateTime $deleted;

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
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdated(): ?DateTime
    {
        return $this->updated;
    }

    /**
     * @return DateTime|null
     */
    public function getDeleted(): ?DateTime
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
        $this->created = DateTime::createFromFormat('U', $bean->created);
        $this->updated = $bean->updated ? DateTime::createFromFormat('U', $bean->updated) : null;
        $this->deleted = $bean->deleted ? DateTime::createFromFormat('U', $bean->deleted) : null;
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
        if ($id = R::store(bean: $bean)) {
            $bean->setId($id);
        }
    }

}