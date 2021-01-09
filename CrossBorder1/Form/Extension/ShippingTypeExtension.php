<?php

namespace Plugin\CrossBorder1\Form\Extension;

use Eccube\Form\Type\Shopping\ShippingType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Repository\LangContentRepository;


class ShippingTypeExtension extends AbstractTypeExtension
{
    private $request;

    private $langContentRepository;

    public function __construct(
        RequestStack $request,
        LangContentRepository $langContentRepository
    )
    {
        $this->request = $request;
        $this->langContentRepository = $langContentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //注文手続き画面で確認ボタンを押すと選択されたvalueでentityが上書きされて保存される(元のデータが多言語化される)ので以下の処理はさせない
        if($this->request->getCurrentRequest()->getMethod() === 'POST'){
            return;
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $options = $event->getForm()->get('Delivery')->getConfig()->getOptions();
            $form = $event->getForm();
            $choice_list = $form->get('Delivery')->getConfig()->getAttribute('choice_list');
            $choices = $choice_list->getChoices();
            $entity = $options['class'];
            $locale = $this->request->getCurrentRequest()->getLocale();
            foreach($choices as $key => $Obj){
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => $entity,
                        'entity_id' => $Obj->getId(),
                        'entity_field' => 'name',
                        'language' => $locale
                    ]);
                if(!is_null($LangContent)){
                    $choices[$key]->setName($LangContent->getContent());
                }
            }
            $choice_loader = new CallbackChoiceLoader(function() use ($choices){
                return $choices;
            });
            $options['choice_loader'] = $choice_loader;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ShippingType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Eccube\Entity\Shipping',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '_shopping_shipping';
    }
}
