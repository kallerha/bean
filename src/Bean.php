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
    public static function getOne(string $className, string $sql, array $bindings = []): object|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        if (!$row = R::getRow(sql: $sql, bindings: $bindings)) {
            return null;
        }

        if (!$bean = R::convertToBean(type: $beanName, row: $row)) {
            return null;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return $className::fromBean(bean: $bean);
    }

    /**
     * @param string $className
     * @param string $sql
     * @param array $bindings
     * @return array<object>
     */
    public static function getAll(string $className, string $sql, array $bindings = []): array
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return [];
        }

        if (!$rows = R::getAll(sql: $sql, bindings: $bindings)) {
            return [];
        }

        if (!$beans = R::convertToBeans(type: $beanName, rows: $rows)) {
            return [];
        }

        $items = [];

        foreach ($beans as $bean) {
            /** @noinspection PhpUndefinedMethodInspection */
            $items[] = $className::fromBean($bean);
        }

        return $items;
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
     * @param string $sql
     * @param array $bindings
     * @return int
     */
    public static function count(string $className, string $sql = '', array $bindings = []): int
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return 0;
        }

        return R::count(type: $beanName, addSQL: $sql, bindings: $bindings);
    }

    /**
     * @param string $className
     * @param string $sql
     * @param array $bindings
     * @return OODBBean|null
     */
    public static function findOne(string $className, string $sql = null, array $bindings = []): object|null
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return null;
        }

        if (!$bean = R::findOne(type: $beanName, sql: $sql, bindings: $bindings)) {
            return null;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return $className::fromBean($bean);
    }

    /**
     * @param string $className
     * @param string $sql
     * @param array $bindings
     * @return array<object>
     */
    public static function findAll(string $className, string $sql = null, array $bindings = []): array
    {
        if (!$beanName = Bean::getBeanName(className: $className)) {
            return [];
        }

        if (!$beans = R::findAll(type: $beanName, sql: $sql, bindings: $bindings)) {
            return [];
        }

        $items = [];

        foreach ($beans as $bean) {
            /** @noinspection PhpUndefinedMethodInspection */
            $items[] = $className::fromBean($bean);
        }

        return $items;
    }

    /**
     * @param iBean $bean
     */
    public static function store(iBean &$bean): void
    {
        R::begin();

        try {
            $id = R::store(bean: $bean->toBean());

            if ($bean->getId() === null) {
                $bean->setId((int)$id);
            }

            R::commit();
        } catch (Exception | SQL) {
            R::rollback();
        }
    }

    /**
     * @param iBean $bean
     */
    public static function trash(iBean &$bean): void
    {
        $toBean = R::findOne($bean::BEAN, '`id` = ?', [$bean->getId()]);

        R::trash($toBean);

        $bean = null;
    }

}