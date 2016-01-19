<?php

namespace Bigfoot\Bundle\MediaBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Controller\CrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Metadata controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/admin/portfolio_metadata")
 */
class MetadataController extends CrudController
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'admin_portfolio_metadata';
    }

    /**
     * @return string
     */
    protected function getEntity()
    {
        return 'BigfootMediaBundle:Metadata';
    }

    protected function getFields()
    {
        return array(
            'id'       => array(
                'label' => 'ID',
            ),
            'name'     => array(
                'label' => 'Name',
            ),
        );
    }

    /**
     * Lists all Metadata entities.
     *
     * @Route("/", name="admin_portfolio_metadata")
     * @Method("GET")
     * @param RequestStack $requestStack
     * @return array
     */
    public function indexAction()
    {
        return $this->doIndex();
    }

    /**
     * Displays a form to create a new Metadata entity.
     *
     * @Route("/new", name="admin_portfolio_metadata_new")
     */
    public function newAction()
    {

        return $this->doNew();
    }

    /**
     * Displays a form to edit an existing Metadata entity.
     *
     * @Route("/{id}/edit", name="admin_portfolio_metadata_edit")
     */
    public function editAction($id)
    {
        return $this->doEdit($id);
    }

    /**
     * Deletes a Metadata entity.
     *
     * @Route("/{id}", name="admin_portfolio_metadata_delete")
     * @Method("GET|DELETE")
     */
    public function deleteAction($id)
    {
        return $this->doDelete($id);
    }
}
