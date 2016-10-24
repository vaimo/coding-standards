<?php
/**
 * Copyright � 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MEQP2\Sniffs\Legacy;

use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_File;

/**
 * Class MageEntitySniff
 * Detects typical Magento 1 classes constructions.
 */
class MageEntitySniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Violation severity.
     *
     * @var int
     */
    protected $severity = 10;

    /**
     * String representation of error.
     *
     * @var string
     */
    protected $errorMessage = 'Possible Magento 2 design violation. Detected typical Magento 1 construction "%s".';

    /**
     * Error violation code.
     *
     * @var string
     */
    protected $errorCode = 'FoundLegacyEntity';

    /**
     * Legacy entity from Magento 1.
     *
     * @var string
     */
    protected $legacyEntity = 'Mage';

    /**
     * Legacy prefixes from Magento 1.
     *
     * @var array
     */
    protected $legacyPrefixes = [
        'Mage_',
        'Enterprise_'
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_DOUBLE_COLON,
            T_NEW
        ];
    }

    /**
     * List of tokens for which we should find name before his position.
     *
     * @var array
     */
    protected $nameBefore = [
        T_DOUBLE_COLON
    ];

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (in_array($tokens[$stackPtr]['code'], $this->nameBefore)) {
            $oldPosition = $stackPtr;
            $stackPtr = $phpcsFile->findPrevious(T_STRING, $stackPtr - 1, null, false, null, true);
            if ($stackPtr === false) {
                return;
            }
            $entityName = $tokens[$stackPtr]['content'];
            $error = [$entityName . $tokens[$oldPosition]['content']];
        } else {
            $oldPosition = $stackPtr;
            $stackPtr = $phpcsFile->findNext(T_STRING, $oldPosition + 1, null, false, null, true);
            if ($stackPtr === false) {
                return;
            }
            $entityName = $tokens[$stackPtr]['content'];
            $error = [$tokens[$oldPosition]['content'] . ' ' . $entityName];
        }
        if ($entityName === $this->legacyEntity ||
            $this->isPrefixLegacy($entityName)
        ) {
            $phpcsFile->addError(
                $this->errorMessage,
                $stackPtr,
                $this->errorCode,
                $error,
                $this->severity
            );
        }
    }

    /**
     * Method checks if passed string contains legacy prefix from Magento 1.
     *
     * @param string $entityName
     * @return bool
     */
    private function isPrefixLegacy($entityName)
    {
        foreach ($this->legacyPrefixes as $entity) {
            if (strpos($entityName, $entity) === 0) {
                return true;
            }
        }
        return false;
    }
}
