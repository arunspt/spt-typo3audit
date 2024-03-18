<?php
namespace SPT\SptTypo3audit\ViewHelpers;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class StrReplaceViewHelper extends AbstractViewHelper 
{
    use CompileWithRenderStatic;
    /**
     * Initialize arguments
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(){
    	$this->registerArgument('string', 'string', 'The string');
    	$this->registerArgument('searchFor', 'string', 'The searchFor');
    	$this->registerArgument('replaceWith', 'string', 'The replaceWith');
    	parent::initializeArguments();
    }

    /**
	 * Replace the $searchFor string with $replaceString in $string
	 *
	 * @param $string string
	 * @param $searchFor string
	 * @param $replaceWith string
	 * @return string
	 */
	public function render() {
		$string = $this->arguments['string'] ?? $this->renderChildren();
		$searchFor = $this->arguments['searchFor'] ?? $this->renderChildren();
		$replaceWith = $this->arguments['replaceWith'] ?? $this->renderChildren();
		return str_replace($searchFor, $replaceWith, $string);

	}
}