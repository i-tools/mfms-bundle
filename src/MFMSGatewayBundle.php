<?php
/**
 * Created by PhpStorm.
 * User: itools
 * Date: 03.04.19
 * Time: 13:07
 */

namespace itools\MFMSGatewayBundle;

use itools\MFMSGatewayBundle\DependencyInjection\MFMSGatewayExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MFMSGatewayBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new MFMSGatewayExtension();
        }
        return $this->extension;
    }
}