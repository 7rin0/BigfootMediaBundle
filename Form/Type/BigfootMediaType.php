<?php

namespace Bigfoot\Bundle\MediaBundle\Form\Type;

use Bigfoot\Bundle\MediaBundle\Form\DataTransformer\MediaTransformer;
use Bigfoot\Bundle\MediaBundle\Provider\Common\AbstractMediaProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Helper type allowing to use the media portfolio popin in a form to fill in a media field.
 *
 * The corresponding entity attribute must be of type string (values are saved as a list of ids separated by a comma).
 *
 * Class BigfootMediaType
 * @package Bigfoot\Bundle\MediaBundle\Form\Type
 */
class BigfootMediaType extends AbstractType
{
    /**
     * @var AbstractMediaProvider
     */
    private $provider;

    /**
     * Sets the value of provider.
     *
     * @param AbstractMediaProvider $provider the provider
     *
     * @return self
     */
    public function setProvider(AbstractMediaProvider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Build form
     *
     * @param  FormBuilderInterface $builder
     * @param  array                $options
     *
     * @return null
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new MediaTransformer($this->provider));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace(
            $view->vars,
            array(
                'type'  => FileType::class,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['multipart'] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'compound' => false,
            )
        );
    }

    // TODO: consider getParent
}
