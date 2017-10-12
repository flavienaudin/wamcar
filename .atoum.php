<?php

if (!is_dir(__DIR__.'/build/')) {
    mkdir(__DIR__.'/build/', 0777);
}

$script->addTestsFromDirectory(__DIR__.'/tests/unit');
$script->noCodeCoverageForNamespaces('Composer');

$xunitWriter = new atoum\writers\file(__DIR__.'/build/test-unit/atoum.xunit.xml');
$xunitReport = new atoum\reports\asynchronous\xunit();
$xunitReport->addWriter($xunitWriter);

$runner->addReport($script->addDefaultReport());
$runner->addReport($xunitReport);
