<?php
/*
 * 全ての他言語登録を一括してここで処理する(試験中)
 */
namespace Plugin\CrossBorder1\Event;

use Doctrine\ORM\Events;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\DeliveryTime;
use Eccube\Entity\OrderPdf;
use Eccube\Entity\PageLayout;
use Eccube\Entity\PaymentOption;
use Eccube\Entity\ProductCategory;
use Plugin\CrossBorder1\Entity\Config;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Common\EventSubscriber;
use Plugin\CrossBorder1\Service\Event\SaveContentEventSubscriber\Functions;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Eccube\Common\EccubeConfig;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class SaveContentEventSubscriber implements EventSubscriber
{
    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var Functions
     */
    private $functions;

    private $eccubeConfig;


    public function __construct(
        RequestStack $request,
        Functions $functions,
        EccubeConfig $eccubeConfig
    )
    {
        $this->request = $request;
        $this->functions = $functions;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postFlush,
            Events::preRemove,
        ];
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws \Doctrine\DBAL\DBALException
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $request = $this->request->getCurrentRequest();
        $locale = $request->getLocale();
        if($this->eccubeConfig->get('locale') === $locale){
            return;
        }
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $id_entity_map = $uow->getIdentityMap();
        $all_request = $request->request->all();
        foreach($all_request as $request_name => $request_value){
            switch(true){
                case //商品登録・編集
                    $request_name === 'admin_product' &&
                    (isset($request_value['product_content1']) ||
                    isset($request_value['product_content2']) ||
                    isset($request_value['product_content3'])) &&
                    array_key_exists('Eccube\\Entity\\Product', $id_entity_map):
                    $entity = 'Eccube\\Entity\\Product';
                    $entity_id = max(array_keys($id_entity_map['Eccube\\Entity\\Product']));
                    for($i = 1;4 >= $i;++$i){
                        $content = $request_value['product_content' . $i];
                        switch($i){
                            case 1:
                                $entity_field = 'name';
                                break;
                            case 2:
                                $entity_field = 'description_detail';
                                break;
                            case 3:
                                $entity_field = 'description_list';
                                break;
                            case 4:
                                $entity_field = 'free_area';
                                break;
                            default:
                                $entity_field = '';
                        }
                        $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    }
                    break;
                case //規格登録・編集
                    preg_match('/admin_class_name|class_name_\d/u', $request_name) &&
                    array_key_exists('Eccube\\Entity\\ClassName', $id_entity_map) &&
                    isset($request_value['class_name_content']):
                    if($request_name === 'admin_class_name'){
                        $entity_id = max(array_keys($id_entity_map['Eccube\\Entity\\ClassName']));
                    }else{
                        $entity_id = preg_replace('/class_name_(\d)/u', '$1', $request_name);
                    }
                    $entity = 'Eccube\\Entity\\ClassName';
                    $content = $request_value['class_name_content'];
                    $entity_field = 'name';
                    $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    break;
                case //カテゴリ登録・編集
                    preg_match('/category_\d+|admin_category/u', $request_name) &&
                    array_key_exists('Eccube\\Entity\\Category', $id_entity_map) &&
                    isset($request_value['category_content']):
                    $entity = 'Eccube\\Entity\\Category';
                    $content = $request_value['category_content'];
                    if($request_name === 'admin_category'){
                        $entity_id = max(array_keys($id_entity_map['Eccube\\Entity\\Category']));
                    }else{
                        $entity_id = preg_replace('/category_(\d)/u', '$1', $request_name);
                    }
                    $entity_field = 'category_name';
                    $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    break;

                case //タグ登録・編集
                    preg_match('/admin_product_tag|tag_\d/u', $request_name) &&
                    isset($request_value['product_tag_content']) &&
                    array_key_exists('Eccube\\Entity\\Tag', $id_entity_map):
                    $entity = 'Eccube\\Entity\\Tag';
                    if($request_name === 'admin_product_tag'){
                        $entity_id = max(array_keys($id_entity_map['Eccube\\Entity\\Tag']));
                    }else{
                        $entity_id = preg_replace('/tag_(\d)/', '$1', $request_name);
                    }
                    $content = $request_value['product_tag_content'];
                    $entity_field = 'name';
                    $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    break;
                case //マスタデータ登録・編集
                    $request_name === 'admin_system_masterdata_edit' &&
                    !empty($request_value['masterdata_name']):
                    $entity = str_replace('-', '\\', $request_value['masterdata_name']);
                    foreach($request_value['data'] as $value){
                        $entity_id = $value['id'];
                        $content = $value['masterdata_content'];
                        $entity_field = 'name';
                        $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    }
                    break;
                case //新着情報管理登録・編集
                    $request_name === 'admin_news' &&
                    isset($request_value['news_content1']) &&
                    isset($request_value['news_content2']) &&
                    array_key_exists('Eccube\\Entity\\News', $id_entity_map):
                    $entity = 'Eccube\\Entity\\News';
                    $entity_id = max(array_keys($id_entity_map[$entity]));
                    for($i = 1;2>=$i;++$i){
                        switch($i){
                            case 1:
                                $content = $request_value['news_content1'];
                                $entity_field = 'title';
                                break;
                            case 2:
                                $content = $request_value['news_content2'];
                                $entity_field = 'description';
                                break;
                        }
                        $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    }
                    break;
                case //規格分類管理
                    preg_match('/admin_class_category|class_category_\d/u', $request_name) &&
                    isset($request_value['class_category_content']) &&
                    array_key_exists('Eccube\Entity\ClassCategory', $id_entity_map):
                    $entity = 'Eccube\\Entity\\ClassCategory';
                    if($request_name === 'admin_class_category'){
                        $entity_id = max(array_keys($id_entity_map[$entity]));
                    }else{
                        $entity_id = preg_replace('/class_category_(\d)/u', '$1', $request_name);
                    }
                    $entity_field = 'name';
                    $content = $request_value['class_category_content'];
                    $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    break;
                case //基本設定
                    $request_name === 'shop_master' &&
                    isset($request_value['shop_master_content1']) &&
                    isset($request_value['shop_master_content2']) &&
                    isset($request_value['shop_master_content3']) &&
                    isset($request_value['shop_master_content4']) &&
                    array_key_exists('Eccube\\Entity\\BaseInfo', $id_entity_map):
                    $entity = "Eccube\\Entity\\BaseInfo";
                    $entity_id = max(array_keys($id_entity_map[$entity]));
                    for($i = 1;4 >= $i;++$i){
                        $content = $request_value['shop_master_content' . $i];
                        switch($i){
                            case 1:
                                $entity_field = 'company_name';
                                break;
                            case 2:
                                $entity_field = 'shop_name';
                                break;
                            case 3:
                                $entity_field = 'good_traded';
                                break;
                            case 4:
                                $entity_field = 'message';
                                break;
                        }
                        $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    }
                    break;
                case
                    $request_name === 'payment_register' &&
                    isset($request_value['payment_register_content']) &&
                    array_key_exists('Eccube\\Entity\\Payment', $id_entity_map):
                    $entity = 'Eccube\\Entity\\Payment';
                    $entity_id = max(array_keys($id_entity_map[$entity]));
                    $content = $request_value['payment_register_content'];
                    $entity_field = 'payment_method';
                    $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    break;
                case
                    $request_name === 'delivery' &&
                    isset($request_value['delivery_content']) &&
                    array_key_exists('Eccube\\Entity\\Delivery', $id_entity_map):
                    $entity = 'Eccube\\Entity\\Delivery';
                    $entity_id = max(array_keys($id_entity_map[$entity]));
                    $content = $request_value['delivery_content'];
                    $entity_field = 'name';
                    $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    if(isset($request_value['delivery_times']) && count($request_value['delivery_times']) > 0){
                        $delivery_times = $id_entity_map['Eccube\\Entity\\DeliveryTime'];
                        if(count($delivery_times) > 0){
                            $continue_flags = [];
                            /** @var DeliveryTime $delivery_time */
                            foreach($delivery_times as $delivery_time){
                                $id = $delivery_time->getId();
                                $delivery_time_value = $delivery_time->getDeliveryTime();
                                foreach($request_value['delivery_times'] as $key => $input_delivery_time){
                                    if(in_array($key, $continue_flags, true)){
                                        continue;
                                    }
                                    if($delivery_time_value === $input_delivery_time['delivery_time']){
                                        $entity = 'Eccube\\Entity\\DeliveryTime';
                                        $entity_id = $id;
                                        $entity_field = 'delivery_time';
                                        $content = $input_delivery_time['delivery_time_content'];
                                        $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                                        $continue_flags[] = $key;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    break;
                case
                    $request_name === 'config' &&
                    isset($request_value['names']) &&
                    !empty($request_value['names']) &&
                    array_key_exists('Plugin\\CrossBorder1\\Entity\\Config', $id_entity_map):
                    $entity = 'Plugin\\CrossBorder1\\Entity\\Config';
                    $continue_flags = [];
                    /** @var Config $Config  */
                    foreach($id_entity_map['Plugin\\CrossBorder1\\Entity\\Config'] as $Config){
                        foreach($request_value['names'] as $key => $name){
                            if(in_array($key, $continue_flags, true)){
                                continue;
                            }
                            if(
                                $Config->getName() === $name['name'] &&
                                $Config->getBackendName() === $name['backend_name']
                            ){
                                $entity_id = $Config->getId();
                                $entity_field = 'name';
                                $content = $name['config_name_content'];
                                $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                                $continue_flags[] = $key;
                            }
                        }
                    }
                    break;
                case
                    $request_name === 'mail' &&
                    isset($request_value['mail_content']) &&
                    !empty($request_value['mail_content']) &&
                    array_key_exists('Eccube\Entity\MailTemplate', $id_entity_map):
                    $entity = 'Eccube\\Entity\\MailTemplate';
                    $entity_id = $entity_id = max(array_keys($id_entity_map[$entity]));
                    $entity_field = 'mail_subject';
                    $content = $request_value['mail_content'];
                    $this->functions->handleLangContent($entity, $entity_id, $entity_field, $content, $locale, $em);
                    break;
                case //メール(shipping_notify)送信用にOrderとlocaleの組み合わせをOrderLangテーブルに保存
                    $request_name === '_shopping_order' &&
                    array_key_exists('Eccube\Entity\Order', $id_entity_map) &&
                    count($request_value) === 1 &&
                    isset($request_value['_token']):
                    $order_id = max(array_keys($id_entity_map['Eccube\\Entity\\Order']));
                    $this->functions->setOrderLang($em, $order_id, $locale);
                    break;
            }
        }
    }

    //各entityの削除処理でのLangContentの削除処理
    public function preRemove(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $Obj = $args->getObject();
        if(
            !empty($Obj) &&
            !$Obj instanceof BlockPosition &&
            !$Obj instanceof OrderPdf &&
            !$Obj instanceof PageLayout &&
            !$Obj instanceof PaymentOption &&
            !$Obj instanceof ProductCategory
        ){
            $entity = get_class($Obj);
            $entity_id = $Obj->getId();
            $this->functions->deleteLangContent($em, $entity, $entity_id);
        }
    }
}
