<?php

namespace Plugin\CrossBorder1\Form\Extension\Master;

use Eccube\Form\Type\Master\JobType;
use Eccube\Form\Type\MasterType;
use Plugin\CrossBorder1\Entity\LangContent;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\Master\JobRepository;

class JobTypeExtension extends AbstractTypeExtension
{
    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    private $jobRepository;

    public function __construct(
        LangContentRepository $langContentRepository,
        RequestStack $request,
        EccubeConfig $eccubeConfig,
        JobRepository $jobRepository
    )
    {
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
        $this->eccubeConfig = $eccubeConfig;
        $this->jobRepository = $jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //元の設定と同じ言語もしくは会員登録submit時は処理しない(confirm時は処理する)
        //プロフィール変更時のsubmit時も処理しない
        //管理画面でも処理しない
        if(
            $this->eccubeConfig->get('locale') === $this->request->getCurrentRequest()->getLocale() ||
            $this->request->getCurrentRequest()->get('mode') === 'complete' ||
            strpos($this->request->getCurrentRequest()->getUri(), $this->eccubeConfig->get('eccube_admin_route')) !== false ||
            (
                $this->request->getCurrentRequest()->request->has('entry') &&
                $this->request->getCurrentRequest()->getMethod() === 'POST' &&
                !$this->request->getCurrentRequest()->request->has('mode')
            )
        ){
            return;
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $options = $event->getForm()->getConfig()->getOptions();
            $form = $event->getForm();
            $choice_list = $form->getConfig()->getAttribute('choice_list');
            $choices = $choice_list->getChoices();
            $entity = $options['class'];
            $locale = $this->request->getCurrentRequest()->getLocale();
            foreach($choices as $key => $Obj){
                /**@var LangContent $LangContent */
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
        return JobType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'class' => 'Eccube\Entity\Master\Job',
                'placeholder' => 'common.select',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'job';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return MasterType::class;
    }
}
