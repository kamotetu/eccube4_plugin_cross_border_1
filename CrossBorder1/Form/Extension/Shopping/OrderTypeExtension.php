<?php

namespace Plugin\CrossBorder1\Form\Extension\Shopping;

use Eccube\Form\Type\Shopping\OrderType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OrderTypeExtension extends AbstractTypeExtension
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
        //注文手続き画面で確認ボタンを押すと選択されたvalueでentityが上書きされて保存される(元のデータが多言語化される)ので以下の処理はさせない
        if($this->request->getCurrentRequest()->getMethod() === 'POST'){
            return;
        }
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            if(array_key_exists('Payment', $form->all())){
                $payment_form = $form->get('Payment');
                $options = $payment_form->getConfig()->getOptions();
                $choices = $options['choices'];
                $locale = $this->request->getCurrentRequest()->getLocale();
                foreach($choices as $key => $Payment){
                    $LangContent = $this->langContentRepository->findOneBy(
                        [
                            'entity' => $options['class'],
                            'entity_id' => $Payment->getId(),
                            'entity_field' => 'payment_method',
                            'language' => $locale,
                        ]
                    );
                    if(!is_null($LangContent)){
                        $choices[$key]->setMethod($LangContent->getContent());
                    }
                }
                $choice_loader = new CallbackChoiceLoader(function() use ($choices){
                    return $choices;
                });
                $options['choice_loader'] = $choice_loader;
                $form->add(
                    'Payment',
                    EntityType::class,
                    $options
                );
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OrderType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '_shopping_order';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Eccube\Entity\Order',
                'skip_add_form' => false,
            ]
        );
    }
}
