<?php

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class PagerExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'pager' => new \Twig_Function_Method($this, 'pager', array('is_safe' => array('html'))),
        );
    }

    /**
     * @TODO SIMPLIFY THIS AND REMOVE ALL LEGACY!
     * template pager plugin
     *
     *   {{ pager({rowcount:pager.numitems, limit:pager.itemsperpage, posvar:'startnum', route:'zikulapagesmodule_admin_index', template:'pager.html.twig'}) }}
     *
     * Available parameters:
     *  route              Name of a fixed route to use (required unless homepage)
     *  rowcount           Total number of items to page in between
     *                       (if an array is assigned, it's count will be used)
     *  limit              Number of items on a page (if <0 unlimited)
     *  posvar             Name of the variable that contains the position data, eg "offset"
     *  owner              If set uses it as the module owner of the Zikula_View instance. Default owner is the Theme module
     *  template           Optional name of a template file (default: 'pagercss.html.twig')
     *  includeStylesheet  Use predefined stylesheet file? Default is yes.
     *  anchorText         Optional text for hyperlink anchor (e.g. 'comments' for the anchor #comments) (default: '')
     *  maxpages           Optional maximum number of displayed pages, others will be hidden / suppressed
     *                       (default: 15 = show only 15 pages)
     *  display            Optional choice between 'page' or 'startnum'. Show links using page number or starting item number (default is startnum)
     *  class              Optional class to apply to the pager container (default : z-pager)
     *  processDetailLinks Should the single page links be processed? (default: false if using pagerimage.tpl, otherwise true)
     *  processUrls        Should urls be processed or assign the arguments? (default: true)
     *  optimize           Only deliver page links which are actually displayed to the template (default: true)
     *  includePostVars    Whether or not to include the POST variables as GET variables in the pager URLs (default: true)
     *
     * @param array $params All attributes passed to this function from the template.
     * @return string
     */
    public function pager($params)
    {
        /** @var Request $request */
        $request = $this->container->get('request');
        if (!isset($params['rowcount'])) {
            trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pager', 'rowcount')));
        }

        if (!isset($params['limit'])) {
            trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pager', 'limit')));
        }

        if (is_array($params['rowcount'])) {
            $params['rowcount'] = count($params['rowcount']);
        } elseif ($params['rowcount'] == 0) {
            return '';
        }

        if ($params['limit'] == 0) {
            $params['limit'] = 5;
        }

        if (!isset($params['display'])) {
            $params['display'] = 'startnum';
        }

        if (!isset($params['class'])) {
            $params['class'] = 'z-pager';
        }

        if (!isset($params['optimize'])) {
            $params['optimize'] = true;
        }

        if (!isset($params['owner'])) {
            $params['owner'] = false;
        }

        if (!isset($params['includePostVars'])) {
            $params['includePostVars'] = true;
        }

        if (!isset($params['route'])) {
            $params['route'] = false;
        }

        $pager = array();
        $pager['total']    = $params['rowcount'];
        $pager['perpage']  = $params['limit'];
        $pager['class']    = $params['class'];
        $pager['optimize'] = $params['optimize'];
        unset($params['rowcount']);
        unset($params['limit']);
        unset($params['class']);
        unset($params['optimize']);

        // current position
        $pager['posvar'] = (isset($params['posvar']) ? $params['posvar'] : 'pos');

        $routeParams = array();
        if ($request->attributes->has('_route_params')) {
            $routeParams = $request->attributes->get('_route_params');
            if (isset($routeParams[$pager['posvar']])) {
                $pager['pos'] = (int)($routeParams[$pager['posvar']]);
            } else {
                $pager['pos'] = (int)$request->query->get($pager['posvar'], '');
            }
        } else {
            $pager['pos'] = (int)$request->query->get($pager['posvar'], '');
        }
        if ($params['display'] == 'page') {
            $pager['pos'] = $pager['pos'] * $pager['perpage'];
            $pager['increment'] = 1;
        } else {
            $pager['increment'] = $pager['perpage'];
        }

        if ($pager['pos'] < 1) {
            $pager['pos'] = 1;
        }
        if ($pager['pos'] > $pager['total']) {
            $pager['pos'] = $pager['total'];
        }
        unset($params['posvar']);

        // number of pages
        $pager['countPages'] = (isset($pager['total']) && $pager['total'] > 0 ? ceil($pager['total'] / $pager['perpage']) : 1);
        if ($pager['countPages'] < 2) {
            return '';
        }

        // current page
        $pager['currentPage'] = ceil($pager['pos'] / $pager['perpage']);
        if ($pager['currentPage'] > $pager['countPages']) {
            $pager['currentPage'] = $pager['countPages'];
        }

        $templateName = (isset($params['template'])) ? $params['template'] : 'pagercss.html.twig';
        $pager['includeStylesheet'] = isset($params['includeStylesheet']) ? $params['includeStylesheet'] : true;
        $anchorText = (isset($params['anchorText']) ? '#' . $params['anchorText'] : '');
        $pager['maxPages'] = (isset($params['maxpages']) ? $params['maxpages'] : 15);
        unset($params['template']);
        unset($params['anchorText']);
        unset($params['maxpages']);

        $pager['args'] = array();

        //also $_POST vars have to be considered, i.e. for search results
        $allVars = ($params['includePostVars']) ? array_merge($_POST, $_GET, $routeParams) : array_merge($_GET, $routeParams);
        foreach ($allVars as $k => $v) {
            if ($k != $pager['posvar'] && !is_null($v)) {
                switch ($k) {
                    case 'module':
                        if (!isset($params['modname'])) {
                            $pager['module'] = $v;
                        }
                        break;
                    case 'func':
                        if (!isset($params['func'])) {
                            $pager['func'] = $v;
                        }
                        break;
                    case 'type':
                        if (!isset($params['type'])) {
                            $pager['type'] = $v;
                        }
                        break;
                    case 'lang':
                        $addcurrentlang2url = \System::getVar('languageurl');
                        if ($addcurrentlang2url == 0) {
                            $pager['args'][$k] =  $v;
                        }
                        break;
                    default:
                        if (is_array($v)) {
                            foreach ($v as $kk => $vv) {
                                if (is_array($vv)) {
                                    foreach ($vv as $kkk => $vvv) {
                                        if (is_array($vvv)) {
                                            foreach ($vvv as $kkkk => $vvvv) {
                                                if (strlen($vvvv)) {
                                                    $tkey = $k . '[' . $kk . '][' . $kkk . '][' . $kkkk . ']';
                                                    $pager['args'][$tkey] = $vvvv;
                                                }
                                            }
                                        } elseif (strlen($vvv)) {
                                            $tkey = $k . '[' . $kk . '][' . $kkk . ']';
                                            $pager['args'][$tkey] = $vvv;
                                        }
                                    }
                                } elseif (strlen($vv)) {
                                    $tkey = $k . '[' . $kk . ']';
                                    $pager['args'][$tkey] =  $vv;
                                }
                            }
                        } else {
                            if (strlen($v)) {
                                $pager['args'][$k] =  $v;
                            }
                        }
                }
            }
        }

        $pagerUrl = function ($pager) use ($params) {
            if (!$params['route']) {
                // only case where this should be true is if this is the homepage
                $startargs   = explode(',', \System::getVar('startargs'));
                foreach ($startargs as $arg) {
                    if (!empty($arg)) {
                        $argument = explode('=', $arg);
                        $pager['args'][$argument[0]] = $argument[1];
                    }
                }
                return \ModUtil::url(\System::getVar('startpage'), \System::getVar('starttype'), \System::getVar('startfunc'), $pager['args']);
            }
            return $this->container->get('router')->generate($params['route'], $pager['args']);
        };
        unset($params['route']);

        // build links to items / pages
        // entries are marked as current or displayed / hidden
        $pager['pages'] = array();
        if ($pager['maxPages'] > 0) {
            $pageInterval = floor($pager['maxPages'] / 2);

            $leftMargin = $pager['currentPage'] - $pageInterval;
            $rightMargin = $pager['currentPage'] + $pageInterval;

            if ($leftMargin < 1) {
                $rightMargin += abs($leftMargin) + 1;
                $leftMargin = 1;
            }
            if ($rightMargin > $pager['countPages']) {
                $leftMargin -= $rightMargin - $pager['countPages'];
                $rightMargin = $pager['countPages'];
            }
        }

        $params['processUrls']        = isset($params['processUrls']) ? (bool)$params['processUrls'] : true;
        $params['processDetailLinks'] = isset($params['processDetailLinks']) ? (bool)$params['processDetailLinks'] : ($templateName != 'pagerimage.html.twig');
        if ($params['processDetailLinks']) {
            for ($currItem = 1; $currItem <= $pager['countPages']; $currItem++) {
                $currItemVisible = true;

                if ($pager['maxPages'] > 0 &&
                    //(($currItem < $leftMargin && $currItem > 1) || ($currItem > $rightMargin && $currItem <= $pager['countPages']))) {
                    (($currItem < $leftMargin) || ($currItem > $rightMargin))) {

                    if ($pager['optimize']) {
                        continue;
                    } else {
                        $currItemVisible = false;
                    }
                }

                if ($params['display'] == 'page') {
                    $pager['args'][$pager['posvar']] = $currItem;
                } else {
                    $pager['args'][$pager['posvar']] = (($currItem - 1) * $pager['perpage']) + 1;
                }

                $pager['pages'][$currItem]['pagenr'] = $currItem;
                $pager['pages'][$currItem]['isCurrentPage'] = ($pager['pages'][$currItem]['pagenr'] == $pager['currentPage']);
                $pager['pages'][$currItem]['isVisible'] = $currItemVisible;

                if ($params['processUrls']) {
                    $pager['pages'][$currItem]['url'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
                } else {
                    $pager['pages'][$currItem]['url'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
                }
            }
            unset($pager['args'][$pager['posvar']]);
        }

        // link to first & prev page
        $pager['args'][$pager['posvar']] = $pager['first'] = '1';
        if ($params['processUrls']) {
            $pager['firstUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
        } else {
            $pager['firstUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
        }

        if ($params['display'] == 'page') {
            $pager['prev'] = ($pager['currentPage'] - 1);
        } else {
            $pager['prev'] = ($leftMargin - 1) * $pager['perpage'] - $pager['perpage'] + $pager['first'];
        }
        $pager['args'][$pager['posvar']] = ($pager['prev'] > 1) ? $pager['prev'] : 1;
        if ($params['processUrls']) {
            $pager['prevUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
        } else {
            $pager['prevUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
        }

        // link to next & last page
        if ($params['display'] == 'page') {
            $pager['next'] = $pager['currentPage'] + 1;
        } else {
            $pager['next'] = $rightMargin * $pager['perpage'] + 1;
        }
        $pager['args'][$pager['posvar']] = ($pager['next'] < $pager['total']) ? $pager['next'] : $pager['next'] - $pager['perpage'];
        if ($params['processUrls']) {
            $pager['nextUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
        } else {
            $pager['nextUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
        }

        if ($params['display'] == 'page') {
            $pager['last'] = $pager['countPages'];
        } else {
            $pager['last'] = $pager['countPages'] * $pager['perpage'] - $pager['perpage'] + 1;
        }
        $pager['args'][$pager['posvar']] = $pager['last'];
        if ($params['processUrls']) {
            $pager['lastUrl'] = \DataUtil::formatForDisplay($pagerUrl($pager) . $anchorText);
        } else {
            $pager['lastUrl'] = array('module' => $pager['module'], 'type' => $pager['type'], 'func' => $pager['func'], 'args' => $pager['args'], 'fragment' => $anchorText);
        }

        $pager['itemStart'] = ($pager['currentPage'] * $pager['perpage']) - $pager['perpage'] + 1;
        $pager['itemEnd'] = $pager['itemStart'] + $pager['perpage'] - 1;
        if ($pager['itemEnd'] > $pager['total']) {
            $pager['itemEnd'] = $pager['total'];
        }

        $templateParameters['pagerPluginArray'] = $pager;
        $templateParameters['hiddenPageBoxOpened'] = 0;
        $templateParameters['hiddenPageBoxClosed'] = 0;

        return $this->container->get('templating')->renderResponse('CoreBundle:Pager:' . $templateName, $templateParameters)->getContent();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulacore.pager';
    }
}
