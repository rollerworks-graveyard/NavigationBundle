<?php

/*
 * This file is part of the RollerworksNavigationBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
