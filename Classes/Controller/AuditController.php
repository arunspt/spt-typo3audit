<?php
namespace SPT\SptTypo3audit\Controller;

use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility as Localize;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository as Repository;
use \SPT\SptTypo3audit\Domain\Repository\AuditRepository as AuditRepository;
use Mpdf;
use Dompdf\Dompdf; 

require_once $_SERVER['DOCUMENT_ROOT'].'/typo3conf/ext/spt_typo3audit/Classes/Library/dompdf/autoload.inc.php'; 
// include PATH_typo3conf.'ext/spt_typo3audit/Classes/Library/dompdf/autoload.inc.php'; 

/***
 *
 * This file is part of the "TYPO3 Audit" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Arun <arun@spawoz.com>, Spawoz
 *
 ***/

/**
 * AuditController
 */
class AuditController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Inject the auditRepository
     *
     * @param \SPT\SptTypo3audit\Domain\Repository\AuditRepository $auditRepository
     */
    public function injectAuditRepository(\SPT\SptTypo3audit\Domain\Repository\AuditRepository $auditRepository)
    {
       $this->auditRepository = $auditRepository;
    }
    
    
    /**
     * action list
     *To display all the details
     * @return void
     */
    public function listAction()
    {
        $arguments = $this->request->getArguments();
        $baseUrlToBackend = $this->request->getBaseUri();
        $baseUrl = str_replace('typo3/','',$baseUrlToBackend);
        $php = PHP_VERSION;
        $sysDetail = array();
        $sysDetail['PHP_Version']=substr(phpversion(), 0, 6);
        $sysDetail['Site_Name']=$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
        $sysDetail['Typo3_Version']=VersionNumberUtility::getNumericTypo3Version();
        //retrieve total pages count
        $pages = $this->auditRepository->getPagesCount();
        // retrieve total domain count
        if($sysDetail['Typo3_Version'] >= 10){
            $sysDomain = 'tx_spttypo3audit_domain_model_audit';
        } else {
            $sysDomain = 'sys_domain';
        }
        $includedFiles = $this->includeFiles();
        $sys_domain = $this->auditRepository->getDomainsCount($sysDomain);        
        // retrieve lang count
        $language = $this->auditRepository->getLanguageCount();
        $sysDetail['Total_Pages']= $pages;
        $sysDetail['Total_Domain']= $sys_domain;
        $sysDetail['Total_Lang']= $language;
        $typo3ver = strstr(VersionNumberUtility::getNumericTypo3Version(), ".",true );
        $typo3Config=$this->getVersioning();
        $extensionsList = $this->getAllExtensions($sysDetail['Typo3_Version']);
        $serverDetails = [];
        foreach($typo3Config[$typo3ver.".x"] as $key => $conf) { 
            $conf['tempCur'] = trim($conf['current'],"M"); 
            $conf['tempReq'] = trim($conf['required'],"M"); 
            $serverDetails[$key] = $conf; 
        }
        $arrDetails = array("sysDetail" => $sysDetail, 
                            "extensions" => $extensionsList, 
                            "serverDetails" => $serverDetails
                        );
        $currentYear = date('Y',time());
        $this->view->assignMultiple([
                                'arrDetails' => $arrDetails,
                                'files' => $includedFiles,
                                'currentYear' => $currentYear,
                                'baseUrl'   => $baseUrl
        ]);
        if ($arguments['saveAsPdf'] == 1) {
            //When the "save as pdf" button is clicked     
            $pdfTemplatePath = GeneralUtility::getFileAbsFileName('EXT:spt_typo3audit/Resources/Private/Backend/Templates/Audit/PdfTemplate.html');      
            $pdfView = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
            $pdfView->setTemplatePathAndFilename($pdfTemplatePath);
            $documentRoot = $_SERVER["DOCUMENT_ROOT"];
            $pdfView->assignMultiple([
                'arrDetails' => $arrDetails,
                'files' => $includedFiles,
                'currentYear' => $currentYear,
                'baseUrl'   => $baseUrl,
                'documentRoot' => $documentRoot
            ]);
            $parseTemplate =  $pdfView->render();
            $dompdf = new Dompdf();
            $dompdf->loadHtml($parseTemplate);
            $dompdf->render();
            $dompdf->stream('Typo3_Audit_Report');
            exit();
        }
    }

    //To include files
    public function includeFiles()
    {  
        $baseUrlToBackend = $this->request->getBaseUri();
        $baseUrl = str_replace('typo3/','',$baseUrlToBackend);
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $extPath = PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath($this->request->getControllerExtensionKey()));
        $prefixPath = $baseUrl.$extPath;
        $styleCss = $prefixPath.'Resources/Public/Css/style.css';
        $auditJs = $prefixPath.'Resources/Public/Js/audit.js';
        $files = [
                    'styleCss' => $styleCss,
                    'auditJs' => $auditJs
                ];
        return $files;
    }

    //To get the current and required values of different parameters of the server
    public function getVersioning(){
        $output = shell_exec('mysql -V'); 
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $MySQLVersion); 
        
        $arr= array(
            '4.x' => array(
                'PHP' =>array(
                    'required'=>'5.2-5.5',
                    'current'=>substr(phpversion(), 0, 6)
                ) ,
                'MySQL' => array(
                    'required'=>'5.0-5.5',
                    'current'=>$MySQLVersion[0],
                ),
                'Image_Magick' => array(
                    'required'=>'-',
                    'current'=>substr($imgmagic[0], 21, 5)
                ),
                'Maximum_Execution_Time' => array(
                    'required'=>'240',
                    'current'=>ini_get('max_execution_time')
                ),

                'Memory_Limit' => array(
                    'required'=>'128M',
                    'current'=>ini_get('memory_limit')
                ),
                'Max_Input_Vars' => array(
                    'required'=>'1500',
                    'current'=>ini_get('max_input_vars') 
                ),
                'Upload_Max_Size' => array(
                    'required'=>'200M',
                    'current'=>ini_get('upload_max_filesize')
                ),
                'Post_Max_Size' =>array( 
                    'required'=>'800M',
                    'current'=>ini_get('post_max_size')
                )
            ),
            '6.x' => array(
                'php' =>array(
                    'required'=>'5.3',
                    'current'=>substr(phpversion(), 0, 6)
                ) ,
                'MySQL' => array(
                    'required'=>'5.1-5.6',
                    'current'=>$MySQLVersion[0],
                ),
                'Image_Magick' => array(
                    'required'=>'-',
                    'current'=>substr($imgmagic[0], 21, 5)
                ),
                'Max_Execution_Time' => array(
                    'required'=>'240',
                    'current'=>ini_get('max_execution_time')
                ),

                'Memory_Limit' => array(
                    'required'=>'128M',
                    'current'=>ini_get('memory_limit')
                ),
                'Max_Input_Vars' => array(
                    'required'=>'1500',
                    'current'=>ini_get('max_input_vars') 
                ),
                'Upload_Max_Size' => array(
                    'required'=>'200M',
                    'current'=>ini_get('upload_max_filesize')
                ),
                'Post_Max_Size' =>array( 
                    'required'=>'800M',
                    'current'=>ini_get('post_max_size')
                )
            ),
            '7.x' => array(
                'php' =>array(
                    'required'=>'5.5',
                    'current'=>substr(phpversion(), 0, 6)
                ) ,
                'MySQL' => array(
                    'required'=>'5.5-5.7',
                    'current'=>$MySQLVersion[0],
                ),
                'Image_Magick' => array(
                    'required'=>'6',
                    'current'=>substr($imgmagic[0], 21, 5)
                ),
                'Max_Execution_Time' => array(
                    'required'=>'240',
                    'current'=>ini_get('max_execution_time')
                ),

                'Memory_Limit' => array(
                    'required'=>'128M',
                    'current'=>ini_get('memory_limit')
                ),
                'Max_Input_Vars' => array(
                    'required'=>'1500',
                    'current'=>ini_get('max_input_vars') 
                ),
                'Upload_Max_Size' => array(
                    'required'=>'200M',
                    'current'=>ini_get('upload_max_filesize')
                ),
                'Post_Max_Size' =>array( 
                    'required'=>'800M',
                    'current'=>ini_get('post_max_size')
                )
            ),
            '8.x' => array(
                'PHP' =>array(
                    'required'=>'7',
                    'current'=>substr(phpversion(), 0, 6)
                ) ,
                'MySQL' => array(
                    'required'=>'5.0-5.7',
                    'current'=>$MySQLVersion[0],
                ),
                'Image_Magick' => array(
                    'required'=>'6',
                    'current'=> '1232'
                ),
                'Max_Execution_Time' => array(
                    'required'=>'240',
                    'current'=>ini_get('max_execution_time')
                ),

                'Memory_Limit' => array(
                    'required'=>'128M',
                    'current'=>ini_get('memory_limit')
                ),
                'Max_Input_Vars' => array(
                    'required'=>'1500',
                    'current'=>ini_get('max_input_vars') 
                ),
                'Upload_Max_Size' => array(
                    'required'=>'200M',
                    'current'=>ini_get('upload_max_filesize')
                ),
                'Post_Max_Size' =>array( 
                    'required'=>'800M',
                    'current'=>ini_get('post_max_size')
                )
            ),
            '9.x' => array(
                'PHP' =>array(
                    'required'=>'7.2',
                    'current'=>substr(phpversion(), 0, 6)
                ) ,
                'MySQL' => array(
                    'required'=>'5.0-5.7',
                    'current'=>$MySQLVersion[0],
                ),
                'Image_Magick' => array(
                    'required'=>'6',
                    'current'=>' 322'
                ),
                'Max_Execution_Time' => array(
                    'required'=>'240',
                    'current'=>ini_get('max_execution_time')
                ),

                'Memory_Limit' => array(
                    'required'=>'128M',
                    'current'=>ini_get('memory_limit')
                ),
                'Max_Input_Vars' => array(
                    'required'=>'1500',
                    'current'=>ini_get('max_input_vars') 
                ),
                'Upload_Max_Size' => array(
                    'required'=>'200M',
                    'current'=>ini_get('upload_max_filesize')
                ),
                'Post_Max_Size' =>array( 
                    'required'=>'800M',
                    'current'=>ini_get('post_max_size')
                )
            ),'10.x' => array(
                'php' =>array(
                    'required'=>'7.2',
                    'current'=>substr(phpversion(), 0, 6)
                ) ,
                'MySQL' => array(
                    'required'=>'5.0-5.7',
                    'current'=>$MySQLVersion[0],
                ),
                'Image_Magick' => array(
                    'required'=>'6',
                    'current'=>' 322'
                ),
                'Max_Execution_Time' => array(
                    'required'=>'240',
                    'current'=>ini_get('max_execution_time')
                ),

                'Memory_Limit' => array(
                    'required'=>'128M',
                    'current'=>ini_get('memory_limit')
                ),
                'Max_Input_Vars' => array(
                    'required'=>'1500',
                    'current'=>ini_get('max_input_vars') 
                ),
                'Upload_Max_Size' => array(
                    'required'=>'200M',
                    'current'=>ini_get('upload_max_filesize')
                ),
                'Post_Max_Size' =>array( 
                    'required'=>'800M',
                    'current'=>ini_get('post_max_size')
                )
            ),'11.x' => array(
                'php' =>array(
                    'required'=>'7.4',
                    'current'=>substr(phpversion(), 0, 6)
                ) ,
                'MySQL' => array(
                    'required'=>'5.7-6.7',
                    'current'=>$MySQLVersion[0],
                ),
                'Image_Magick' => array(
                    'required'=>'6',
                    'current'=>' 322'
                ),
                'Max_Execution_Time' => array(
                    'required'=>'240',
                    'current'=>ini_get('max_execution_time')
                ),

                'Memory_Limit' => array(
                    'required'=>'128M',
                    'current'=>ini_get('memory_limit')
                ),
                'Max_Input_Vars' => array(
                    'required'=>'1500',
                    'current'=>ini_get('max_input_vars') 
                ),
                'Upload_Max_Size' => array(
                    'required'=>'200M',
                    'current'=>ini_get('upload_max_filesize')
                ),
                'Post_Max_Size' =>array( 
                    'required'=>'800M',
                    'current'=>ini_get('post_max_size')
                )
            ),
        );

        return $arr;
    }

    //To get the details of all extensions
    public function getAllExtensions($myTargetVersion)
    { 
        $i=1;
        $totalCompatible6=0;
        $totalCompatible7=0;
        $totalCompatible8=0;
        $totalInstalled=0;
        $totalNonInstalled=0;
        $assignArray = array();
        $extensionlist = array();
        $overviewReport = array();
        //Get extension list
        $myExtList = $this->objectManager->get(ListUtility::class);
        $allExtensions = $myExtList->getAvailableAndInstalledExtensionsWithAdditionalInformation();
        foreach ($allExtensions as $extensionKey => $spExt) {            
            //Filter all local extension for whole TER data start            
            if (strtolower($spExt['type']) == 'local' && $spExt['key']!='spt_typo3audit') {
                $spExt['versions']['minVersion'] = strstr($spExt['constraints']['depends']['typo3'], '-', true);
                $spExt['versions']['maxVersion'] = substr($spExt['constraints']['depends']['typo3'], strpos($spExt['constraints']['depends']['typo3'], "-") + 1);                
                if (version_compare($myTargetVersion,$spExt['versions']['maxVersion'],'>') || version_compare($myTargetVersion,$spExt['versions']['minVersion'],'<')) {
                    $totalCompatible8 = $totalCompatible8 + 1;
                    $compatibility = false;
                }else{
                    $compatibility = true;
                }
                $assignArray[] = array ("title" => $spExt['title'], "compatibility" => $compatibility, "TER" => $spExt['terObject'] , "Version" => $spExt['version'] );
                if($spExt['installed']){
                    $totalInstalled = $totalInstalled + 1;
                } else {
                    $totalNonInstalled = $totalNonInstalled + 1;
                }
            }
        }
        $overviewReport["extenionList"] = $assignArray;
        $overviewReport["totalInstalled"] = $totalInstalled;
        $overviewReport["totalNot"] = $totalNonInstalled;
        $overviewReport["totalNotCompatible"] = $totalCompatible8;
        return $overviewReport;
    }
}
