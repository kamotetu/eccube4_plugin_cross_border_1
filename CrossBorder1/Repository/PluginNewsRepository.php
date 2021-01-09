<?php

namespace Plugin\CrossBorder1\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Eccube\Entity\News;
use Eccube\Repository\NewsRepository;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * NewsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PluginNewsRepository
{
    private $newsRepository;

    private $langContentRepository;

    private $request;

    public function __construct(
        NewsRepository $newsRepository,
        LangContentRepository $langContentRepository,
        RequestStack $request
    )
    {
        $this->newsRepository = $newsRepository;
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
    }

    /**
     * @return News[]|ArrayCollection
     */
    public function getList()
    {
        // second level cacheを効かせるためfindByで取得
        $Results = $this->newsRepository->findBy(['visible' => true], ['publish_date' => 'DESC', 'id' => 'DESC']);

        // 公開日時前のNewsをフィルター
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->lte('publish_date', new \DateTime()));

        $News = new ArrayCollection($Results);
        $new_news = [];
        foreach($News as $news){
            $LangContents = $this->langContentRepository->findBy(
                [
                    'entity' => 'Eccube\\Entity\\News',
                    'entity_id' => $news->getId(),
                    'language' => $this->request->getCurrentRequest()->getLocale(),
                ]
            );

            if(!empty($LangContents)){
                foreach($LangContents as $LangContent){
                    switch($LangContent->getEntityField()){
                        case 'title':
                            $news->setTitle($LangContent->getContent());
                            break;
                        case 'description':
                            $news->setDescription($LangContent->getContent());
                            break;
                    }
                }
            }
            $new_news[] = $news;
        }
        $News = new ArrayCollection($new_news);

        return $News->matching($criteria);
    }
}
