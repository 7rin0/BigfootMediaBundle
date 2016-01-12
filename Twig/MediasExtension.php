<?php

namespace Bigfoot\Bundle\MediaBundle\Twig;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Bigfoot\Bundle\MediaBundle\Provider\Common\AbstractMediaProvider;

/**
 * Helper filter facilitating the display of an image from the portfolio.
 * Class MediasExtension
 * @package Bigfoot\Bundle\MediaBundle\Twig
 */
class MediasExtension extends \Twig_Extension
{
    /**
     * @var Request
     */
    private $requestStack;

    /**
     * @var AbstractMediaProvider
     */
    private $provider;

    /**
     * Sets the value of requestStack.
     *
     * @param RequestStack $requestStack the request stack
     *
     * @return self
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack->getCurrentRequest();

        return $this;
    }

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
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('media', array($this, 'mediaFilter')),
            new \Twig_SimpleFilter('medias', array($this, 'mediasFilter'))
        );
    }

    /**
    * @return array
    */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('media_details', array($this, 'getMediasWithDetails')),
        );
    }

    /**
     * @param $value
     * @return string
     */
    public function getMediasWithDetails($value)
    {
        $orderedMedias = array();

        if ($value) {
            $ids       = explode(';', $value);
            $results   = $this->provider->find($ids);
            $className = $this->provider->getClassName();

            if ($ids) {
                foreach ($results as $key => $media) {
                    $orderedMedias[$key] = $this->provider->getMediaDetails($this->request, $media);
                }
            }
        }

        return $orderedMedias;
    }

    /**
     * @param $value
     * @return string
     */
    public function mediaFilter($value)
    {
        $medias = $this->mediasFilter($value);

        if (empty($medias)) {
            return null;
        }

        return current($medias);
    }

    /**
     * @param $value
     * @return string
     */
    public function mediasFilter($value, $entities = false)
    {
        $orderedMedias = array();
        $accessor = PropertyAccess::createPropertyAccessor();

        if ($value) {
            $ids       = preg_match('/;/', $value) ? explode(';', $value) : $value;
            $results   = $this->provider->find($ids);
            $className = $this->provider->getClassName();

            if ($entities) {
                return $results;
            }

            if ($ids) {
                foreach ($results as $media) {
                    if (is_array($media)) {
                        $id = $accessor->getValue($media, '[id]');
                        $url = $accessor->getValue($media, '[url]');

                        if (empty($id) || empty($url)) {
                            continue;
                        }

                        $orderedMedias[$id] = $url;
                        continue;
                    }

                    if (!$media instanceof $className) {
                        continue;
                    }

                    $orderedMedias[$media->getId()] = $this->provider->getUrl($this->request, $media);
                }
            }
        }

        return $orderedMedias;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'medias';
    }
}
