<?php

namespace Bigfoot\Bundle\MediaBundle\Form\DataTransformer;

use AppBundle\Entity\Issue;
use Bigfoot\Bundle\MediaBundle\Provider\Common\AbstractMediaProvider;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Media transformer
 */
class MediaTransformer implements DataTransformerInterface
{
    /**
     * @var AbstractMediaProvider
     */
    private $provider;

    /**
     * Constructor
     *
     * @param AbstractMediaProvider $provider
     */
    public function __construct(AbstractMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function transform($element)
    {
        if (!method_exists($this->provider, 'transform')) {
            return $element;
        }

        return $this->provider->transform($element);
    }

    public function reverseTransform($element)
    {
        if (!method_exists($this->provider, 'reverseTransform')) {
            return $element;
        }

        return $this->provider->reverseTransform($element);
    }
}
