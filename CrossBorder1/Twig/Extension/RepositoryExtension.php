<?php

namespace Plugin\CrossBorder1\Twig\Extension;

use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;
use Plugin\CrossBorder1\Repository\PluginNewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Plugin\CrossBorder1\Repository\PluginCategoryRepository;

class RepositoryExtension extends AbstractExtension
{

    private $eccubeConfig;

    private $request;

    private $entityManager;

    private $pluginNewsRepository;

    private $pluginCategoryRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        RequestStack $request,
        EntityManagerInterface $entityManager,
        PluginNewsRepository $pluginNewsRepository,
        PluginCategoryRepository $pluginCategoryRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->pluginNewsRepository = $pluginNewsRepository;
        $this->pluginCategoryRepository = $pluginCategoryRepository;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('repository', [$this, 'getPluginRepository'], ['pre_escape' => 'html', 'is_safe' => ['html']]),

        ];
    }

    public function getPluginRepository($entity)
    {
        if($this->eccubeConfig->get('locale') !== $this->request->getCurrentRequest()->getLocale()){
            switch($entity){
                case 'Eccube\\Entity\\News':
                    return $this->pluginNewsRepository;
                    break;
                case 'Eccube\\Entity\\Category':
                    return $this->pluginCategoryRepository;
                    break;
                default:
                    return $this->entityManager->getRepository($entity);
            }
        }else{
            return $this->entityManager->getRepository($entity);
        }
    }
}
