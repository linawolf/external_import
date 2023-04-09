<?php

declare(strict_types=1);

namespace Cobweb\ExternalImport\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Cobweb\ExternalImport\Domain\Repository\LogRepository;
use Cobweb\ExternalImport\Utility\CompatibilityUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for the "Log" backend module
 */
class LogModuleController extends ActionController
{

    protected ModuleTemplateFactory $moduleTemplateFactory;

    protected ?ModuleTemplate $moduleTemplate = null;

    protected PageRenderer $pageRenderer;

    protected LogRepository $logRepository;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        PageRenderer $pageRenderer,
        LogRepository $logRepository
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRenderer = $pageRenderer;
        $this->logRepository = $logRepository;
    }

    public function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle(
            'External Import - ' .
            $this->getLanguageService()->sL('LLL:EXT:external_import/Resources/Private/Language/LogModule.xlf:mlang_tabs_tab')
        );
    }

    /**
     * Loads the resources (JS, CSS) needed by some action views.
     *
     * @return void
     */
    protected function loadResources(): void
    {
        $publicResourcesPath = PathUtility::getAbsoluteWebPath(
            ExtensionManagementUtility::extPath('external_import') . 'Resources/Public/'
        );
        $this->pageRenderer->addCssFile($publicResourcesPath . 'StyleSheet/ExternalImport.css');
        // TODO: remove when compatibility with v11 is dropped
        if (CompatibilityUtility::isV11()) {
            $this->pageRenderer->addCssFile($publicResourcesPath . 'StyleSheet/ExternalImport11.css');
        }
        $this->pageRenderer->addRequireJsConfiguration(
            [
                'paths' => [
                    'datatables' => $publicResourcesPath . 'JavaScript/Contrib/jquery.dataTables',
                ]
            ]
        );
        // TODO: remove and replace with Luxon when compatibility with v11 is dropped
        // Reference: https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.1/Important-88158-ReplacedMomentJsWithLuxon.html
        if (CompatibilityUtility::isV12()) {
            $this->pageRenderer->addRequireJsConfiguration(
                [
                    'paths' => [
                        'moment' => $publicResourcesPath . 'JavaScript/Contrib/moment'
                    ]
                ]
            );
        }
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/ExternalImport/LogModule');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:external_import/Resources/Private/Language/JavaScript.xlf');
    }

    /**
     * Displays the list of all available log entries.
     *
     * @return ResponseInterface
     */
    public function listAction(): ResponseInterface
    {
        $this->loadResources();

        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Returns the global language object.
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
