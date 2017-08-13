<?php

namespace Phpactor\ClassMover\Domain;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;

class SourceCode
{
    private $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public static function fromString(string $source)
    {
        return new self($source);
    }

    public function addUseStatement(FullyQualifiedName $classToUse): SourceCode
    {
        $lines = explode(PHP_EOL, $this->source);
        $useStmt = 'use '.$classToUse->__toString().';';

        $namespaceLineNb = null;
        $lastUseLineNb = null;
        $phpDeclarationLineNb = null;

        foreach ($lines as $index => $line) {
            if (preg_match('{^<\?php}', $line)) {
                $phpDeclarationLineNb = $index;
            }

            if (preg_match('{^namespace}', $line)) {
                $namespaceLineNb = $index;
            }

            if (preg_match('{^use}', $line)) {
                $lastUseLineNb = $index;
            }
        }

        if ($lastUseLineNb) {
            return $this->insertAfter($lastUseLineNb, $useStmt);
        }

        if ($namespaceLineNb) {
            return $this->insertAfter($namespaceLineNb, PHP_EOL.$useStmt);
        }

        if (null !== $phpDeclarationLineNb) {
            return $this->insertAfter($phpDeclarationLineNb, PHP_EOL.$useStmt);
        }

        throw new \InvalidArgumentException(
            'Could not find <?php start tag'
        );
    }

    private function insertAfter(int $lineNb, $text)
    {
        $lines = explode(PHP_EOL, $this->source);
        $newLines = [];
        foreach ($lines as $index => $line) {
            if ($line === $text) {
                return $this;
            }

            $newLines[] = $line;
            if ($index === $lineNb) {
                $newLines[] = $text;
            }
        }

        return $this->replaceSource(implode(PHP_EOL, $newLines));
    }

    public function replaceSource(string $source)
    {
        return new self($source);
    }

    public function __toString()
    {
        return $this->source;
    }
}
