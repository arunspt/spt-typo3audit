
# Module configuration
module.tx_spttypo3audit_tools_spttypo3auditspttypo3audit {
    persistence {
        storagePid = {$module.tx_spttypo3audit_spttypo3audit.persistence.storagePid}
    }
    view {
        templateRootPaths.0 = EXT:spt_typo3audit/Resources/Private/Backend/Templates/
        templateRootPaths.1 = {$module.tx_spttypo3audit_spttypo3audit.view.templateRootPath}
        partialRootPaths.0 = EXT:spt_typo3audit/Resources/Private/Backend/Partials/
        partialRootPaths.1 = {$module.tx_spttypo3audit_spttypo3audit.view.partialRootPath}
        layoutRootPaths.0 = EXT:spt_typo3audit/Resources/Private/Backend/Layouts/
        layoutRootPaths.1 = {$module.tx_spttypo3audit_spttypo3audit.view.layoutRootPath}
    }
    
}