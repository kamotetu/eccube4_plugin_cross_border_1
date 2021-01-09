<?php

namespace Plugin\CrossBorder1\Form\Extension\Shopping;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\DeliveryTime;
use Eccube\Form\Type\Shopping\ShippingType;
use Plugin\CrossBorder1\Entity\LangContent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Eccube\Common\EccubeConfig;
use Twig\Environment;


class ShippingTypeExtension extends AbstractTypeExtension
{
    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    private $env;

    public function __construct(
        LangContentRepository $langContentRepository,
        RequestStack $request,
        EccubeConfig $eccubeConfig,
        Environment $env
    )
    {
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
        $this->eccubeConfig = $eccubeConfig;
        $this->env = $env;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //注文手続き画面で確認ボタンを押すと選択されたvalueでentityが上書きされて保存される(元のデータが多言語化される)ので以下の処理はさせない
        if(
            $this->request->getCurrentRequest()->getMethod() === 'POST' ||
            $this->request->getCurrentRequest()->getLocale() === $this->eccubeConfig->get('locale')
        ){
            return;
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){

            $form = $event->getForm();
            $options1 = $form->get('shipping_delivery_date')->getConfig()->getOptions();
            $convert_choices = [];
            if(!empty($options1['choices'])){
                foreach($options1['choices'] as $key => $value){
                    $convert_key = $this->changeDateFormat($key);
                    $convert_choices[$convert_key] = $value;
                }
            }
            $options1['choices'] = $convert_choices;
            $form->add(
                'shipping_delivery_date',
                ChoiceType::class,
                $options1
            );

            $options2 = $form->get('DeliveryTime')->getConfig()->getOptions();

            /** @var ArrayCollection $choices */
            $choices = $options2['choices'];
            if(!empty($choices->getValues())){
                $ArrayCollection = new ArrayCollection();
                /** @var DeliveryTime $Obj */
                foreach($choices as $Obj){
                    /** @var LangContent $LangContent */
                    $LangContent = $this->langContentRepository->findOneBy(
                        [
                            'entity' => get_class($Obj),
                            'entity_id' => $Obj->getId(),
                            'entity_field' => 'delivery_time',
                            'language' => $this->request->getCurrentRequest()->getLocale(),
                        ]
                    );
                    if(!is_null($LangContent)){
                        $Obj->setDeliveryTime($LangContent->getContent());
                    }
                    $ArrayCollection->add($Obj);
                }
                $options2['choices'] = $ArrayCollection;
                $form->add(
                    'DeliveryTime',
                    EntityType::class,
                    $options2
                );
            }

        });
    }

    public function changeDateFormat($key)
    {
        $none_week_date = preg_replace('/\(.+\)\z/u', '', $key);
        $date = new \DateTimeImmutable($none_week_date);
        return \twig_localized_date_filter($this->env, $date, 'medium', 'none');
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
