<?php

namespace Plugin\CrossBorder1;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\CrossBorder1\Entity\Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    public function enable(array $meta, ContainerInterface $container)
    {
        $this->insertFirstData($container);
    }

    /**
     * Configへ初期データ挿入
     * @param ContainerInterface $container
     */
    public function insertFirstData(ContainerInterface $container)
    {
        /**@var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $query = $em->createQueryBuilder()
            ->select('c')
            ->from("Plugin\\CrossBorder1\\Entity\\Config", "c")
            ->setMaxResults(1)
            ->getQuery();
        $Config = $query->getResult();
        if(!empty($Config)){
            return;
        }else{
            for($i = 1;$i <= 2;++$i){
                $Config = new Config();
                $Config->setSortNo(1);
                $Config->setVisible(0);
                if($i === 1){
                    $Config->setName('日本語');
                    $Config->setBackendName('ja');
                }else{
                    $Config->setName('英語');
                    $Config->setBackendName('en');
                }
                $em->persist($Config);
                $em->flush();
            }
        }
    }
}
