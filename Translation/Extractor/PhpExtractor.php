<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flexy\SystemBundle\Translation\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor as BasePhpExtractor;

/**
 * PhpExtractor extracts translation messages from a php template.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class PhpExtractor extends BasePhpExtractor
{

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalog)
    {
        // load any existing translation files
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->in($directory);
        foreach ($files as $file) {
            // Ignore Backend Templates
            if (strpos($file->getPathname(), '/Backend/') === false) {
                $this->parseTokens(token_get_all(file_get_contents($file)), $catalog);
            }
        }
    }

}
