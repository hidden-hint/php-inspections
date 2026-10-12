<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;

use function array_filter;

final class InheritedSignatureDetector
{
    public function isInherited(ClassMethod $method, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();

        return null !== $classReflection && $this->isDeclaredByAncestor($classReflection, $method->name->toString());
    }

    private function isDeclaredByAncestor(ClassReflection $classReflection, string $methodName): bool
    {
        return [] !== array_filter(
            [...$classReflection->getParents(), ...$classReflection->getInterfaces()],
            static fn(ClassReflection $ancestor): bool => $ancestor->hasNativeMethod($methodName),
        );
    }
}
