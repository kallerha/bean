<?php

declare(strict_types=1);

namespace FluencePrototype\Bean;

use Exception;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use RedBeanPHP\RedException\SQL;
use ReflectionClass;
use ReflectionException;

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
    private function findOrDispenseHelper(string $className): OODBBean
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
     * @param $className
     * @return OODBBean|null
     */
    protected function findOrDispense($className): OODBBean|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        return $this->findOrDispenseHelper(className: $beanName);
    }

    /**
     * @param string $className
     * @param string $sql
     * @param array $bindings
     * @return OODBBean|null
     */
    public static function getOneAndConvertToBean(string $className, string $sql, array $bindings = []): OODBBean|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        if (!$row = Bean::getOne(sql: $sql, bindings: $bindings)) {
            return null;
        }

        return R::convertToBean(type: $beanName, row: $row);
    }

    /**
     * @param string $className
     * @param string $sql
     * @param array $bindings
     * @return array|null
     */
    public static function getAllAndConvertToBeans(string $className, string $sql, array $bindings = []): array|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        if (!$rows = Bean::getAll(sql: $sql, bindings: $bindings)) {
            return null;
        }

        return R::convertToBeans(type: $beanName, rows: $rows);
    }

    /**
     * @param string $className
     * @return string|null
     */
    public static function getBeanName(string $className): string|null
    {
        try {
            $reflectionClass = new ReflectionClass(objectOrClass: $className);

            if (!$reflectionClass->implementsInterface(interface: iBean::class)) {
                throw new Exception();
            }

            return $reflectionClass->getConstant(name: 'BEAN');
        } catch (ReflectionException | Exception) {
            return null;
        }
    }

    /**
     * @param string $className
     * @return int|null
     */
    public static function count(string $className): int|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        return R::count(type: $beanName);
    }

    /**
     * @param string $className
     * @param string $sql
     * @param array $bindings
     * @return OODBBean|null
     */
    public static function findOne(string $className, string $sql, array $bindings = []): OODBBean|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        return R::findOne(type: $beanName, sql: $sql, bindings: $bindings);
    }

    /**
     * @param string $className
     * @param string $sql
     * @param array $bindings
     * @return OODBBean|null
     */
    public static function findAll(string $className, string $sql, array $bindings = []): array|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        return R::findAll(type: $beanName, sql: $sql, bindings: $bindings);
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return array|null
     */
    private static function getOne(string $sql, array $bindings = []): array|null
    {
        return R::getRow($sql, $bindings);
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return array|null
     */
    private static function getAll(string $sql, array $bindings = []): array|null
    {
        return R::getAll($sql, $bindings);
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