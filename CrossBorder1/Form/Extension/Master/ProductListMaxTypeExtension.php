<?php

namespace Plugin\CrossBorder1\Form\Extension\Master;

use Eccube\Form\Type\Master\ProductListMaxType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Repository\LangContentRepository;

class ProductListMaxTypeExtension extends AbstractTypeExtension
{
    private $langContentRepository;

    private $request;

    public function __construct(
        LangContentRepository $langContentRepository,
        RequestStack $request
    )
    {
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
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
        return ProductListMaxType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'class' => 'Eccube\Entity\Master\ProductListMax',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_list_max';
    }
}
