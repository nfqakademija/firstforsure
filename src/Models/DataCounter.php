<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.9
 * Time: 16.23
 */

namespace App\Models;


class DataCounter
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var int
     */
    private $countValue;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Counter
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Counter
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     * @return Counter
     */
    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountValue(): int
    {
        return $this->countValue;
    }

    /**
     * @param int $countValue
     * @return Counter
     */
    public function setCountValue(int $countValue): self
    {
        $this->countValue = $countValue;

        return $this;
    }


}