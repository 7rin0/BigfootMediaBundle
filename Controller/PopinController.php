<?php

namespace Bigfoot\Bundle\MediaBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Controller\BaseController;
use Bigfoot\Bundle\MediaBundle\Entity\Media;
use Bigfoot\Bundle\MediaBundle\Entity\MediaRepository;
use Bigfoot\Bundle\MediaBundle\Event\PortfolioEvent;
use Bigfoot\Bundle\MediaBundle\Provider\Common\AbstractMediaProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bigfoot PopinController
 * Implements the routes necessary to display the media management module.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/portfolio")
 */
class PopinController extends BaseController
{
    /**
     * Get media provider
     *
     * @return AbstractMediaProvider
     */
    protected function getMediaProvider()
    {
        $provider = $this->container->getParameter('bigfoot_media.provider');

        if (!empty($provider) && $this->container->has($provider)) {
            return $this->get($provider);
        }

        return $this->get('bigfoot_media.provider.media');
    }

    /**
     * Get elements per Page
     *
     * @return integer
     */
    protected function getElementsPerPage()
    {
        return $this->container->getParameter('bigfoot_media.pagination_per_page');
    }

    /**
     * Get theme bundle
     *
     * @return string
     */
    protected function getThemeBundle()
    {
        $theme = $this->get('bigfoot.theme');

        return $theme->getTwigNamespace();
    }

    /**
     * Displays the popin used for media selection in forms
     *
     * @Route("/popin/{id}", name="portfolio_popin", defaults={"id"=null})
     * @Method("GET")
     * @Template("BigfootMediaBundle::popin.html.twig")
     *
     * @param integer $id
     *
     * @return array
     */
    public function popinAction($id)
    {
        $this->get('event_dispatcher')->dispatch(PortfolioEvent::OPEN_POPIN, new GenericEvent($id));

        $provider = $this->getMediaProvider();

        $orderedMedias    = array();
        $selectedMediaIds = array();
        $allMedias        = $provider->findAll(0, $this->getElementsPerPage());

        if ($id) {
            $selectedMediaIds = explode(';', $id);
            $orderedMedias    = $provider->find($selectedMediaIds);
        }

        $search = $provider->getSearchData();

        $form = $this->createForm($provider->getSearchFormType(), $search);

        return array(
            'allMedias'      => $allMedias,
            'selectedMedias' => $orderedMedias,
            'mediaIds'       => $selectedMediaIds,
            'form'           => $form->createView(),
            'template_line'  => $provider->getLineTemplate(),
            'perPage'        => $this->getElementsPerPage(),
            'total'          => $provider->getTotal(),
            'configuration'  => $provider->getConfiguration()
        );
    }

    /**
     * @Route("/popin/search", name="portfolio_search")
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchAction(RequestStack $requestStack)
    {
        $provider = $this->getMediaProvider();
        $search   = $provider->getSearchData();
        $requestStack = $requestStack->getCurrentRequest();
        $form     = $this->createForm($provider->getSearchFormType(), $search);
        $form->handleRequest($requestStack);

        if ($form->isValid()) {
            $this->getSession()->set($provider->getSearchSessionKey(), $search->getSearchForSession());

            $results = $provider->search($search, 0, $this->getElementsPerPage());

            $selected = $requestStack->get('ids', '');
            $selected = explode(';', $selected);

            $render = $this->render(
                $this->getThemeBundle().':snippets:table.html.twig',
                array(
                    'allMedias'     => $results,
                    'mediaIds'      => $selected,
                    'pagination'    => true,
                    'template_line' => $provider->getLineTemplate(),
                    'perPage'       => $this->getElementsPerPage(),
                    'total'         => $provider->getTotal(),
                    'configuration' => $provider->getConfiguration()
                )
            );

            return new JsonResponse(
                array(
                    'status'  => 200,
                    'content' => $render->getContent()
                )
            );
        }

        return new JsonResponse(false);
    }

    /**
     * Pagination in popin for media
     *
     * @Route("/popin/paginate", name="portfolio_popin_paginate")
     * @Method("POST")
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function paginateAction(RequestStack $requestStack)
    {
        $provider = $this->getMediaProvider();
        $requestStack = $requestStack->getCurrentRequest();
        $page     = $requestStack->get('page', null);
        $selected = $requestStack->get('selected', '');
        $selected = explode(';', $selected);

        if (empty($page)) {
            return new JsonResponse(
                array(
                    'status'  => 400,
                    'message' => 'missing required parameter page'
                )
            );
        }

        $start  = $page == 1 ? 0 : ($page - 1) * $this->getElementsPerPage();
        $medias = $provider->findAll($start, $this->getElementsPerPage());

        $render = $this->render(
            $this->getThemeBundle().':snippets:table.html.twig',
            array(
                'allMedias'     => $medias,
                'mediaIds'      => $selected,
                'template_line' => $provider->getLineTemplate(),
                'total'         => $provider->getTotal(),
                'configuration' => $provider->getConfiguration()
            )
        );

        return new JsonResponse(
            array(
                'status'  => 200,
                'content' => $render->getContent()
            )
        );
    }

    /**
     * Displays an edit form for a media object
     *
     * @Route("/popin/{id}/edit", name="portfolio_edit")
     * @Method({"GET", "POST"})
     * @Template("BigfootMediaBundle:snippets:edit.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param                                           $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(RequestStack $requestStack, $id)
    {
        $provider = $this->getMediaProvider();
        $requestStack = $requestStack->getCurrentRequest();

        if (!$provider->getConfiguration('edit', false)) {
            return new Response();
        }

        $media = $provider->find($id);
        $form  = $this->createForm($provider->getFormType(), $media);

        if ($requestStack->isMethod('POST')) {
            $form->handleRequest($requestStack);

            if ($form->isValid()) {
                $this->persistAndFlush($media);

                $render = $this->render(
                    $provider->getFormTemplate(),
                    array(
                        'form'  => $form->createView(),
                        'media' => $media,
                        'id'    => $id
                    )
                );

                return new Response(
                    json_encode(
                        array(
                            'id'      => $id,
                            'success' => true,
                            'object'  => $media,
                            'html'    => $render->getContent()
                        )
                    ),
                    200,
                    array(
                        'Content-Type',
                        'application/json'
                    )
                );
            }
        }

        $render = $this->render(
            $provider->getFormTemplate(),
            array(
                'form'  => $form->createView(),
                'media' => $media,
                'id'    => $id
            )
        );

        return new Response(
            json_encode(
                array(
                    'html' => $render->getContent()
                )
            ),
            200,
            array(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * Handles deletion
     *
     * @Route("/popin/{id}/delete", name="portfolio_delete")
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $em = $this->getEntityManager();

        $media = $this->getMedia($id);

        $em->remove($media);
        $em->flush();

        return new Response(
            json_encode(
                array(
                    'id'      => $id,
                    'success' => true,
                )
            ),
            200,
            array(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * Handles upload
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @Route("/upload", name="portfolio_upload")
     */
    public function uploadAction(RequestStack $requestStack)
    {
        // retrieves the posted data, for reference
        $requestStack = $requestStack->getCurrentRequest();
        $file = $requestStack->get('value');
        $name = $requestStack->get('name');

        $json = array(
            'name'    => $name,
            'success' => false,
            'html'    => null,
        );

        // separate out the data
        $data = explode(',', $file);
        // encode it correctly
        $encodedData = str_replace(' ', '+', $data[1]);
        $decodedData = base64_decode($encodedData);

        $media = new Media();

        // generate new name, relative and absolute path
        $fileManager  = $this->container->get('bigfoot_core.manager.file_manager');
        $name         = $fileManager->sanitizeName($name);
        $image        = uniqid().'_'.$name;
        $directory    = $this->getUploadDir();
        $absolutePath = $directory.'/'.$image;

        if (!file_exists($directory)) {
            $filesystem = $this->get('filesystem');
            $filesystem->mkdir($directory, 0777);
        }

        if (file_put_contents($absolutePath, $decodedData)) {
            $relativePath = $this->container->getParameter('bigfoot.core.upload_dir').$this->container->getParameter('bigfoot.media.portfolio_dir').$image;
            $imageInfos   = getimagesize($absolutePath);
            $media
                ->setType($imageInfos['mime'])
                ->setFile($relativePath);

            $em = $this->getEntityManager();

            $em->persist($media);
            $em->flush();

            /** @var MediaRepository $mediaRepository */
            $mediaRepository = $em->getRepository('BigfootMediaBundle:Media');

            $mediaRepository->setMetadata($media, 'title', $name);
            $mediaRepository->setMetadata($media, 'width', $imageInfos[0]);
            $mediaRepository->setMetadata($media, 'height', $imageInfos[1]);
            $mediaRepository->setMetadata($media, 'size', $media->convertFileSize(filesize($absolutePath)));

            $em->flush();

            $json['success'] = true;
            $json['html']    = $this
                ->container
                ->get('twig')
                ->render(
                    'BigfootMediaBundle:snippets:table_line.html.twig',
                    array(
                        'line' => $media,
                        'used' => false,
                    )
                );
        }

        return new Response(json_encode($json), 200, array('Content-Type', 'application/json'));
    }

    /**
     * Get media object from ID
     *
     * @param $id
     *
     * @return Media
     */
    private function getMedia($id)
    {
        $em = $this->getEntityManager();
        /** @var MediaRepository $mediaRepository */
        $mediaRepository = $em->getRepository('BigfootMediaBundle:Media');

        return $mediaRepository->find($id);
    }

    /**
     * @param bool $absolute
     *
     * @return string
     */
    private function getUploadDir($absolute = true)
    {
        $dir = '';

        if ($absolute) {
            $dir .= $this->get('kernel')->getRootDir().'/../web';
        }

        return rtrim($dir, '/').sprintf(
            '/%s/%s',
            trim($this->container->getParameter('bigfoot.core.upload_dir'), '/'),
            trim($this->container->getParameter('bigfoot.media.portfolio_dir'), '/')
        );
    }
}
