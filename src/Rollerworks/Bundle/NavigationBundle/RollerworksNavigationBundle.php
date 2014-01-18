<?php

/*
 * This file is part of the RollerworksNavigationBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\NavigationBundle;

use Rollerworks\Bundle\NavigationBundle\DependencyInjection\NavigationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RollerworksNavigationBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new NavigationExtension();
        }

        return $this->extension;
    }
}
