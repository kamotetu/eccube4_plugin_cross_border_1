<?php

namespace Plugin\CrossBorder1\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\CrossBorder1\Entity\OrderLang;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * LangContentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderLangRepository extends AbstractRepository
{
    /**
     * LangContentRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderLang::class);
    }

    /**
     * @param int $id
     *
     * @return null|LangContent
     */
    public function get($id = 1)
    {
        return $this->find($id);
    }
}