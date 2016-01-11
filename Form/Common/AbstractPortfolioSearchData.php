<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> Bigfoot Media Library Client project.
 * Sylvain Plançon <s.plancon@c2is.fr>
 */
namespace Bigfoot\Bundle\MediaBundle\Form\Common;

abstract class AbstractPortfolioSearchData
{
    /**
     * @return string
     */
    abstract public function getSearch();

    /**
     * @return string
     */
    abstract public function getSearchForSession();
}