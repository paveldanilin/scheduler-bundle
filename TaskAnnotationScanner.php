<?php

namespace Pada\SchedulerBundle;

use Pada\Reflection\Scanner\ClassInfo;
use Pada\Reflection\Scanner\ScannerInterface;
use Pada\SchedulerBundle\Annotation\Scheduled;

final class TaskAnnotationScanner implements TaskScannerInterface
{
    private ScannerInterface $metaScanner;
    /** @var array<string>  */
    private array $scanDir;
    /** @var array<string>  */
    private array $excludeDir;

    public function __construct(ScannerInterface $metaScanner, array $scanDir)
    {
        $this->metaScanner = $metaScanner;
        $this->scanDir = $scanDir;
        $this->excludeDir = ['vendor', 'var'];
    }

    /**
     * @param array<string> $scanDir
     * @return void
     */
    public function setScanDir(array $scanDir): void
    {
        $this->scanDir = $scanDir;
    }

    /**
     * @return \Generator<Task>
     */
    public function next(): \Generator
    {
        foreach ($this->scanDir as $dir) {
            if (!$this->shouldScan($dir)) {
                continue;
            }
            /** @var ClassInfo $classInfo */
            foreach ($this->metaScanner->in($dir) as $classInfo) {
                /** @var Scheduled|null $classLevelAnnotation */
                $classLevelAnnotation = $this->getScheduledClassAnnotation($classInfo);
                $className = $classInfo->getReflection()->getName();
                if (null === $classLevelAnnotation) {
                    foreach ($this->getScheduledMethods($classInfo) as $scheduledMethod) {
                        [$methodName, $methodLevelAnnotation] = $scheduledMethod;
                        yield $this->createTask($className, $methodName, $methodLevelAnnotation);
                    }
                } else {
                    yield $this->createTask($className, '__invoke', $classLevelAnnotation);
                }
            }
        }
    }

    private function shouldScan(string $dir): bool
    {
        if (false === \is_dir($dir)) {
            return false;
        }
        $base = \basename($dir);
        if (\in_array($base, $this->excludeDir, true)) {
            return false;
        }
        return true;
    }

    private function createTask(string $className, string $methodName, Scheduled $scheduled): Task
    {
        return new Task(
            $className,
            $methodName,
            $scheduled->getCron(),
            $scheduled->getTimeout(),
            $scheduled->getErrorHandler(),
            $scheduled->getErrorThreshold(),
            $scheduled->getDelayTimeout(),
        );
    }

    private function getScheduledClassAnnotation(ClassInfo $classInfo): ?Scheduled
    {
        foreach ($classInfo->getClassAnnotations() as $annotation) {
            if ($annotation instanceof Scheduled) {
                return $annotation;
            }
        }
        return null;
    }

    /**
     * @param ClassInfo $classInfo
     * @return \Generator<array>
     */
    private function getScheduledMethods(ClassInfo $classInfo): \Generator
    {
        foreach ($classInfo->getMethodNames() as $methodName) {
            foreach ($classInfo->getMethodAnnotations($methodName) as $annotation) {
                if ($annotation instanceof Scheduled) {
                    yield [$methodName, $annotation];
                }
            }
        }
    }
}
