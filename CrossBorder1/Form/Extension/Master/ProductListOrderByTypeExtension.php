<?php

namespace Plugin\CrossBorder1\Form\Extension\Master;

use Eccube\Form\Type\Master\ProductListOrderByType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Repository\LangContentRepository;


class ProductListOrderByTypeExtension extends AbstractTypeExtension
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $options = $event->getForm()->getConfig()->getOptions();
            $form = $event->getForm();
            $choice_list = $form->getConfig()->getAttribute('choice_list');
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
        return ProductListOrderByType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'class' => 'Eccube\Entity\Master\ProductListOrderBy',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_list_order_by';
    }
}
