<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Entity\Category;
use Eccube\Form\Type\Admin\CategoryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Plugin\CrossBorder1\Repository\LangcontentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;

class CategoryTypeExtension extends AbstractTypeExtension
{
    private $trans;

    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    public function __construct(
        TranslatorInterface $trans,
        LangcontentRepository $langContentRepository,
        RequestStack $request,
        EccubeConfig $eccubeConfig
    )
    {
        $this->trans = $trans;
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'category_content',
            TextType::class,
            [
                'required' => false,
                'mapped'=> false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => $this->eccubeConfig['eccube_stext_len'],
                            ]
                        ),
                    ],
            ]
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            if(!empty($data) && $data instanceof Category){
                $entity = get_class($data);
                $locale = $this->request->getCurrentRequest()->getLocale();
                $entity_id = $data->getId();
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => $entity,
                        'entity_id' => $entity_id,
                        'language' => $locale,
                    ]);
                if(!is_null($LangContent)){
                    $options = $form->get('category_content')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'category_content',
                        TextType::class,
                        $options
                    );
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CategoryType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                   'data_class' => 'Eccube\Entity\Category',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_category';
    }
}
