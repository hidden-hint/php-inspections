<?php

declare(strict_types=1);

namespace HiddenHint\PhpInspections\PHPStan\Rules;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\TrinaryLogic;

use function array_filter;

final class InheritedSignatureDetector
{
    public function isInherited(ClassMethod $method, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();

        return null !== $classReflection
            && (
                $this->isDeclaredByAncestor($classReflection, $method->name->toString())
                || $this->isImplementingAbstractTraitMethod($classReflection, $method)
            );
    }

    private function isDeclaredByAncestor(ClassReflection $classReflection, string $methodName): bool
    {
        return [] !== array_filter(
            [...$classReflection->getParents(), ...$classReflection->getInterfaces()],
            static fn(ClassReflection $ancestor): bool => $ancestor->hasNativeMethod($methodName),
        );
    }

    private function isImplementingAbstractTraitMethod(ClassReflection $classReflection, ClassMethod $method): bool
    {
        return !$method->isAbstract() && [] !== array_filter(
            $classReflection->getTraits(true),
            fn(ClassReflection $trait): bool => $this->declaresAbstractMethod($trait, $method->name->toString()),
        );
    }

    private function declaresAbstractMethod(ClassReflection $trait, string $methodName): bool
    {
        return $trait->hasNativeMethod($methodName)
            && $this->isAbstract($trait->getNativeMethod($methodName)->isAbstract());
    }

    private function isAbstract(TrinaryLogic|bool $abstract): bool
    {
        return $abstract instanceof TrinaryLogic ? $abstract->yes() : $abstract;
    }
}
