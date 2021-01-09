<?php

namespace Plugin\CrossBorder1\Service\Event\SaveContentEventSubscriber;

use Plugin\CrossBorder1\Entity\OrderLang;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Plugin\CrossBorder1\Repository\OrderLangRepository;
use Eccube\Repository\ClassNameRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Plugin\CrossBorder1\Entity\LangContent;
use Doctrine\ORM\EntityManagerInterface;


class Functions
{

    private $registry;

    public function __construct(
        RegistryInterface $registry
    )
    {
        $this->registry = $registry;
    }

    public function handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em)
    {
        $LangContent = $this->getLangContent($entity, $entity_id, $entity_field, $locale);

        if(is_null($LangContent) && $content !== ''){
            $this->save($entity, $entity_id, $entity_field, $content, $locale, $em);
        }elseif(!empty($LangContent) && $content === ''){
            $this->delete($LangContent, $em);
        }elseif(!empty($LangContent) && $content !== ''){
            $this->update($LangContent, $content, $em);
        }
    }

    public function save($entity, $entity_id, $entity_field, $content, $locale, $em)
    {
        $LangContent = new LangContent();
        $value_cols = [];
        $base_cols = $LangContent->getMyProperty();
        $value_cols[] = $entity;
        $value_cols[] = $entity_id;
        $value_cols[] = $entity_field;
        $value_cols[] = $content;
        $value_cols[] = $locale;
        $base_and_value_cols = array_combine($base_cols, $value_cols);
        return $em->getConnection()->insert('plg_cross_border1_lang_content', $base_and_value_cols);
    }

    public function update($LangContent, $content, $em)
    {
        $sql = "UPDATE plg_cross_border1_lang_content SET content = ? WHERE id = ?";
        $values = [$content, $LangContent->getId()];
        return $em->getConnection()->executeUpdate($sql, $values);
    }

    public function delete($LangContent, $em)
    {
        $sql = "DELETE FROM plg_cross_border1_lang_content WHERE id = ?";
        $values = [$LangContent->getId()];
        return $em->getConnection()->executeQuery($sql, $values);
    }

    public function getLangContent($entity, $entity_id, $entity_field, $locale)
    {
        //ネストを回避するためここでnew
        $LangContentRepository = new LangContentRepository($this->registry);
        return $LangContentRepository->findOneBy(
            [
                'entity' => $entity,
                'entity_id' => $entity_id,
                'language' => $locale,
                'entity_field' => $entity_field,
            ]
        );
    }

    public function getClassNameLastId()
    {
        $ClassNameRepository = new ClassNameRepository($this->registry);
        $qb = $ClassNameRepository->createQueryBuilder('c')
            ->select('c.id')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery();
        return $qb->getResult()[0]['id'];
    }

    public function deleteClassNameContent(EntityManagerInterface $em, $entity_id)
    {
        $LangContentRepository = new LangContentRepository($this->registry);
        $qb = $LangContentRepository->createQueryBuilder('l')
            ->delete()
            ->where('l.entity_id = :entity_id')
            ->andWhere('l.entity = :entity')
            ->setParameter('entity_id', $entity_id)
            ->setParameter('entity', 'Eccube\Entity\ClassName')
            ->getQuery();
        $result = $qb->getResult();
    }


    public function deleteLangContent(EntityManagerInterface $em, $entity, $entity_id)
    {
        $LangContentRepository = new LangContentRepository($this->registry);
        $LangContents = $LangContentRepository->findBy(
            [
                'entity' => $entity,
                'entity_id' => $entity_id,
            ]
        );
        if(!empty($LangContents)){
            foreach($LangContents as $LangContent){
                $sql = "DELETE FROM plg_cross_border1_lang_content WHERE id = ?";
                $values = [$LangContent->getId()];
                $em->getConnection()->executeQuery($sql, $values);
            }
        }
    }

    public function setOrderLang(EntityManagerInterface $em, $order_id, $locale)
    {
        $OrderLangRepository = new OrderLangRepository($this->registry);
        $OrderLang = $OrderLangRepository->findOneBy(
            [
                'order_id' => $order_id,
            ]
        );
        if(is_null($OrderLang)){
            $OrderLang = new OrderLang();
            $value_cols = [];
            $base_cols = $OrderLang->getMyProperty();
            $value_cols[] = $order_id;
            $value_cols[] = $locale;
            $base_and_value_cols = array_combine($base_cols, $value_cols);
            return $em->getConnection()->insert('plg_cross_border1_order_lang', $base_and_value_cols);
        }else{
            $sql = "UPDATE plg_cross_border1_order_lang SET language = ? WHERE id = ?";
            $values = [$locale, $OrderLang->getId()];
            return $em->getConnection()->executeUpdate($sql, $values);
        }
    }
}
