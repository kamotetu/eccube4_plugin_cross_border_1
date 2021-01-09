<?php

namespace Plugin\CrossBorder1\Service\Controller\PluginCsvImportController;

use Plugin\CrossBorder1\Repository\LangContentRepository;
use Plugin\CrossBorder1\Entity\LangContent;
use Doctrine\ORM\EntityManagerInterface;

class Functions
{
    private $langContentRepository;

    public function __construct(
        LangContentRepository $langContentRepository
    )
    {
        $this->langContentRepository = $langContentRepository;
    }

    public function saveProductLangContent(EntityManagerInterface $em, $row, $key, $entity_id, $locale)
    {
        if(
            isset($row[$key['name_lang']]) &&
            $row[$key['name_lang']] !== ''
        ){
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Product',
                    'entity_id' => $entity_id,
                    'entity_field' => 'name',
                    'language' => $locale,
                ]
            );
            if(is_null($LangContent)){
                $LangContent = new LangContent();
                $LangContent->setEntity('Eccube\\Entity\\Product');
                $LangContent->setEntityId($entity_id);
                $LangContent->setLanguage($locale);
                $LangContent->setEntityField('name');
                $LangContent->setContent($row[$key['name_lang']]);
            }else{
                $LangContent->setContent($row[$key['name_lang']]);
            }
            $em->persist($LangContent);
        }
        if(
            isset($row[$key['description_detail_lang']]) &&
            $row[$key['description_detail_lang']] !== ''
        ){
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Product',
                    'entity_id' => $entity_id,
                    'entity_field' => 'description_detail',
                    'language' => $locale,
                ]
            );
            if(is_null($LangContent)){
                $LangContent = new LangContent();
                $LangContent->setEntity('Eccube\\Entity\\Product');
                $LangContent->setEntityId($entity_id);
                $LangContent->setLanguage($locale);
                $LangContent->setEntityField('description_detail');
                $LangContent->setContent($row[$key['description_detail_lang']]);
            }else{
                $LangContent->setContent($row[$key['description_detail_lang']]);
            }
            $em->persist($LangContent);
        }
        if(
            isset($row[$key['description_list_lang']]) &&
            $row[$key['description_list_lang']] !== ''
        ){
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Product',
                    'entity_id' => $entity_id,
                    'entity_field' => 'description_list',
                    'language' => $locale,
                ]
            );
            if(is_null($LangContent)){
                $LangContent = new LangContent();
                $LangContent->setEntity('Eccube\\Entity\\Product');
                $LangContent->setEntityId($entity_id);
                $LangContent->setLanguage($locale);
                $LangContent->setEntityField('description_list');
                $LangContent->setContent($row[$key['description_list_lang']]);
            }else{
                $LangContent->setContent($row[$key['description_list_lang']]);
            }
            $em->persist($LangContent);
        }
        if(
            isset($row[$key['free_area_lang']]) &&
            $row[$key['free_area_lang']] !== ''
        ){
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Product',
                    'entity_id' => $entity_id,
                    'entity_field' => 'free_area',
                    'language' => $locale,
                ]
            );
            if(is_null($LangContent)){
                $LangContent = new LangContent();
                $LangContent->setEntity('Eccube\\Entity\\Product');
                $LangContent->setEntityId($entity_id);
                $LangContent->setLanguage($locale);
                $LangContent->setEntityField('free_area');
                $LangContent->setContent($row[$key['free_area_lang']]);
            }else{
                $LangContent->setContent($row[$key['free_area_lang']]);
            }
            $em->persist($LangContent);
        }
    }

    public function getProductLangCsvHeader($headers)
    {
        $headers['言語別商品名'] =
            [
                'id' => 'name_lang',
                'description' => 'admin.product.product_csv.product_name_description_lang',
                'required' => false,
            ];
        $headers['言語別商品説明(詳細)'] =
            [
                'id' => 'description_detail_lang',
                'description' => 'admin.product.product_csv.description_detail_description_lang',
                'required' => false,
            ];
        $headers['言語別商品説明(一覧)'] =
            [
                'id' => 'description_list_lang',
                'description' => 'admin.product.product_csv.description_list_description_lang',
                'required' => false,
            ];
        $headers['言語別フリーエリア'] =
            [
                'id' => 'free_area_lang',
                'description' => 'admin.product.product_csv.free_area_description_lang',
                'required' => false,
            ];
        return $headers;
    }

    public function saveCategoryLangContent(EntityManagerInterface $em, $row, $key, $entity_id, $locale)
    {
        if(
            isset($row[$key['category_name_lang']]) &&
            $row[$key['category_name_lang']] !== ''
        ){
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Category',
                    'entity_id' => $entity_id,
                    'entity_field' => 'category_name',
                    'language' => $locale,
                ]
            );
            if(is_null($LangContent)){
                $LangContent = new LangContent();
                $LangContent->setEntity('Eccube\\Entity\\Category');
                $LangContent->setEntityId($entity_id);
                $LangContent->setLanguage($locale);
                $LangContent->setEntityField('category_name');
                $LangContent->setContent($row[$key['category_name_lang']]);
            }else{
                $LangContent->setContent($row[$key['category_name_lang']]);
            }
            $em->persist($LangContent);
            $em->flush();
        }
    }

    public function getCategoryLangCsvHeader($headers)
    {
        $headers['言語別カテゴリ名'] =
            [
                'id' => 'category_name_lang',
                'description' => 'admin.product.category_csv.category_name_description_lang',
                'required' => false,
            ];
        return $headers;
    }
}
