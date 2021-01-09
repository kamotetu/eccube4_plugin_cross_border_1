<?php

namespace Plugin\CrossBorder1\Form\Type\Common;

use Plugin\CrossBorder1\Entity\Config;
use Plugin\CrossBorder1\Entity\LangContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Translation\TranslatorInterface;
use Plugin\CrossBorder1\Repository\ConfigRepository;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ChangeLangType extends AbstractType
{
    private $session;

    private $eccubeConfig;

    private $trans;

    private $configRepository;

    private $langContentRepository;

    private $request;

    public function __construct(
        SessionInterface $session,
        EccubeConfig $eccubeConfig,
        TranslatorInterface $trans,
        ConfigRepository $configRepository,
        LangContentRepository $langContentRepository,
        RequestStack $request
    )
    {
        $this->session = $session;
        $this->eccubeConfig = $eccubeConfig;
        $this->trans = $trans;
        $this->configRepository = $configRepository;
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];

        $admin_route = $this->eccubeConfig->get('eccube_admin_route');

        if(strpos($this->request->getCurrentRequest()->getUri(), $admin_route) === false){
            $Configs = $this->configRepository->findBy(
                [
                    'visible' => 1,
                ],
                [
                    'sort_no' => 'ASC',
                ]
            );
        }else{
            $Configs = $this->configRepository->findBy(
                [
                    //
                ],
                [
                    'sort_no' => 'ASC',
                ]
            );
        }

        if(!empty($Configs)){
            /**@var Config $Config*/
            foreach($Configs as $key => $Config){
                if(strpos($this->request->getCurrentRequest()->getUri(), $admin_route) === false){
                    /**@var LangContent $LangContent */
                    $LangContent = $this->langContentRepository->findOneBy(
                        [
                            'entity' => get_class($Config),
                            'entity_id' => $Config->getId(),
                            'entity_field' => 'name',
                            'language' => $this->request->getCurrentRequest()->getLocale(),
                        ]
                    );
                    if(!is_null($LangContent)){
                        $choices[$LangContent->getContent()] = $Config->getBackendName();
                    }else{
                        $choices[$Config->getName()] = $Config->getBackendName();
                    }
                }else{
                    $choices[$Config->getName()] = $Config->getBackendName();
                }
            }
        }
        if(!empty($this->session->get('lang'))){
            $data = $this->session->get('lang');
        }else{
            $data = $this->eccubeConfig->get('locale');
        }
        $builder->add(
            'lang',
            ChoiceType::class,
            [
                'placeholder' => false,
                'data' => $data,
                'required' => false,
                'choices' => $choices,
                'attr' =>
                    [
                        'onChange' => 'get_language_value()',
                    ],
            ]
        );
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            []
        );
    }
}
