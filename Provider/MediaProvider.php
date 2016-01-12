<?php

namespace Bigfoot\Bundle\MediaBundle\Provider;

use Bigfoot\Bundle\MediaBundle\Entity\Media;
use Bigfoot\Bundle\MediaBundle\Form\PortfolioSearchData;
use Symfony\Component\HttpFoundation\Request;

use Bigfoot\Bundle\MediaBundle\Provider\Common\AbstractMediaProvider;

/**
 * Media provider
 *
 * @package Bigfoot\Bundle\MediaBundle
 */
class MediaProvider extends AbstractMediaProvider
{
    /**
     * Get class name
     *
     * @return string
     */
    public function getClassname()
    {
        return 'Bigfoot\Bundle\MediaBundle\Entity\Media';
    }
    /**
     * Get form type
     *
     * @return string
     */
    public function getFormType()
    {
        return 'bigfoot_media_mediatype';
    }

    /**
     * Get form template
     *
     * @return string
     */
    public function getFormTemplate()
    {
        return $this->getThemeBundle().':snippets:edit.html.twig';
    }

    /**
     * Get form template
     *
     * @return string
     */
    public function getLineTemplate()
    {
        return $this->getThemeBundle().':snippets:table_line.html.twig';
    }

    /**
     * Configuration
     *
     * @return array
     */
    protected function configuration()
    {
        return array(
            'upload' => true,
            'edit'   => true,
            'delete' => true,
            'search' => true
        );
    }

    /**
     * Get url
     *
     * @param  RequestStack $requestStack
     * @param  mixed  $media
     *
     * @return string
     */
    public function getUrl(RequestStack $requestStack, $media)
    {
        return sprintf('%s/%s', $requestStack->getCurrentRequest()->getBasePath(), $media->getFile());
    }

    /**
     * Get media details
     *
     * @param  mixed $media
     *
     * @return array
     */
    public function getMediaDetails(RequestStack $requestStack, $media)
    {
        return  array(
            'file'   => $this->getUrl($request, $media),
            'title'  => $media->getMetadata('title'),
            'width'  => $media->getMetadata('width'),
            'height' => $media->getMetadata('height')
        );
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        $queryString = $this->session->get('bigfoot_media.portfolio.search');

        $qb = $this
            ->getRepository()
            ->createQueryBuilder('e');

        $query = $qb
            ->select('COUNT(DISTINCT e)')
            ->leftJoin('e.metadatas', 'm')
            ->leftJoin('e.tags', 't');

        if ($queryString) {
            $search = sprintf('%%%s%%', $queryString);

            $query
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('m.value', ':search'),
                        $qb->expr()->like('e.file', ':search'),
                        $qb->expr()->like('t.name', ':search')
                    )
                )
                ->setParameter('search', $search);
        }

        return $query
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find medias
     *
     * @param  integer $offset
     * @param  integer $limit
     *
     * @return array
     */
    public function findAll($offset = 0, $limit = 20)
    {
        $queryString = $this->session->get('bigfoot_media.portfolio.search');

        $qb = $this
            ->getRepository()
            ->createQueryBuilder('e');

        $query = $qb
            ->select('DISTINCT e')
            ->leftJoin('e.metadatas', 'm')
            ->leftJoin('e.tags', 't');

        if ($queryString) {
            $search = sprintf('%%%s%%', $queryString);

            $query
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('m.value', ':search'),
                        $qb->expr()->like('e.file', ':search'),
                        $qb->expr()->like('t.name', ':search')
                    )
                )
                ->setParameter('search', $search);
        }

        return $query
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get medias
     *
     * @param  mixed $identifier
     *
     * @return array
     */
    public function find($identifier)
    {
        if (!is_array($identifier)) {
            return $this
                ->getRepository()
                ->createQueryBuilder('e')
                ->where('e.id IN (:ids)')
                ->setParameter('ids', $identifier)
                ->getQuery()
                ->getResult();
        }

        $medias = $this
            ->getRepository()
            ->createQueryBuilder('e')
            ->where('e.id IN (:ids)')
            ->setParameter('ids', $identifier)
            ->getQuery()
            ->getResult();

        $mediasUnsorted = $ordered = array();

        /** @var Media $media */
        foreach ($medias as $media) {
            $mediasUnsorted[$media->getId()] = $media;
        }

        foreach ($identifier as $id) {
            if (isset($mediasUnsorted[$id])) {
                $ordered[$id] = $mediasUnsorted[$id];
            }
        }

        return $ordered;
    }

    /**
     * Search
     *
     * @param  mixed  $model
     * @param  integer $offset
     * @param  integer $limit
     *
     * @return array
     */
    public function search($model, $offset = 0, $limit = 20)
    {
        $qb = $this
            ->getRepository()
            ->createQueryBuilder('e')
            ->select('DISTINCT e');

        $query = $qb;

        $queryString = $model->getSearch();

        if (!empty($queryString)) {
            $search = sprintf('%%%s%%', $queryString);

            $query
                ->leftJoin('e.metadatas', 'm')
                ->leftJoin('e.tags', 't')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('m.value', ':search'),
                        $qb->expr()->like('e.file', ':search'),
                        $qb->expr()->like('t.name', ':search')
                    )
                )
                ->setParameter('search', $search);
        }

        return $query
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get search form type
     *
     * @return string
     */
    public function getSearchFormType()
    {
        return 'bigfoot_portfolio_search';
    }

    /**
     * Get search form type
     *
     * @return string
     */
    public function getSearchData()
    {
        $search = new PortfolioSearchData();

        $queryString = $this->session->get('bigfoot_media.portfolio.search');

        if (!empty($queryString)) {
            $search
                ->setSearch($queryString);
        }

        return $search;
    }

    /**
     * Get search session key
     *
     * @return string
     */
    public function getSearchSessionKey()
    {
        return 'bigfoot_media.portfolio.search';
    }
}
