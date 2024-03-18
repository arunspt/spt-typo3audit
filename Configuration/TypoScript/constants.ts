
module.tx_spttypo3audit_spttypo3audit {
    view {
        # cat=module.tx_spttypo3audit_spttypo3audit/file; type=string; label=Path to template root (BE)
        templateRootPath = EXT:spt_typo3audit/Resources/Private/Backend/Templates/
        # cat=module.tx_spttypo3audit_spttypo3audit/file; type=string; label=Path to template partials (BE)
        partialRootPath = EXT:spt_typo3audit/Resources/Private/Backend/Partials/
        # cat=module.tx_spttypo3audit_spttypo3audit/file; type=string; label=Path to template layouts (BE)
        layoutRootPath = EXT:spt_typo3audit/Resources/Private/Backend/Layouts/
    }
    persistence {
        # cat=module.tx_spttypo3audit_spttypo3audit//a; type=string; label=Default storage PID
        storagePid =
    }
}
