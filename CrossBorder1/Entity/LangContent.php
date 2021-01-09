<?php

namespace Plugin\CrossBorder1\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LangContent
 *
 * @ORM\Table(name="plg_cross_border1_lang_content")
 * @ORM\Entity(repositoryClass="Plugin\CrossBorder1\Repository\LangContentRepository")
 */
class LangContent
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     * @ORM\Column(name="entity", type="string", length=255)
     */
    private $entity;

    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @var int
     * @ORM\Column(name="entity_id", type="integer")
     */
    private $entity_id;

    public function setEntityId($entity_id)
    {
        $this->entity_id = $entity_id;
        return $this;
    }

    public function getEntityId()
    {
        return $this->entity_id;
    }

    /**
     * @var string
     * @ORM\Column(name="entity_field", type="string", length=255, nullable=true)
     */
    private $entity_field;

    public function setEntityField($entity_field)
    {
        $this->entity_field = $entity_field;
        return $this;
    }

    public function getEntityField()
    {
        return $this->entity_field;
    }

    /**
     * @var string
     * @ORM\Column(name="content", type="string", length=4000)
     */
    private $content;

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @var string
     * @ORM\Column(name="language", type="string", length=255)
     */
    private $language;

    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getMyProperty()
    {
        $properties = [];
        foreach($this as $key => $value){
            if($key === 'id'){
                continue;
            }
            $properties[] = $key;
        }
        return $properties;
    }
}
