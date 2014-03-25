<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommonController
 *
 * @package Mautic\CoreBundle\Controller
 */
class CommonController extends Controller implements EventsController {
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxAction(Request $request, array $parameters = array(), $contentTemplate = "Default:index.html.php") {
        $bundle   = $request->get("bundle");

        //Ajax call so respond with json
        $newContent  = $this->renderView("Mautic{$bundle}Bundle:$contentTemplate", $parameters);
        $breadcrumbs = $this->renderView("MauticCoreBundle:Default:breadcrumbs.html.php", $parameters);
        $response = new JsonResponse();
        $response->setData(array(
            'newContent'  => $newContent,
            'breadcrumbs' => $breadcrumbs
        ));

        return $response;
    }
}