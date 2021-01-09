<?php

namespace Plugin\CrossBorder1\Form\Extension\Front;

use Eccube\Form\Type\Front\EntryType;
use Plugin\CrossBorder1\Entity\LangContent;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Eccube\Common\EccubeConfig;

class EntryTypeExtension extends AbstractTypeExtension
{
    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    public function __construct(
        LangContentRepository $langContentRepository,
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
        //元の設定と同じ言語もしくは会員登録submit時は処理しない(confirm時は処理する)
        //プロフィール編集でも処理しない
        if(
            $this->eccubeConfig->get('locale') === $this->request->getCurrentRequest()->getLocale() ||
            $this->request->getCurrentRequest()->get('mode') === 'complete' ||
            (
                $this->request->getCurrentRequest()->request->has('entry') &&
                $this->request->getCurrentRequest()->getMethod() === 'POST' &&
                !$this->request->getCurrentRequest()->request->has('mode')
            )
        ){
            return;
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $sex_form = $form->get('sex');
            $children = $sex_form->all();
            foreach($children as $key => $child){
                $options = $child->getConfig()->getOptions();
                /**@var LangContent $LangContent */
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => 'Eccube\\Entity\\Master\\Sex',
                        'entity_id' => $options['value'],
                        'entity_field' => 'name',
                        'language' => $this->request->getCurrentRequest()->getLocale(),
                    ]
                );
                if(!is_null($LangContent)){
                    $options['label'] = $LangContent->getContent();
                    $sex_form->remove($key);
                }
                $sex_form->add(
                    $key,
                    RadioType::class,
                    $options
                );
            }
        });
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event){
            $form = $event->getForm();
            $sex_form = $form->get('sex');
            $data = $sex_form->getViewData();
            /** @var LangContent $LangContent */
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => $sex_form->getConfig()->getOptions()['class'],
                    'entity_id' => $data,
                    'entity_field' => 'name',
                    'language' => $this->request->getCurrentRequest()->getLocale(),
                ]
            );
            if(!is_null($LangContent)){
                $sex_form->getNormData()->setName($LangContent->getContent());
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return EntryType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Eccube\Entity\Customer',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        // todo entry,mypageで共有されているので名前を変更する
        return 'entry';
    }
}
