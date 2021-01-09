<?php

namespace Plugin\CrossBorder1\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LangContent
 *
 * @ORM\Table(name="plg_cross_border1_order_lang")
 * @ORM\Entity(repositoryClass="Plugin\CrossBorder1\Repository\OrderLangRepository")
 */
class OrderLang
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
     * @var int
     * @ORM\Column(name="order_id", type="integer")
     */
    private $order_id;

    /**
     * @var string
     * @ORM\Column(name="language", type="string", length=255)
     */
    private $language;

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

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
