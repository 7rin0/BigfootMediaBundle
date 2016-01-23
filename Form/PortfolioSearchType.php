<?php

namespace Bigfoot\Bundle\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PortfolioSearchType
 *
 * @package Bigfoot\Bundle\MediaBundle\Form
 */
class PortfolioSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'search',
            TextType::class,
            array(
                'label'     => 'context.term',
                'required'  => false,
                'attr' => array(
                    'data-portfolio-input' => 'search'
                )
            )
        );
    }
}
