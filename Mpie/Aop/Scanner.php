<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop;

use Attribute;
use Exception;
use Mpie\Aop\Collector\AspectCollector;
use Mpie\Aop\Collector\PropertyAnnotationCollector;
use Mpie\Di\Reflection;
use Mpie\Utils\Composer;
use Mpie\Utils\Filesystem;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Throwable;

final class Scanner
{
    private AstManager  $astManager;

    private string      $proxyMap;

    private string      $runtimeDir;

    private array       $classMap    = [];

    private Filesystem  $filesystem;

    private static bool $initialized = false;

    private static self $scanner;

    /**
     * @throws Exception
     */
    private function __construct(
        private array $scanDirs,
        private array $collectors,
        string $runtimeDir,
        private bool $cache = false,
    ) {
        $this->filesystem = new Filesystem();
        $this->astManager = new AstManager();
        $this->runtimeDir = rtrim($runtimeDir, '/') . '/aop/';
        $this->filesystem->isDirectory($this->runtimeDir) || $this->filesystem->makeDirectory($this->runtimeDir, 0755, true);
        $this->classMap = $this->findClasses($this->scanDirs);
        $this->proxyMap = $this->runtimeDir . 'proxy.php';
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public static function init(ScannerConfig $config): void
    {
        if (! self::$initialized) {
            self::$scanner     = new self($config->getScanDirs(), $config->getCollectors(), $config->getRuntimeDir(), $config->isCache());
            self::$initialized = true;
            self::$scanner->boot();
        }
    }

    /**
     * @throws Exception
     */
    public static function instance(): self
    {
        if (self::$initialized) {
            return self::$scanner;
        }
        throw new Exception('Scanner is not initialized');
    }

    public function findClasses(array $dirs): array
    {
        $files   = Finder::create()->in($dirs)->name('*.php')->files();
        $classes = [];
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            foreach ($this->astManager->getClassesByRealPath($realPath) as $class) {
                $classes[$class] = $realPath;
            }
        }
        return $classes;
    }

    public function addClass(string $class, string $path): void
    {
        $this->classMap[$class] = $path;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function boot(): void
    {
        if (! $this->cache || ! $this->filesystem->exists($this->proxyMap)) {
            $this->filesystem->exists($this->proxyMap) && $this->filesystem->delete($this->proxyMap);
            if (($pid = pcntl_fork()) == -1) {
                throw new Exception('Process fork failed.');
            }
            pcntl_wait($pid);
        }
        Composer::getClassLoader()->addClassMap($this->getProxyMap($this->collectors));
        $this->collect($this->collectors);
        unset($this->filesystem, $this->astManager);
    }

    /**
     * @throws ReflectionException
     */
    private function getProxyMap(array $collectors): array
    {
        if (! $this->filesystem->exists($this->proxyMap)) {
            $proxyDir = $this->runtimeDir . 'proxy/';
            $this->filesystem->exists($proxyDir) || $this->filesystem->makeDirectory($proxyDir, 0755, true, true);
            $this->filesystem->cleanDirectory($proxyDir);
            $this->collect($collectors);
            $collectedClasses = array_unique(array_merge(AspectCollector::getCollectedClasses(), PropertyAnnotationCollector::getCollectedClasses()));
            $scanMap          = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $this->filesystem->put($proxyPath, $this->generateProxyClass($class, $this->classMap[$class]));
                $scanMap[$class] = $proxyPath;
            }
            $this->filesystem->put($this->proxyMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            exit;
        }
        return include $this->proxyMap;
    }

    private function generateProxyClass(string $class, string $path): string
    {
        $ast       = $this->astManager->getNodes($path);
        $traverser = new NodeTraverser();
        $metadata  = new Metadata($class);
        if (in_array(PropertyAnnotationCollector::class, $this->collectors)) {
            $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
        }
        if (in_array(AspectCollector::class, $this->collectors)) {
            $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
        }
        $modifiedStmts = $traverser->traverse($ast);
        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrintFile($modifiedStmts);
    }

    /**
     * @throws ReflectionException
     */
    private function collect(array $collectors): void
    {
        foreach ($this->classMap as $class => $path) {
            $reflectionClass = Reflection::class($class);
            // Collection class annotations
            foreach ($reflectionClass->getAttributes() as $attribute) {
                $attributeInstance = $attribute->newInstance();
                if ($attributeInstance instanceof Attribute) {
                    continue;
                }
                try {
                    foreach ($collectors as $collector) {
                        $collector::collectClass($class, $attributeInstance);
                    }
                } catch (Throwable $e) {
                    echo '[NOTICE] ' . $class . ': ' . $e->getMessage() . PHP_EOL;
                }
            }
            // Collect property annotations
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                foreach ($reflectionProperty->getAttributes() as $attribute) {
                    try {
                        foreach ($collectors as $collector) {
                            $collector::collectProperty($class, $reflectionProperty->getName(), $attribute->newInstance());
                        }
                    } catch (Throwable $e) {
                        echo '[NOTICE] ' . $class . ': ' . $e->getMessage() . PHP_EOL;
                    }
                }
            }
            // Collection method annotations
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $method = $reflectionMethod->getName();
                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    try {
                        foreach ($collectors as $collector) {
                            $collector::collectMethod($class, $method, $attribute->newInstance());
                        }
                    } catch (Throwable $e) {
                        echo '[NOTICE] ' . $class . ': ' . $e->getMessage() . PHP_EOL;
                    }
                }
                // Annotation that collects the parameters of the method
                foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                    try {
                        foreach ($reflectionParameter->getAttributes() as $attribute) {
                            foreach ($collectors as $collector) {
                                $collector::collectorMethodParameter($class, $method, $reflectionParameter->getName(), $attribute->newInstance());
                            }
                        }
                    } catch (Throwable $e) {
                        echo '[NOTICE] ' . $class . ': ' . $e->getMessage() . PHP_EOL;
                    }
                }
            }
        }
    }
}
