<?php

namespace Plugin\CrossBorder1\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Plugin\CrossBorder1\Entity\Config;
use Plugin\CrossBorder1\Form\Type\Admin\ConfigLangType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\RequestStack;


class ConfigType extends AbstractType
{

    private $eccubeConfig;

    private $request;

    public function __construct(
        EccubeConfig $eccubeConfig,
        RequestStack $request
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $options['data']['uploaded_files'];
        $builder->add(
            'names',
            CollectionType::class,
            [
                'entry_type' => ConfigLangType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ]
        )->add(
            'file',
            FileType::class,
            [
                'required' => false,
                'constraints' =>
                    [
                        new Assert\File(
                            [
                                'mimeTypes' => 'text/plain',
                            ]
                        ),
                    ]
            ]
        )->add(
            'uploaded_files',
            ChoiceType::class,
            [
                'mapped' => false,
                'choices' => $choices,
            ]
        );

        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event){
            if($this->request->getCurrentRequest() !== null){
                $mode = $this->request->getCurrentRequest()->get('mode');
                if($mode === 'upload'){
                    $data = $event->getData();
                    if(is_null($data['file'])){
                        $form = $event->getForm();
                        $form->get('file')->addError(new FormError('ファイルが選択されていません。'));
                    }
                }
            }
        });
    }


}
