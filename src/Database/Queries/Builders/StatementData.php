<?php

namespace Curfle\Database\Queries\Builders;

class StatementData
{

    /**
     * @var string
     */
    protected string $query;

    /**
     * @var array
     */
    protected array $params;

    public function __construct(string $query, array $params)
    {
        $this->query = $query;
        $this->params = $params;
    }

    /**
     * @param array $params
     * @return StatementData
     */
    public function setParams(array $params): static
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param string $query
     * @return StatementData
     */
    public function setQuery(string $query): static
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }
}