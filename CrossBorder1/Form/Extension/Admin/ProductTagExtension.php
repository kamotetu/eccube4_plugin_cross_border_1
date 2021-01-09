<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Entity\Tag;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Plugin\CrossBorder1\Repository\LangcontentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\ProductTag;

class ProductTagExtension extends AbstractTypeExtension
{
    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    public function __construct(
        LangcontentRepository $langContentRepository,
        RequestStack $request,
        EccubeConfig $eccubeConfig
    )
    {
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
            'product_tag_content',
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
            if(!empty($data) && $data instanceof Tag){
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
                    $options = $form->get('product_tag_content')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'product_tag_content',
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
        return ProductTag::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_product_tag';
    }
}
