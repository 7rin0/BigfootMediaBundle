<?php

namespace Bigfoot\Bundle\MediaBundle\Controller;

use Bigfoot\Bundle\MediaBundle\Entity\MediaRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Doctrine\ORM\AbstractQuery;

use Bigfoot\Bundle\CoreBundle\Controller\BaseController;
use Bigfoot\Bundle\CoreBundle\Entity\Tag;
use Bigfoot\Bundle\MediaBundle\Entity\Media;

/**
 * Bigfoot MediaController. Implements the routes necessary to display the media management module.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/portfolio")
 */
class MediaController extends BaseController
{
    /**
     * Displays the list of persisted medias.
     *
     * @Route("/", name="portfolio_dashboard")
     * @Method("GET")
     * @Template("BigfootMediaBundle::portfolio.html.twig")
     */
    public function portfolioDashboardAction()
    {
        return array();
    }

    /**
     * Add a tag
     *
     * @Route("/tag/add", name="portfolio_tag_add")
     */
    public function addTagAction(RequestStack $requestStack)
    {
        $em = $this->container->get('doctrine')->getManager();

        $tag = new Tag();
        $tag->setName($request->get('tag'));

        $em->persist($tag);
        $em->flush();

        return new Response(json_encode(
            array(
                'html' => $this->container->get('twig')->render('BigfootMediaBundle:snippets:tag_option.html.twig', array(
                        'tag' => $tag,
                )),
            )
        ), 200, array('Content-Type', 'application/json'));
    }

    /**
     * @Route("/list-fields", name="portfolio_list_fields")
     */
    public function listFieldsAction(RequestStack $requestStack)
    {
        $em = $this->container->get('doctrine')->getManager();
        $table = $requestStack->getCurrentRequest()->get('table', '');

        $query = $em->createQuery(
            'SELECT mu.column_ref
            FROM BigfootMediaBundle:MediaUsage mu
            WHERE mu.tableRef = :table'
        )->setParameter('table', $table);
        $columns = $query->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        $columnChoices = array();
        foreach ($columns as $column) {
            $columnChoices[$column] = $column;
        }

        return new Response(json_encode($columnChoices), 200, array('Content-Type', 'application/json'));
    }

    /**
     * @Route("/ck/upload", name="bigfoot_media_upload", options={"expose"=true})
     */
    public function ckUploadAction(RequestStack $requestStack)
    {
        $content = '';
        /** @var UploadedFile $file */
        if ($file = $requestStack->getCurrentRequest()->files->get('upload', false)) {
            try {
                $fileName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $size = $file->getSize();
                $absPath = sprintf('%s/%s', rtrim($this->getUploadDir(), '/'), $requestStack->getCurrentRequest()->get('CKEditor'));
                $relPath = sprintf('%s/%s', rtrim($this->getUploadDir(false), '/'), $requestStack->getCurrentRequest()->get('CKEditor'));
                $file->move($absPath, $fileName);
                $content = sprintf("window.parent.CKEDITOR.tools.callFunction(%s, '%s', '%s')",
                    $requestStack->getCurrentRequest()->get('CKEditorFuncNum'),
                    sprintf('%s/%s', $relPath, $fileName),
                    ''
                );

                $media = new Media();
                $media->setFile(sprintf('%s/%s', $relPath, $fileName));
                $media->setType($mimeType);

                $em = $this->getDoctrine()->getManager();

                $em->persist($media);
                $em->flush();

                /** @var MediaRepository $mediaRepository */
                $mediaRepository = $em->getRepository('BigfootMediaBundle:Media');

                list($width, $height) = getimagesize(sprintf('%s/%s', rtrim($absPath, '/'), $fileName));
                $mediaRepository->setMetadata($media, 'title', $fileName);
                $mediaRepository->setMetadata($media, 'width', $width);
                $mediaRepository->setMetadata($media, 'height', $height);
                $mediaRepository->setMetadata($media, 'size', $media->convertFileSize($size));

                $em->flush();
            } catch (\Exception $e) {
                $content = sprintf('alert(\'%s\')', $e->getMessage());
            }
        }

        return new Response(sprintf('<script>%s</script>', $content));
    }

    /**
     * @return string
     */
    private function getUploadDir($absolute = true)
    {
        $dir = '';

        if ($absolute) {
            $dir .= $this->get('kernel')->getRootDir() . '/../web';
        }

        return rtrim($dir, '/').sprintf('/%s/%s', trim($this->container->getParameter('bigfoot.core.upload_dir'), '/'), trim($this->container->getParameter('bigfoot.media.portfolio_dir'), '/'));
    }
}
