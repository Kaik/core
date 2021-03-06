<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Helper;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\ExtensionEvents;

class ExtensionStateHelper
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ExtensionApi
     */
    private $extensionApi;

    /**
     * ExtensionStateHelper constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param CacheClearer $cacheClearer
     * @param ExtensionRepository $extensionRepository
     * @param TranslatorInterface $translator
     * @param ExtensionApi $extensionApi
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheClearer $cacheClearer,
        ExtensionRepository $extensionRepository,
        TranslatorInterface $translator,
        ExtensionApi $extensionApi
    ) {
        $this->dispatcher = $dispatcher;
        $this->cacheClearer = $cacheClearer;
        $this->extensionRepository = $extensionRepository;
        $this->translator = $translator;
        $this->extensionApi = $extensionApi;
    }

    /**
     * Set the state of a module.
     *
     * @param integer $id
     * @param integer $state
     *
     * @return boolean True if successful, false otherwise
     */
    public function updateState($id, $state)
    {
        /** @var ExtensionEntity $extension */
        $extension = $this->extensionRepository->find($id);
        $this->dispatcher->dispatch(ExtensionEvents::UPDATE_STATE, new GenericEvent($extension, ['state' => $state]));

        // Check valid state transition
        switch ($state) {
            case Constant::STATE_INACTIVE:
                $eventName = CoreEvents::MODULE_DISABLE;
                break;
            case Constant::STATE_ACTIVE:
                if (Constant::STATE_INACTIVE === $extension->getState()) {
                    // ACTIVE is used for freshly installed modules, so only register the transition if previously inactive.
                    $eventName = CoreEvents::MODULE_ENABLE;
                }
                break;
            case Constant::STATE_UPGRADED:
                if (Constant::STATE_UNINITIALISED == $extension->getState()) {
                    throw new \RuntimeException($this->translator->__('Error! Invalid module state transition.'));
                }
                break;
        }

        // change state
        $extension->setState($state);
        $this->extensionRepository->persistAndFlush($extension);

        // clear the cache before calling events
        $this->cacheClearer->clear('symfony');

        if (isset($eventName)) {
            $moduleBundle = $this->extensionApi->getModuleInstanceOrNull($extension->getName());
            $event = new ModuleStateEvent($moduleBundle, $extension->toArray());
            $this->dispatcher->dispatch($eventName, $event);
        }

        return true;
    }
}
