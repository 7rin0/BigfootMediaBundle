<?php

namespace Bigfoot\Bundle\MediaBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaType
 * @package Bigfoot\Bundle\MediaBundle\Form
 */
class MediaType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $this->entityManager;

        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($em) {
                    $data = $event->getData();
                    $form = $event->getForm();

                    if (!$data) {
                        return null;
                    }

                    $mediaRepo = $em->getRepository('BigfootMediaBundle:Media');
                    $mediaRepo->initMetadata($data);

                    $form->add(
                        'metadatas',
                        CollectionType::class,
                        array(
                            'label' => ' ',
                            'type' => new MediaMetadataType(),
                            'options' => array(
                                'required' => false,
                                'attr' => array(
                                    'class' => 'metadatas'
                                )
                            ),
                        )
                    );
                }
            )
            ->add(
                'tags',
                'bigfoot_tag',
                array(
                    'label' => 'Tags',
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Bigfoot\Bundle\MediaBundle\Entity\Media'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_media_mediatype';
    }
}
