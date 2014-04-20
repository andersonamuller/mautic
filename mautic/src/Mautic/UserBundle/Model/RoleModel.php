<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\UserBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use Mautic\RoleBundle\Event\RoleEvent;
use Mautic\UserBundle\Entity\Role;
use Mautic\UserBundle\UserEvents;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Class RoleModel
 * {@inheritdoc}
 * @package Mautic\CoreBundle\Model\FormModel
 */
class RoleModel extends FormModel
{

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->repository     = 'MauticUserBundle:Role';
        $this->permissionBase = 'user:roles';
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param array $overrides
     * @return int
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveEntity($entity, $overrides = array())
    {
        if (!$entity instanceof Role) {
            throw new NotFoundHttpException('Entity must be of class Role()');
        }
        $isNew            = ($entity->getId()) ? 0 : 1;
        $permissionNeeded = ($isNew) ? "create" : "editother";
        if (!$this->container->get('mautic.security')->isGranted('user:roles:'. $permissionNeeded)) {
            throw new AccessDeniedException($this->container->get('translator')->trans('mautic.core.accessdenied'));
        }

        if (!$isNew) {
            //delete all existing
            $this->em->getRepository('MauticUserBundle:Permission')->purgeRolePermissions($entity);
        }

        //build the new permissions
        $formPermissionData = $this->request->request->get('role[permissions]', null, true);
        //set permissions if applicable and if the user is not an admin
        $permissions = (!empty($formPermissionData) && !$this->request->request->get('role[isAdmin]', 0, true)) ?
            $this->container->get('mautic.security')->generatePermissions($formPermissionData) :
            array();

        foreach ($permissions as $permissionEntity) {
            $entity->addPermission($permissionEntity);
        }

        return parent::saveEntity($entity, $overrides);
    }

    /**
     * {@inheritdoc}
     *
     * @param      $entity
     * @param null $action
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, $action = null)
    {
        if (!$entity instanceof Role) {
            throw new NotFoundHttpException('Entity must be of class Role()');
        }

        $params = (!empty($action)) ? array('action' => $action) : array();
        return $this->container->get('form.factory')->create('role', $entity, $params);
    }


    /**
     * Get a specific entity or generate a new one if id is empty
     *
     * @param $id
     * @return null|object
     */
    public function getEntity($id = '')
    {
        if (empty($id)) {
            return new Role();
        }

        return parent::getEntity($id);
    }


    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $entity
     * @param $isNew
     * @throws \Symfony\Component\HttpKernel\NotFoundHttpException
     */
    protected function dispatchEvent($action, $entity, $isNew = false)
    {
        if (!$entity instanceof Role) {
            throw new NotFoundHttpException('Entity must be of class Role()');
        }

        $dispatcher = new EventDispatcher();
        $event      = new RoleEvent($entity, $isNew);

        switch ($action) {
            case "save":
                $dispatcher->dispatch(UserEvents::ROLE_SAVE, $event);
                break;
            case "delete":
                $dispatcher->dispatch(UserEvents::ROLE_DELETE, $event);
                break;
        }
    }
}