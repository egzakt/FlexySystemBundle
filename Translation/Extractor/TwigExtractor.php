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
use Symfony\Bridge\Twig\Translation\TwigExtractor as BaseTwigExtractor;

/**
 * TwigExtractor extracts translation messages from a twig template.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TwigExtractor extends BaseTwigExtractor
{

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        // load any existing translation files
        $finder = new Finder();
        $files = $finder->files()->name('*.twig')->in($directory);
        foreach ($files as $file) {
            // Ignore Backend Templates
            if (strpos($file->getPathname(), '/Backend/') === false) {
                $this->extractTemplate(file_get_contents($file->getPathname()), $catalogue);
            }
        }
    }

}
