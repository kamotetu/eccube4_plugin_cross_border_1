<?php

namespace Plugin\CrossBorder1\Form\Extension;

use Eccube\Entity\CartItem;
use Eccube\Form\Type\AddCartType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Plugin\CrossBorder1\Entity\LangContent;
use Eccube\Common\EccubeConfig;


class AddCartTypeExtension extends AbstractTypeExtension
{
    private $request;

    private $langContentRepository;

    private $eccubeConfig;

    public function __construct(
        RequestStack $request,
        LangContentRepository $langContentRepository,
        EccubeConfig $eccubeConfig
    )
    {
        $this->request = $request;
        $this->langContentRepository = $langContentRepository;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //元の設定と同じ言語もしくは会員登録submit時は処理しない(confirm時は処理する)
        //管理画面でも処理しない
        if(
            $this->eccubeConfig->get('locale') === $this->request->getCurrentRequest()->getLocale() ||
            strpos($this->request->getCurrentRequest()->getUri(), $this->eccubeConfig->get('eccube_admin_route')) !== false
        ){
            return;
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            if($form->has('classcategory_id1')){
                $options = $form->get('classcategory_id1')->getConfig()->getOptions();
                $choices = $options['choices'];
                $trans_choices = [];
                if(!empty($choices)){
                    foreach($choices as $key => $choice){
                        /** @var LangContent $LangContent */
                        $LangContent = $this->langContentRepository->findOneBy(
                            [
                                'entity' => 'Eccube\\Entity\\ClassCategory',
                                'entity_id' => $choice,
                                'entity_field' => 'name',
                                'language' => $this->request->getCurrentRequest()->getLocale(),
                            ]
                        );
                        if(!is_null($LangContent)){
                            $key = $LangContent->getContent();
                        }
                        $trans_choices[$key] = $choice;
                    }
                }
                $options['choices'] = $trans_choices;
                $form->add(
                    'classcategory_id1',
                    ChoiceType::class,
                    $options
                );
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return AddCartType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('product');
        $resolver->setDefaults(
            [
               'data_class' => CartItem::class,
               'id_add_product_id' => true,
               'constraints' => [
                   // FIXME new Assert\Callback(array($this, 'validate')),
               ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'add_cart';
    }
}
