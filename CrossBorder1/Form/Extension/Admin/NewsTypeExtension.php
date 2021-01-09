<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Entity\News;
use Eccube\Form\Type\Admin\NewsType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Plugin\CrossBorder1\Repository\LangcontentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class NewsTypeExtension extends AbstractTypeExtension
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
        $builder->add(//タイトル
            'news_content1',
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
                                'max' => $this->eccubeConfig['eccube_mtext_len'],
                            ]
                        ),
                    ],
            ]
        )->add(//本文
            'news_content2',
            TextareaType::class,
            [
                'required' => false,
                'mapped' => false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                        'rows' => 8,
                    ],
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_ltext_len']]),
                ],
            ]
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            $locale = $this->request->getCurrentRequest()->getLocale();
            if(!empty($data) && $data instanceof News){
                $entity = get_class($data);
                $entity_id = $data->getId();
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => $entity,
                        'entity_id' => $entity_id,
                        'entity_field' => 'title',
                        'language' => $locale,
                    ]);
                if(!is_null($LangContent)){
                    $options = $form->get('news_content1')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'news_content1',
                        TextType::class,
                        $options
                    );
                }
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => $entity,
                        'entity_id' => $entity_id,
                        'entity_field' => 'description',
                        'language' => $locale,
                    ]
                );
                if(!is_null($LangContent)){
                    $options = $form->get('news_content2')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'news_content2',
                        TextareaType::class,
                        $options
                    );
                }
            }
        });
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event){
            if($this->eccubeConfig->get('locale') !== $this->request->getCurrentRequest()->getLocale()){
                $locale = $this->request->getCurrentRequest()->getLocale();
                $form = $event->getForm();
                $options = $form->get('publish_date')->getConfig()->getOptions();
                $this->request->getCurrentRequest()->setLocale($this->eccubeConfig->get('locale'));
                $form->add(
                    'publish_date',
                    DateTimeType::class,
                    $options
                );
                $this->request->getCurrentRequest()->setLocale($locale);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return NewsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => News::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_news';
    }
}
