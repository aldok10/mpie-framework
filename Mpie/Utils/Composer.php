<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Utils;

use Composer\Autoload\ClassLoader;
use Exception;

class Composer
{
    /**
     * @throws Exception
     */
    public static function getClassLoader(): ClassLoader
    {
        foreach (spl_autoload_functions() as $autoloadFunction) {
            if (is_array($autoloadFunction) && ($loader = $autoloadFunction[0]) instanceof ClassLoader) {
                return $loader;
            }
        }
        throw new Exception('Cannot find any composer class loader');
    }
}
