<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'SPT.SptTypo3audit',
                'tools', // Make module a submodule of 'tools'
                'spttypo3audit', // Submodule key
                '', // Position
                [
                    SPT\SptTypo3audit\Controller\AuditController::class => 'list',
                ],
                [
                    'access' => 'user,group',
                    'icon'   => 'EXT:spt_typo3audit/ext_icon.gif',
                    'labels' => 'LLL:EXT:spt_typo3audit/Resources/Private/Language/locallang_spttypo3audit.xlf',
                    'navigationComponentId' => '',
                ]
            );

        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('spt_typo3audit', 'Configuration/TypoScript', 'TYPO3 Audit');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_spttypo3audit_domain_model_audit', 'EXT:spt_typo3audit/Resources/Private/Language/locallang_csh_tx_spttypo3audit_domain_model_audit.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_spttypo3audit_domain_model_audit');

    }
);
