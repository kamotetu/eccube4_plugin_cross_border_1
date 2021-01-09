<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Form\Type\Master\ProductListMaxType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Eccube\Repository\Master\ProductListMaxRepository;
use Symfony\Component\Translation\TranslatorInterface;
use Eccube\Form\Type\Admin\MasterdataDataType;
use Plugin\CrossBorder1\Repository\LangcontentRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class MasterdataDataTypeExtension extends AbstractTypeExtension
{
    private $trans;

    private $langContentRepository;

    private $request;

    public function __construct(
        TranslatorInterface $trans,
        LangcontentRepository $langContentRepository,
        RequestStack $request
    )
    {
        $this->trans = $trans;
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'masterdata_content',
            TextType::class,
            [
                'required' => false,
                'mapped'=> false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
            ]
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            if(!empty($data)){
                $masterdata = $form->getParent()->getParent()->getData()['masterdata_name'];
                $entity = str_replace('-', '\\', $masterdata);
                $request = $this->request->getCurrentRequest();
                $content_form = $form->get('masterdata_content');
                $options = $content_form->getConfig()->getOptions();
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => $entity,
                        'entity_id' => $data['id'],
                        'language' => $request->getLocale(),
                    ]);
                if(!is_null($LangContent)){
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'masterdata_content',
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
        return MasterdataDataType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_system_masterdata_data';
    }
}
