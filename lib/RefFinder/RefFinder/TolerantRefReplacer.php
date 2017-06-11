<?php

namespace DTL\ClassMover\RefFinder\RefFinder;

use DTL\ClassMover\Finder\FileSource;
use DTL\ClassMover\RefFinder\ClassRefList;
use DTL\ClassMover\RefFinder\FullyQualifiedName;
use Microsoft\PhpParser\TextEdit;

class TolerantRefReplacer
{
    public function replaceReferences(FileSource $source, ClassRefList $classRefList, FullyQualifiedName $name)
    {
        $edits = [];
        foreach ($classRefList as $classRef) {
            $edits[] = new TextEdit(
                $classRef->position()->start(),
                $classRef->position()->length(),
                $classRef->name()->transpose($name)->__toString()
            );
        }
        return $source->replaceSource(TextEdit::applyEdits($edits, $source->__toString()));
    }
}