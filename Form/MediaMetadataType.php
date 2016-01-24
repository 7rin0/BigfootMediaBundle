<?php

namespace Bigfoot\Bundle\MediaBundle\Form;

use Bigfoot\Bundle\CoreBundle\Form\Type\TranslatedEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaMetadataType
 * @package Bigfoot\Bundle\MediaBundle\Form
 */
class MediaMetadataType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();

                    if (!$data) {
                        return null;
                    }

                    $form->add(
                        'value',
                        TextType::class,
                        array(
                            'label' => $data->getType()
                        )
                    );
                }
            )
            ->add('value')
            ->add(
                'translation',
                TranslatedEntityType::class
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Bigfoot\Bundle\MediaBundle\Entity\MediaMetadata'
            )
        );
    }
}
