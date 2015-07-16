<?php
/**
 *
 *
 * @copyright Copyright (c) Beijing Jinritemai Technology Co.,Ltd.
 */

namespace Yafrk\Db\Adapter\Driver\Pdo;

use Yafrk\Db\Adapter\Driver\ResultInterface;
use Yafrk\Db\Adapter\Exception;

class Result implements ResultInterface
{

    const STATEMENT_MODE_SCROLLABLE = 'scrollable';
    const STATEMENT_MODE_FORWARD = 'forward';

    /**
     * @var string
     */
    protected $statementMode = self::STATEMENT_MODE_FORWARD;

    /**
     * @var \PDOStatement
     */
    protected $resource = null;

    /**
     * @var array Result options
     */
    protected $options;

    /**
     * Is the current complete?
     *
     * @var bool
     */
    protected $currentComplete = false;

    /**
     * Track current item in recordset
     *
     * @var mixed
     */
    protected $currentData = null;

    /**
     * Current position of scrollable statement
     *
     * @var int
     */
    protected $position = -1;

    /**
     * @var mixed
     */
    protected $generatedValue = null;

    /**
     * @var null
     */
    protected $rowCount = null;

    /**
     * Initialize
     *
     * @param  \PDOStatement $resource
     * @param               $generatedValue
     * @param  int $rowCount
     * @return Result
     */
    public function initialize(\PDOStatement $resource, $generatedValue, $rowCount = null)
    {
        $this->resource = $resource;
        $this->generatedValue = $generatedValue;
        $this->rowCount = $rowCount;
        return $this;
    }

    /**
     * @return null
     */
    public function buffer()
    {
        return null;
    }

    /**
     * @return bool|null
     */
    public function isBuffered()
    {
        return false;
    }

    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get the data
     *
     * @return array
     */
    public function current()
    {
        if ($this->currentComplete) {
            return $this->currentData;
        }

        $this->currentData = $this->resource->fetch(\PDO::FETCH_ASSOC);
        return $this->currentData;
    }

    /**
     * Next
     *
     * @return mixed
     */
    public function next()
    {
        $this->currentData = $this->resource->fetch(\PDO::FETCH_ASSOC);
        $this->currentComplete = true;
        $this->position++;
        return $this->currentData;
    }

    /**
     * Key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @throws Exception\RuntimeException
     * @return void
     */
    public function rewind()
    {
        if ($this->statementMode == self::STATEMENT_MODE_FORWARD && $this->position > 0) {
            throw new Exception\RuntimeException(
                'This result is a forward only result set, calling rewind() after moving forward is not supported');
        }
        $this->currentData = $this->resource->fetch(\PDO::FETCH_ASSOC);
        $this->currentComplete = true;
        $this->position = 0;
    }

    /**
     * Valid
     *
     * @return boolean
     */
    public function valid()
    {
        return ($this->currentData !== false);
    }

    /**
     * Count
     *
     * @return integer
     */
    public function count()
    {
        if (!is_int($this->rowCount)) {
            $this->rowCount = (int)$this->resource->rowCount();
        }
        return $this->rowCount;
    }

    /**
     * @return int
     */
    public function getFieldCount()
    {
        return $this->resource->columnCount();
    }

    /**
     * Is query result
     *
     * @return boolean
     */
    public function isQueryResult()
    {
        return ($this->resource->columnCount() > 0);
    }

    /**
     * Get affected rows
     *
     * @return integer
     */
    public function getAffectedRows()
    {
        return $this->resource->rowCount();
    }

    /**
     * @return mixed|null
     */
    public function getGeneratedValue()
    {
        return $this->generatedValue;
    }

    /**
     * Get all array data
     *
     * @return array
     */
    public function getFetchArrays()
    {
        return $this->resource->fetchAll(\PDO::FETCH_ASSOC);
    }
}