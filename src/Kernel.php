<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Bref\SymfonyBridge\BrefKernel;

class Kernel extends BrefKernel
{
    use MicroKernelTrait;
}
