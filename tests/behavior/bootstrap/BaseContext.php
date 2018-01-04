<?php

namespace Test\Behavior\Context;

use Behat\Behat\Context\Context as ContextInterface;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\StepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\Dotenv\Dotenv;

abstract class BaseContext extends RawMinkContext implements ContextInterface
{
    // TODO : découper un peu le contenu de cette classe

    /** @var \mageekguy\atoum\asserter\generator */
    protected $assert;

    /**
     * BaseContext constructor.
     */
    public function __construct()
    {
        (new Dotenv())->load(__DIR__.'/../../../.env');
        $this->assert = new \mageekguy\atoum\asserter\generator();
    }

    /**
     * Tries closure every second for $tries seconds.
     * This is useful when waiting for a javascript animation to finish or an elasticsearch index update
     *
     * @param \Closure $closure
     * @param int $tries
     *
     * @throws \Exception
     */
    public function spins(\Closure $closure, $tries = 10)
    {
        for ($i = 0; $i <= $tries; $i++) {
            try {
                $closure();

                return;
            } catch (\Exception $e) {
                if ($i == $tries) {
                    throw $e;
                }
            }
            sleep(1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function selectOption($select, $option)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $field = $page->findField($select);
        if (!$field) {
            throw new \RuntimeException("Could not find field '{$select}'");
        }
        if ($field->hasClass('selectized')) {
            return $this->selectFromSelectize($select, $option, $field);
        }
        return parent::selectOption($select, $option);
    }


    /**
     * Find an element by Css selector
     *
     * @param $selector
     * @return NodeElement|mixed|null
     */
    protected function findByCssSelector($selector)
    {
        return $this->getSession()->getPage()->find('css', $selector);
    }

    /**
     * @param $formPrefix
     * @param $field
     * @return string
     */
    protected function getFieldName($formPrefix, $field)
    {
        return $formPrefix . '[' . $field . ']';
    }

    /**
     * Selects the passed option from a selectize.js dropdown
     *
     * @param string $selector
     * @param string $option
     * @param \Behat\Mink\Element\NodeElement $field
     *
     * @throws \RuntimeException
     */
    private function selectFromSelectize($selector, $option, NodeElement $field)
    {
        $parent = $field->getParent();
        $element = $parent->find('css', '.selectize-input');
        if (!$element) {
            throw new \RuntimeException("Could not find selectize element for '{$selector}'");
        }

        /** Open the dropdown */
        $element->click();

        /** @var NodeElement[] $options */
        $options = $parent->findAll('css', '.selectize-dropdown-content .option');
        foreach ($options as $opt) {
            if ($opt->getText() == $option || $opt->getAttribute('data-value') == $option) {
                $opt->click();
            }
        }
    }

    /**
     * Take screenshot
     * Works only with Selenium2Driver.
     *
     * @AfterStep
     */
    public function takeScreenshotAfterStep(AfterStepScope $event)
    {
        $driver = $this->getSession()->getDriver();
        $scenario = $this->getScenario($event);

        if ($driver instanceof Selenium2Driver && $scenario) {
            $directory = 'build/behat/'.date('Ymd').date('Hm');
            self::createDir($directory);

            $directory .= '/'.self::slugify($event->getSuite()->getName());
            self::createDir($directory);

            $directory .= '/'.self::slugify($event->getFeature()->getTitle());
            self::createDir($directory);

            $directory .= '/'.self::slugify($scenario->getTitle());
            self::createDir($directory);

            $filename = sprintf('%s_%s_%s%s_%s.%s',
                $this->getMinkParameter('browser_name'),
                date('c'),
                ($event->getTestResult()->isPassed() ? '' : '{failed}_'),
                self::slugify($event->getStep()->getText()),
                uniqid('', true),
                'png'
            );

            file_put_contents($directory.'/'.$filename, $driver->getScreenshot());
        }
    }


    /**
     * @param $dirname
     */
    private static function createDir($dirname)
    {
        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }
    }

    /**
     * @param $text
     * @return mixed|string
     */
    private static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * @param StepScope $scope
     * @return \Behat\Gherkin\Node\ScenarioInterface|null
     */
    private static function getScenario(StepScope $scope)
    {
        $scenario = null;

        $feature = $scope->getFeature();
        $step = $scope->getStep();
        $line = $step->getLine();

        foreach ($feature->getScenarios() as $tmp) {
            if ($tmp->getLine() > $line) {
                break;
            }

            $scenario = $tmp;
        }

        return $scenario;
    }

}
